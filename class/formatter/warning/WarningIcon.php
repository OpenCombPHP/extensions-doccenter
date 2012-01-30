<?php
namespace org\opencomb\doccenter\formatter\warning ;

use org\opencomb\doccenter\formatter\AbstractMultiLineTransformer ;

class WarningIcon extends AbstractMultiLineTransformer{
	public function pattern(){
		return '`\(([!^?])\)`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sPre ) = $arrMatch ;
		
		$arrClass = array(
			'?' => 'question',
			'!' => 'warning',
			'^' => 'notice',
		);
		
		return '<div class="'.$arrClass[$sPre].'Icon"></div>';
	}
}
