<?php
namespace org\opencomb\doccenter\formatter\other ;

use org\opencomb\doccenter\formatter\AbstractMultiLineTransformer ;

/**
 * @wiki /文档中心/wiki语法
 * {|
 *  ! 语法
 *  ! html
 *  ! 说明
 *  |-- --
 *  | &#91;see xxx]
 *  | '<a href="/?c=org.opencomb.doccenter.WikiContent&title=xxx">参见 xxx</a>';
 *  |}
 */

class see extends AbstractMultiLineTransformer{
	public function pattern(){
		return '`\[see (.*?)\]`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sAttr) = $arrMatch ;
		$sLink = '/?c=org.opencomb.doccenter.WikiContent&title='.$sAttr;
		return '<a href="'.$sLink.'">参见 '.$sAttr.'</a>';
	}
}
