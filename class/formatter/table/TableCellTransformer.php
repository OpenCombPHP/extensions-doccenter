<?php
namespace org\opencomb\doccenter\formatter\table ;

use org\opencomb\doccenter\formatter\ITransformer ;

class TableCellTransformer implements ITransformer{
	public function pattern(){
		return '`\s*\|(.*?)(<br />)`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sText , $sTail) = $arrMatch ;
		
		$str = '<td>'.$sText.'</td>';
		return $str ;
	}
}
