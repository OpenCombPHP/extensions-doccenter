<?php
namespace org\opencomb\doccenter ;

use org\opencomb\coresystem\mvc\controller\ControlPanel ;
use org\jecat\framework\lang\compile\CompilerFactory ;
use org\jecat\framework\lang\compile\Compiler ;
use org\jecat\framework\fs\FileSystem ;

// /?c=org.opencomb.doccenter.DocumentGenerator&noframe=1&path[]=/framework/class/util/match/ResultSet.php&path[]=/framework/class/util/match/RegExp.php&path[]=/framework/class/util/match/Result.php

class DocumentGenerator  extends ControlPanel
{
	public function process(){
		// echo json_encode($this->params['path']);
		$arrPath = $this->params['path'];
		$arrResult = array();
		
		foreach($arrPath as $path){
			$arrResult [] = $this->generate($path);
		}
		echo json_encode($arrResult);
		//
		$this->mainView()->disable(); 
	}
	
	private function generate($path){
		// for debug
		if(substr($path,-5,1) === 'r'){
			return array(
				'path' => $path,
				'error'=>true,
				'errorString' => 'for debug : '.substr($path,-5,1),
			);
		}
		// for debug end
		$arrGenerate = array();
		$arrGenerate['path'] = $path;
		$arrGenerate['class'] = array();
		$arrGenerate['functions'] = array();
		
		$aInputStream = $this->getInputStream($path);
		$aTokenPool = $this->getTokenPool($aInputStream);
		foreach($aTokenPool->classIterator() as $aClassToken){
			$classInfo = array();
			$classInfo['name'] = $aClassToken->name();
			$classInfo['namespace'] = $aClassToken->belongsNamespace()->name();
			$arrGenerate['class'][] = $classInfo;
			
			$arrFunction = array();
			foreach($aTokenPool->functionIterator($aClassToken->fullName()) as $aFunctionToken){
				$functionInfo = array();
				$functionInfo['name'] = $aFunctionToken->name();
				$arrFunction[] = $functionInfo;
				//echo 'function:',$aFunctionToken->name(),'<br/>';
			}
			$arrGenerate['functions'][$aClassToken->name()] = $arrFunction;
		}
		return $arrGenerate;
	}
	
	private function getInputStream($path){
		$aFile = FileSystem::singleton()->findFile($path);
		if($aFile === null) return null;
		return $aFile->openReader();
	}
	
	private function getTokenPool($aInputStream){
		$aCompiler = CompilerFactory::singleton()->create();
		$aTokenPool = $aCompiler->scan($aInputStream);
		$aCompiler->interpret($aTokenPool);
		return $aTokenPool;
	}
}
