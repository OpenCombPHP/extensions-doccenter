<?php
namespace org\opencomb\doccenter\formatter\font ;

use org\opencomb\doccenter\formatter\ITransformer ;

class AttrTagsTransformer implements ITransformer{
	public function pattern(){
		return '`\[(font) (.*?)\](.*?)\[/(font)\]`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sBegin , $sAttr , $sCont , $sEnd) = $arrMatch ;
		return '<'.$sBegin.' '.$sAttr.'>'.$sCont.'</'.$sEnd.'>';
	}
}
