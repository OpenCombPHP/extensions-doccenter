<?php
namespace org\opencomb\doccenter\formatter\font;

use org\opencomb\doccenter\formatter\AbstractMultiLineTransformer;

/**
 * @wiki /文档中心/wiki语法
 * {|
 * ! 语法
 * ! html
 * ! 说明
 * |-- --
 * | &#91;font xxx1]xxx2[/font]
 * | <font xxx1>xxx2</font>
 * |}
 */

class AttrTagsTransformer extends AbstractMultiLineTransformer {
	public function pattern() {
		return '`\[(font) (.*?)\](.*?)\[/(font)\]`';
	}
	
	public function replacement(array $arrMatch) {
		list ( $sAll, $sBegin, $sAttr, $sCont, $sEnd ) = $arrMatch;
		return '<' . $sBegin . ' ' . $sAttr . '>' . $sCont . '</' . $sEnd . '>';
	}
}
