<?php
namespace org\opencomb\doccenter\formatter\warning ;

use org\opencomb\doccenter\formatter\ITransformer ;

class WarningBlock implements ITransformer{
	public function pattern(){
		return '`\[!\](.*?)\[/!\]`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sCont ) = $arrMatch ;
		return '<div class="warning">'.$sCont.'</div>';
	}
}
