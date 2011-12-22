<?php
namespace org\opencomb\doccenter ;

use org\opencomb\coresystem\mvc\controller\ControlPanel ;
use org\jecat\framework\lang\compile\CompilerFactory ;
use org\jecat\framework\lang\compile\Compiler ;
use org\jecat\framework\fs\FileSystem ;
use org\jecat\framework\lang\Exception ;
use org\opencomb\platform\ext\ExtensionManager ;

// /?c=org.opencomb.doccenter.DocumentGenerator&noframe=1&path[]=/framework/class/util/match/ResultSet.php&path[]=/framework/class/util/match/RegExp.php&path[]=/framework/class/util/match/Result.php
// /?c=org.opencomb.doccenter.DocumentGenerator&debug=1&noframe=1&path[]=/extensions/doccenter/0.1/class/testCompiler.php

class DocumentGenerator  extends ControlPanel
{
	public function process(){
		$arrPath = $this->params['path'];
		$arrResult = array();
		
		foreach($arrPath as $path){
			$arrResult [] = $this->generate($path);
		}
		if(empty($this->params['debug'])){
			echo json_encode($arrResult);
		}else{
			echo "<pre>";
			var_dump($arrResult);
			echo "</pre>";
		}
		$this->mainView()->disable(); 
	}
	
	public function generate($path){
		// for debug
		if(substr($path,-5,1) === 'r'){
			return array(
				'path' => $path,
				'error'=>true,
				'errorString' => 'for debug : '.substr($path,-5,1),
			);
		}// for debug end
		
		
		$arrGenerate = array();
		$arrGenerate['path'] = $path;
		$arrGenerate['classlist'] = array();
		
		$aTokenPool = $this->getTokenPool($path);
		if(null === $aTokenPool){
			return array(
				'path' => $path,
				'error' => true,
				'errorString' => 'can not open token pool',
			);
		}
		foreach($aTokenPool->classIterator() as $aClassToken){
			$classInfo = array();
			$classInfo['name'] = $aClassToken->name();
			$classInfo['version'] = $this->getVersionByClassName($aClassToken->fullName())->to32Integer();
			$classInfo['abstract'] = $aClassToken->isAbstract();
			$classInfo['namespace'] = $aClassToken->belongsNamespace()->name();
			$classInfo['comment'] = ( null === $aClassToken->docToken() )?'':$aClassToken->docToken()->docComment()->description();
			
			$arrFunction = array();
			foreach($aTokenPool->functionIterator($aClassToken->fullName()) as $aFunctionToken){
				// comment of function 
				// pick up function return type
				// pick up param type and comment
				$arrInfoFromFunctionComment = array(
					'return' => '',
					'paramlist' => array(),
				);
				if( null !== $aFunctionToken->docToken() ){
					$aDocComment = $aFunctionToken->docToken()->docComment();
					foreach($aDocComment->itemIterator('return') as $strReturn){
						$arrInfoFromFunctionComment['return'] = $strReturn ;
					}
					foreach($aDocComment->itemIterator('param') as $strParam){
						$arrParam = preg_split('|\\s+|',$strParam,3);
						$arrParamInfo = array ();
						if(count($arrParam) === 2){
							$arrParamInfo['name'] = $arrParam[0] ;
							$arrParamInfo['comment'] = $arrParam[1] ;
						}else if(count($arrParam) === 3){
							$arrParamInfo['name'] = $arrParam[0] ;
							$arrParamInfo['type'] = $arrParam[1] ;
							$arrParamInfo['comment'] = $arrParam[2] ;
						}
						$arrInfoFromFunctionComment['paramlist'][$arrParamInfo['name']] = $arrParamInfo ;
					}
				}
				// function info
				$functionInfo = array();
				$functionInfo['name'] = $aFunctionToken->name();
				$functionInfo['version'] = $classInfo['version'];
				$functionInfo['class'] = $classInfo['name'];
				$functionInfo['namespace'] = $classInfo['namespace'];
				$functionInfo['access'] = (string)$aFunctionToken->accessToken();
				$functionInfo['abstract'] = (int)(null !== $aFunctionToken->abstractToken());
				$functionInfo['static'] = (int)(null !== $aFunctionToken->staticToken());
				// @todo
				$functionInfo['returnType'] = $aTokenPool->findName($arrInfoFromFunctionComment['return'],$aFunctionToken->belongsNamespace());
				$functionInfo['returnByRef'] = (int)$aFunctionToken->isReturnByRef();
				$functionInfo['comment'] = ( null === $aFunctionToken->docToken() )?'':$aFunctionToken->docToken()->docComment()->description();
				
				// parameter list
				$parameterlist = array();
				foreach($aFunctionToken->parameterIterator() as $parameterToken){
					if($parameterToken->belongsFunction() !== $aFunctionToken){
						throw new Exception('parameter belongs function not match');
					}
					$parameterInfo = array();
					$parameterInfo['version'] = $classInfo['version'];
					$parameterInfo['namespace'] = $classInfo['namespace'];
					$parameterInfo['class'] = $classInfo['name'];
					$parameterInfo['method'] = $functionInfo['name'];
					$parameterInfo['default'] = ('' === $parameterToken->defaultValue())?'':$parameterToken->defaultValue();
					
					// param type
					if($parameterToken->type() !== ''){
						$parameterInfo['type'] = $parameterToken->type() ;
					}else if(isset($arrInfoFromFunctionComment['paramlist'][$parameterToken->name()])){
						$parameterInfo['type'] = $arrInfoFromFunctionComment['paramlist'][$parameterToken->name()]['type'] ;
					}else{
						$parameterInfo['type'] = '' ;
					}
					
					$parameterInfo['name'] = $parameterToken->name();
					$parameterInfo['byRef'] = (int)$parameterToken->isReference();
					// comment
					if(isset($arrInfoFromFunctionComment['paramlist'][$parameterToken->name()])){
						$parameterInfo['comment'] = $arrInfoFromFunctionComment['paramlist'][$parameterToken->name()]['comment'] ;
					}else{
						$parameterInfo['comment'] = '' ;
					}
					
					$parameterlist[] = $parameterInfo;
				}
				$functionInfo['parameterlist'] = $parameterlist;
				$arrFunction[] = $functionInfo;
			}
			$classInfo['functionlist'] = $arrFunction;
			
			$arrGenerate['classlist'][] = $classInfo;
		}
		return $arrGenerate;
	}
	
	private function getTokenPool($path){
		$aFile = FileSystem::singleton()->findFile($path);
		if($aFile === null) return null;
		$aInputStream = $aFile->openReader();
		$aCompiler = CompilerFactory::singleton()->create();
		$aTokenPool = $aCompiler->scan($aInputStream);
		$aCompiler->interpret($aTokenPool);
		return $aTokenPool;
	}
	
	private function getVersionByClassName($classname){
		if(null === $this->aExtensionManager){
			$this->aExtensionManager = ExtensionManager::singleton() ;
		}
		$extensionName = $this->aExtensionManager->extensionNameByClass($classname);
		$extensionMetainfo = $this->aExtensionManager->extensionMetainfo($extensionName);
		return $extensionMetainfo->version();
	}
	
	private $aExtensionManager = null;
}
