<?php
namespace org\opencomb\doccenter\formatter\font ;

use org\opencomb\doccenter\formatter\AbstractMultiLineTransformer ;

class AttrTagsTransformer extends AbstractMultiLineTransformer{
	public function pattern(){
		return '`\[(font) (.*?)\](.*?)\[/(font)\]`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sBegin , $sAttr , $sCont , $sEnd) = $arrMatch ;
		return '<'.$sBegin.' '.$sAttr.'>'.$sCont.'</'.$sEnd.'>';
	}
}
