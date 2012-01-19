<?php
namespace org\opencomb\doccenter\formatter\table ;

use org\opencomb\doccenter\formatter\ITransformer ;

class TableBeginTransformer implements ITransformer{
	public function pattern(){
		return '`{\|(.*?)<br />`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sAttr) = $arrMatch ;
		
		$str = '<table '.$sAttr.'><tr>';
		return $str ;
	}
}
