<?php
namespace org\opencomb\doccenter\formatter\font ;

use org\opencomb\doccenter\formatter\AbstractMultiLineTransformer ;

class SingleTagsTransformer extends AbstractMultiLineTransformer{
	public function pattern(){
		return '`\[(b|i|u|s)\](.*?)\[/(b|i|u|s)\]`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sBegin , $sCont , $sEnd) = $arrMatch ;
		return '<'.$sBegin.'>'.$sCont.'</'.$sEnd.'>';
	}
}
