<?php
namespace org\opencomb\doccenter\formatter\warning ;

use org\opencomb\doccenter\formatter\AbstractMultiLineTransformer ;

class WarningBlock extends AbstractMultiLineTransformer{
	public function pattern(){
		return '`\[([!^?])\](.*?)\[/([!^?])\]`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sPre , $sCont ) = $arrMatch ;
		
		$arrClass = array(
			'?' => 'question',
			'!' => 'warning',
			'^' => 'notice',
		);
		
		return '<div class="'.$arrClass[$sPre].'Block">'.$sCont.'</div>';
	}
}
