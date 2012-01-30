<?php
namespace org\opencomb\doccenter\formatter\table ;

use org\opencomb\doccenter\formatter\AbstractSingleLineTransformer ;

/**
 * @wiki /文档中心/wiki语法
 * {|
 *  ! 语法
 *  ! html
 *  ! 说明
 *  |-- --
 *  | |xxx
 *  | <td>xxx</td>
 *  |}
 */

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
