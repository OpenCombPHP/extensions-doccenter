<?php
namespace org\opencomb\doccenter\formatter\table ;

use org\opencomb\doccenter\formatter\AbstractSingleLineTransformer ;

class TableEndTransformer extends AbstractSingleLineTransformer{
	public function pattern(){
		return '`^\s*\|}(.*?)$`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll) = $arrMatch ;
		
		$str = '</tr></table><br />';
		return $str ;
	}
}
