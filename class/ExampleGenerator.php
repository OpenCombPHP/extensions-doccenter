<?php
namespace org\opencomb\doccenter ;

use org\opencomb\coresystem\mvc\controller\ControlPanel ;
use org\jecat\framework\fs\FileSystem ;
use org\jecat\framework\lang\compile\CompilerFactory ;
use org\jecat\framework\lang\compile\object\Token ;
use org\jecat\framework\lang\compile\object\TokenPool ;
use org\opencomb\platform\ext\ExtensionManager ;
use org\jecat\framework\lang\oop\ClassLoader;
use org\jecat\framework\fs\IFSO ;
use org\jecat\framework\fs\IFolder ;
use org\jecat\framework\db\DB ;
use org\jecat\framework\db\sql\StatementFactory ;

class ExampleGenerator extends ControlPanel{
	public function process(){
		$arrPath = $this->params['path'];
		
		echo count($arrPath);
		echo ' ';
		echo "\n";
		
		foreach($arrPath as $path){
			$arrGenerate = $this->generate($path);
			if( TRUE === $this->cleanInDB($arrGenerate)){
				$this->saveInDB($arrGenerate);
			}
		}
		$this->mainView()->disable();
	}
	
	private function generate($sPath){
		$aFile = FileSystem::singleton()->findFile($sPath);
		if($aFile === null) return null;
		
		// PackageNamespace
		$sPackageNamespace = '';
		$aClassLoader = ClassLoader::singleton();
		foreach($aClassLoader->packageIterator() as $aPackage){
			if($this->isInFolder($aFile,$aPackage->folder()) ){
				$sPackageNamespace = $aPackage->ns();
				break;
			}
		}
		
		$aInputStream = $aFile->openReader();
		$aCompiler = CompilerFactory::singleton()->create();
		$aTokenPool = $aCompiler->scan($aInputStream);
		$aCompiler->interpret($aTokenPool);
		
		// sourceClass
		$sSourceClass = '';
		foreach($aTokenPool->classIterator() as $aClassToken){
			$sSourceClass = $aClassToken->fullName();
			break ;
		}
		
		$arrDoc = $this->generateDoc($aTokenPool);
		foreach($arrDoc as &$doc){
			$doc['sourcePackageNamespace'] = $sPackageNamespace ;
			$doc['sourceClass'] = $sSourceClass ;
		}
		return $arrDoc ;
	}
	
	private function isInFolder(IFSO $aFSO,IFolder $aFolder){
		while($aFSO !== null){
			if( $aFSO === $aFolder){
				return true;
			}
			$aFSO = $aFSO->directory();
		}
		return false;
	}
	
	private function generateDoc(TokenPool $aTokenPool){
		$arrDoc = array();
		$aIterator = $aTokenPool -> iterator() ;
		foreach( $aIterator as $aToken ){
			if($aToken->tokenType() === T_DOC_COMMENT){
				if( ! $aToken instanceof \org\jecat\framework\lang\compile\object\DocCommentDeclare ){
					$aToken = new \org\jecat\framework\lang\compile\object\DocCommentDeclare($aToken) ;
				}
				$aDocComment = $aToken->docComment();
				if(!$aDocComment->hasItem('example')) continue;
				
				$arrExample = array();
				
				$sTitle = '';
				$sName = '';
				$iIndex = 0;
				$sCode = '';
				
				$sExample = $aDocComment->item('example');
				$arrExampleMatch = array();
				if(preg_match('|^(.*):(.*)\[(.*)\]$|',$sExample,$arrExampleMatch)){
					$sTitle = $arrExampleMatch[1] ;
					$sName = $arrExampleMatch[2] ;
					$iIndex = (int)$arrExampleMatch[3] ;
				}else if(preg_match('|^(.*):(.*)$|',$sExample,$arrExampleMatch)){
					$sTitle = $arrExampleMatch[1] ;
					$sName = $arrExampleMatch[2] ;
				}else{
					$arrExampleMatch[]=$sExample ;
					$sTitle = $sExample ;
				}
				$arrExample['title'] = $sTitle ;
				$arrExample['name'] = $sName ;
				$arrExample['index'] = $iIndex ;
				
				$sForClass = $aDocComment->item('forclass') ;
				$arrExample['forclass'] = $sForClass ;
				
				$sForMethod = $aDocComment->item('formethod') ;
				$arrExample['formethod'] = $sForMethod ;
				
				$sForWiki = $aDocComment->item('forwiki') ;
				$arrExample['forwiki'] = $sForWiki ;
				
				$sNamespace = $aToken->belongsNamespace()->name();
				
				$sExtension = ExtensionManager::singleton()->extensionNameByNamespace($sNamespace);
				$arrExample['extension'] = $sExtension ;
				
				$aVersion = null ;
				if(empty($sExtension)){
					$strFrameworkNs = 'org\\jecat\\framework' ;
					$nFNLength = strlen($strFrameworkNs) ;
					$strPlatformNs = 'org\\opencomb\\platform' ;
					$nPNLength = strlen($strPlatformNs) ;
					if( substr($classname,0,$nFNLength) === $strFrameworkNs ){
						$aVersion = Version::FromString(\org\jecat\framework\VERSION) ;
					}else if ( substr($classname,0,$nPNLength) === $strPlatformNs ){
						$aVersion = Platform::singleton()->version();
					}
				}else{
					$aExtensionMetainfo = ExtensionManager::singleton()->extensionMetainfo($sExtension);
					$aVersion = $aExtensionMetainfo->version();
				}
				$sVersion = $aVersion->to32Integer();
				$arrExample['version'] = $sVersion ;
				
				$iLine = $aToken->line();
				$arrExample['sourceLine'] = $iLine ;
				
				$aCodeBeginToken = null;
				$aCodeEndToken = null;
				$aFolIterator = clone $aIterator ;
				while( $aFolIterator->valid ()){
					$aFolToken = $aFolIterator->current();
					switch($aFolToken->tokenType() ){
					case Token::T_BRACE_OPEN:
						if($aCodeBeginToken === null){
							$aCodeBeginToken = $aFolToken ;
						}
						break;
					case Token::T_BRACE_CLOSE:
						if($aCodeBeginToken !== null and $aCodeBeginToken->theOther() === $aFolToken){
							$aCodeEndToken = $aFolToken ;
							break;
						}
						break;
					}
					$sCode .= $aFolToken->__toString();
					if($aCodeEndToken !== null){
						break;
					}
					
					$aFolIterator->next() ;
				}
				$arrExample['code'] = $sCode ;
				
				$arrDoc [] = $arrExample ;
			}
		}
		return $arrDoc ;
	}
	
	private function cleanInDB(array $arrGenerate){
		$aDB = DB::singleton();
		foreach($arrGenerate as $generate){
			$extension = $generate ['extension'] ;
			$version = $generate ['version'] ;
			$sourcePackageNamespace = $generate ['sourcePackageNamespace'] ;
			$sourceClass = $generate ['sourceClass'] ;
			$aDB->execute(
					'DELETE 
						doccenter_example,
						doccenter_example_class,
						doccenter_example_method ,
						doccenter_example_topic
					FROM 
						`doccenter_example`
							LEFT JOIN `doccenter_example_class` ON `doccenter_example`.`eid` = `doccenter_example_class`.`eid`
							LEFT JOIN `doccenter_example_method` ON `doccenter_example`.`eid` = `doccenter_example_method`.`eid`
							LEFT JOIN `doccenter_example_topic` ON `doccenter_example`.`eid` = `doccenter_example_topic`.`eid`
					WHERE (
						`doccenter_example`.`extension` = "'.$extension.'"
						AND `doccenter_example`.`version` = "'.$version.'"
						AND `doccenter_example`.`sourcePackageNamespace` = "'.addslashes($sourcePackageNamespace).'"
						AND `doccenter_example`.`sourceClass` = "'.addslashes($sourceClass).'"
					)'
			);
		}
		return true;
	}
	
	private function saveInDB(array $arrGenerate){
		$arrKeyExample = array(
			'extension',
			'version',
			'title',
			'name',
			'index',
			'code',
			'sourcePackageNamespace',
			'sourceClass',
			'sourceLine',
		);
		$aDB = DB::singleton() ;
		foreach($arrGenerate as $generate){
			// example
			$aExampleInsert = StatementFactory::singleton()->createInsert('doccenter_example');
			foreach($arrKeyExample as $sKey){
				$aExampleInsert->setData('`'.$sKey.'`',$generate[$sKey]);
			}
			$aDB->execute($aExampleInsert);
			$eid = $aDB->lastInsertId();
			
			// class
			if(!empty($generate['forclass'])){
				$aExampleClassInsert = StatementFactory::singleton()->createInsert('doccenter_example_class');
				$aExampleClassInsert->setData('eid',$eid);
				$aExampleClassInsert->setData('class',$generate['forclass']);
				$aDB->execute($aExampleClassInsert);
			}
			
			// method
			if(!empty($generate['formethod'])){
				$aExampleMethodInsert = StatementFactory::singleton()->createInsert('doccenter_example_method');
				$aExampleMethodInsert->setData('eid',$eid);
				$aExampleMethodInsert->setData('method',$generate['formethod']);
				$aDB->execute($aExampleMethodInsert);
			}
			
			// topic
			if(!empty($generate['formethod'])){
				$aExampleTopicInsert = StatementFactory::singleton()->createInsert('doccenter_example_topic');
				$aExampleTopicInsert->setData('eid',$eid);
				$aExampleTopicInsert->setData('topic_title',$generate['forwiki']);
				$aDB->execute($aExampleTopicInsert);
			}
		}
	}
}
