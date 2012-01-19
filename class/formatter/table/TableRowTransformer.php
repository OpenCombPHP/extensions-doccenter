<?php
namespace org\opencomb\doccenter\formatter\table ;

use org\opencomb\doccenter\formatter\ITransformer ;

class TableRowTransformer implements ITransformer{
	public function pattern(){
		return '`(^|<br />)\s*\|[- ]+<br />`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll) = $arrMatch ;
		
		$str = '<br /></tr><tr>';
		return $str ;
	}
}
