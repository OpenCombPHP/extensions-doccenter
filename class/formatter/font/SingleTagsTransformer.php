<?php
namespace org\opencomb\doccenter\formatter\font ;

use org\opencomb\doccenter\formatter\AbstractMultiLineTransformer ;

/**
 * @wiki /文档中心/wiki语法
 * {|
 *  ! 语法
 *  ! html
 *  ! 说明
 *  |-- --
 *  | &#91;b]xxx[/b]
 *  | <b>xxx</b>
 *  |-- --
 *  | &#91;i]xxx[/i]
 *  | <i>xxx</i>
 *  |-- --
 *  | &#91;u]xxx[/u]
 *  | <u>xxx</u>
 *  |-- --
 *  | &#91;s]xxx[/s]
 *  | <s>xxx</s>
 *  |}
 */

class SingleTagsTransformer extends AbstractMultiLineTransformer{
	public function pattern(){
		return '`\[(b|i|u|s)\](.*?)\[/(b|i|u|s)\]`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sBegin , $sCont , $sEnd) = $arrMatch ;
		return '<'.$sBegin.'>'.$sCont.'</'.$sEnd.'>';
	}
}
