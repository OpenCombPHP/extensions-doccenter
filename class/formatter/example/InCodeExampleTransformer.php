<?php
namespace org\opencomb\doccenter\formatter\example ;

use org\opencomb\doccenter\formatter\AbstractSingleLineTransformer ;
use org\opencomb\platform\ext\ExtensionManager ;
use org\jecat\framework\fs\FileSystem ;

class InCodeExampleTransformer extends AbstractSingleLineTransformer{
	public function pattern(){
		return '`\[example lang=["\'](\w*)["\']\](.*?)\[/example\]`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sLang , $sCode) = $arrMatch ;
		return '<pre class="brush:'.$sLang.'">'.$sCode.'</pre>';
	}
}