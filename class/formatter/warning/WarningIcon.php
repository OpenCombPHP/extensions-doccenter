<?php
namespace org\opencomb\doccenter\formatter\warning;

use org\opencomb\doccenter\formatter\AbstractMultiLineTransformer;

/**
 * @wiki /文档中心/wiki语法
 * {|
 * ! 语法
 * ! html
 * ! 说明
 * |-- --
 * | &#40;!)
 * | <div class="warningIcon"></div>
 * |-- --
 * | &#40;?)
 * | <div class="questionIcon"></div>
 * |-- --
 * | &#40;^)
 * | <div class="noticeIcon"></div>
 * |}
 */

class WarningIcon extends AbstractMultiLineTransformer {
	public function pattern() {
		return '`\(([!^?])\)`';
	}
	
	public function replacement(array $arrMatch) {
		list ( $sAll, $sPre ) = $arrMatch;
		
		$arrClass = array ('?' => 'question', '!' => 'warning', '^' => 'notice' );
		
		return '<div class="' . $arrClass [$sPre] . 'Icon"></div>';
	}
}
