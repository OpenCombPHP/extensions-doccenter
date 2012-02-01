<?php
namespace org\opencomb\doccenter\generator ;

use org\jecat\framework\lang\compile\object\Token ;
use org\jecat\framework\lang\compile\object\TokenPool ;

class UseInExample{
	public function processToken(Token $aToken , TokenPool $aTokenPool){
		// assert T_STRING
		if($aToken->tokenType() !== T_STRING) return;
		
		$sSourceCode = $aToken->sourceCode();
		$sFindName = $aTokenPool->findName($sSourceCode);
		if( strlen($sFindName) > strlen($sSourceCode)){
			$this->arrUseList[$sSourceCode] = $sFindName ;
		}
	}
	
	public function useList(){
		return $this->arrUseList ;
	}
	
	public function codeForUseList(){
		$sCode = '';
		foreach($this->arrUseList as $sSourceCode => $sFindName ){
			$nLenFindName = strlen($sFindName);
			$nLenSourceCode = strlen($sSourceCode);
			// 如果 sFindName 以 sSourceCode 结尾
			$sTail = substr($sFindName , $nLenFindName-$nLenSourceCode, $nLenFindName);
			if($sTail === $sSourceCode){
				$sCodeLine = 'use '.$sFindName." ;";
			}else{
				$sCodeLine = 'use '.$sFindName.' as '.$sSourceCode." ;";
			}
			
			$sCode .= $sCodeLine ."\n";
		}
		// 加上注释符号
		if(!empty($sCode)){
			$sCode = "/*\n".$sCode."*/\n";
		}
		return $sCode ;
	}
	
	private $arrUseList = array();
}
