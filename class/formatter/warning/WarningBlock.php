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
 * | &#91!]xxx[\!]
 * | <div class="warningBlock">xxx</div>
 * |-- --
 * | &#91?]xxx[\?]
 * | <div class="questionBlock">xxx</div>
 * |-- --
 * | &#91^]xxx[\^]
 * | <div class="noticeBlock">xxx</div>
 * |}
 */

class WarningBlock extends AbstractMultiLineTransformer {
	public function pattern() {
		return '`\[([!^?])\](.*?)\[/([!^?])\]`';
	}
	
	public function replacement(array $arrMatch) {
		list ( $sAll, $sPre, $sCont ) = $arrMatch;
		
		$arrClass = array ('?' => 'question', '!' => 'warning', '^' => 'notice' );
		
		return '<div class="' . $arrClass [$sPre] . 'Block">' . $sCont . '</div>';
	}
}
