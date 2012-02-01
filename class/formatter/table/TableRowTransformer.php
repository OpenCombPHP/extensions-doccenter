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
 *  | |- （后可接任意多个减号“-”或空格“ ”）
 *  | </tr><tr>
 *  | 必须独占一行
 *  |}
 */

class TableRowTransformer extends AbstractSingleLineTransformer{
	public function pattern(){
		return '`^\s*\|-[- ]+$`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll) = $arrMatch ;
		
		$str = '</tr><tr>';
		return $str ;
	}
}
