<?php
namespace org\opencomb\doccenter\formatter\table ;

use org\opencomb\doccenter\formatter\AbstractSingleLineTransformer ;

class TableCellTransformer extends AbstractSingleLineTransformer{
	public function pattern(){
		return '`(^|<tr>|</th>|</td>)\s*\|(.*?)$`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sPre , $sText) = $arrMatch ;
		
		$str = $sPre.'<td>'.$sText.'</td>';
		return $str ;
	}
}
