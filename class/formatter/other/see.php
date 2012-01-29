<?php
namespace org\opencomb\doccenter\formatter\other ;

use org\opencomb\doccenter\formatter\AbstractMultiLineTransformer ;

class see extends AbstractMultiLineTransformer{
	public function pattern(){
		return '`\[see (.*?)\]`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sAttr) = $arrMatch ;
		$arr = explode('#',$sAttr);
		$sLink = '/?c=org.opencomb.doccenter.WikiContent&title='.$arr[0];
		if(isset($arr[1])){
			$sLink.='#'.$arr[1];
		}
		return '<a href="'.$sLink.'">参见 '.$sAttr.'</a>';
	}
}
