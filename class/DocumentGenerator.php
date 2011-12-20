<?php
namespace org\opencomb\doccenter ;

use org\opencomb\coresystem\mvc\controller\ControlPanel ;

// /?c=org.opencomb.doccenter.DocumentGenerator&noframe=1&path[]=/framework/class/util/match/ResultSet.php&path[]=/framework/class/util/match/RegExp.php&path[]=/framework/class/util/match/Result.php

class DocumentGenerator  extends ControlPanel
{
	public function process(){
		// echo json_encode($this->params['path']);
		$arrPath = $this->params['path'];
		$arrResult = array();
		
		foreach($arrPath as $path){
			$arrResult [] = $this->GenerateByPath($path);
		}
		echo json_encode($arrResult);
		//
		$this->mainView()->disable(); 
	}
	
	private function GenerateByPath($path){
		return $path;
	}
}
