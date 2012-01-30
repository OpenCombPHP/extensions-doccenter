<?php
namespace org\opencomb\doccenter\formatter\table ;

use org\opencomb\doccenter\formatter\AbstractSingleLineTransformer ;

class TableRowTransformer extends AbstractSingleLineTransformer{
	public function pattern(){
		return '`^\s*\|[- ]+$`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll) = $arrMatch ;
		
		$str = '</tr><tr>';
		return $str ;
	}
}
