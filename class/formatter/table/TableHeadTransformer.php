<?php
namespace org\opencomb\doccenter\formatter\table;

use org\opencomb\doccenter\formatter\AbstractSingleLineTransformer;

/**
 * @wiki /文档中心/wiki语法
 * {|
 * ! 语法
 * ! html
 * ! 说明
 * |-- --
 * | !xxx
 * | <th>xxx</th>
 * |}
 */

class TableHeadTransformer extends AbstractSingleLineTransformer {
	public function pattern() {
		return '`(^|<tr>|</th>|</td>)\s*!(.*?)$`';
	}
	
	public function replacement(array $arrMatch) {
		list ( $sAll, $sPre, $sText ) = $arrMatch;
		
		$str = $sPre . '<th>' . $sText . '</th>';
		return $str;
	}
}
