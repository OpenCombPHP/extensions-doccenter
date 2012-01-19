<?php
namespace org\opencomb\doccenter\formatter\table ;

use org\opencomb\doccenter\formatter\ITransformer ;

class TableEndTransformer implements ITransformer{
	public function pattern(){
		return '`\|}(.*?)(<br />|$)`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll) = $arrMatch ;
		
		$str = '</tr></table>';
		return $str ;
	}
}
