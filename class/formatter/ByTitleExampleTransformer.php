<?php
namespace org\opencomb\doccenter\formatter ;

use org\opencomb\platform\ext\ExtensionManager ;
use org\jecat\framework\fs\FileSystem ;
use org\opencomb\doccenter\example\Example ;

class ByTitleExampleTransformer implements ITransformer{
	public function pattern(){
		return '`\[example title=["\']([a-zA-Z0-9/]*)["\']\]`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sTitle) = $arrMatch ;
		
		$arr = Example::loadByTitle($sTitle);
		
		$str = '' ;
		foreach($arr as $aExample){
			foreach($aExample->iterator() as $aS){
				$str .= $aS->code();
			}
		}
		return '<pre class="brush:php">'.$str.'</pre>';
	}
}
