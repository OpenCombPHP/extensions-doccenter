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

class WikiGenerator extends ControlPanel{
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
		
		$arrWiki = $this->generateWiki($aTokenPool);
		foreach($arrWiki as &$wiki){
			$wiki['sourcePackageNamespace'] = $sPackageNamespace ;
			$wiki['sourceClass'] = $sSourceClass ;
		}
		return $arrWiki ;
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
	
	private function generateWiki(TokenPool $aTokenPool){
		$arrWikis = array();
		$aIterator = $aTokenPool -> iterator() ;
		foreach( $aIterator as $aToken ){
			if($aToken->tokenType() === T_DOC_COMMENT){
				if( ! $aToken instanceof \org\jecat\framework\lang\compile\object\DocCommentDeclare ){
					$aToken = new \org\jecat\framework\lang\compile\object\DocCommentDeclare($aToken) ;
				}
				$aDocComment = $aToken->docComment();
				if(!$aDocComment->hasItem('wiki')) continue;
				
				$arrWiki = array();
				
				$sWiki = $aDocComment->item('wiki');
				$arrWiki['title'] = $sWiki ;
				
				$sText = $aDocComment->description();
				$arrWiki['text'] = $sText ;
				
				$sNamespace = $aToken->belongsNamespace()->name();
				$sExtension = ExtensionManager::singleton()->extensionNameByNamespace($sNamespace);
				$arrWiki['extension'] = $sExtension ;
				
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
				$arrWiki['version'] = $sVersion ;
				
				$iLine = $aToken->line();
				$arrWiki['sourceLine'] = $iLine ;
				
				$arrWikis [] = $arrWiki ;
			}
		}
		return $arrWikis ;
	}
	
	private function cleanInDB(array $arrGenerate){
		$aDB = DB::singleton();
		foreach($arrGenerate as $generate){
			$extension = $generate ['extension'] ;
			$version = $generate ['version'] ;
			$sourcePackageNamespace = $generate ['sourcePackageNamespace'] ;
			$sourceClass = $generate ['sourceClass'] ;
			$aDB->execute(
					'DELETE FROM
						doccenter_topic
					WHERE (
						`extension` = "'.$extension.'"
						AND `version` = "'.$version.'"
						AND `sourcePackageNamespace` = "'.addslashes($sourcePackageNamespace).'"
						AND `sourceClass` = "'.addslashes($sourceClass).'"
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
			'text',
			'sourcePackageNamespace',
			'sourceClass',
			'sourceLine',
		);
		$aDB = DB::singleton() ;
		foreach($arrGenerate as $generate){
			// wiki
			$aExampleInsert = StatementFactory::singleton()->createInsert('doccenter_topic');
			foreach($arrKeyExample as $sKey){
				$aExampleInsert->setData('`'.$sKey.'`',$generate[$sKey]);
			}
			$aDB->execute($aExampleInsert);
			$eid = $aDB->lastInsertId();
		}
	}
}
