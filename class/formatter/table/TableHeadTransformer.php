<?php
namespace org\opencomb\doccenter\formatter\table ;

use org\opencomb\doccenter\formatter\ITransformer ;

class TableHeadTransformer implements ITransformer{
	public function pattern(){
		return '`\s*!(.*?)(<br />)`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sText , $sTail) = $arrMatch ;
		
		$str = '<th>'.$sText.'</th>';
		return $str ;
	}
}
