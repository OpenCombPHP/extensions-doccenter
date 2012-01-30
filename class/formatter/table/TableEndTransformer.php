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
 *  | |}
 *  | </tr></table>
 *  |}
 */

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
