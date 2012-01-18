<?php
namespace org\opencomb\doccenter\formatter ;

use org\opencomb\platform\ext\ExtensionManager ;
use org\jecat\framework\fs\FileSystem ;

class InCodeExampleTransformer implements ITransformer{
	public function pattern(){
		return '`\[example lang=["\'](\w*)["\']\](.*?)\[/example\]`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sLang , $sCode) = $arrMatch ;
		return '<pre class="brush:'.$sLang.'">'.$sCode.'</pre>';
	}
}
