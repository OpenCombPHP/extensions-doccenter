<?php
namespace org\opencomb\doccenter\formatter\table ;

use org\opencomb\doccenter\formatter\AbstractSingleLineTransformer ;

class TableBeginTransformer extends AbstractSingleLineTransformer{
	public function pattern(){
		return '`^\s*{\|(.*?)$`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sAttr) = $arrMatch ;
		
		$str = '<table '.$sAttr.'><tr>';
		return $str ;
	}
}
