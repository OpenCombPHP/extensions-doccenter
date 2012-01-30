<?php
namespace org\opencomb\doccenter\formatter\example ;

use org\opencomb\doccenter\formatter\AbstractMultiLineTransformer ;
use org\opencomb\platform\ext\ExtensionManager ;
use org\jecat\framework\fs\FileSystem ;

/**
 * @wiki /文档中心/wiki语法
 * {|
 *  ! 语法
 *  ! html
 *  ! 说明
 *  |-- --
 *  | &#91;example lang="lang"]code[/example]
 *  | <pre class="brush:lang">code</pre>
 *  |}
 */

class InCodeExampleTransformer extends AbstractMultiLineTransformer{
	public function pattern(){
		return '`\[example lang=["\'](\w*)["\']\](.*?)\[/example\]`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sLang , $sCode) = $arrMatch ;
		$sCode = htmlentities($sCode,ENT_QUOTES, "UTF-8");
		return '<div class="example">
					<h3>例子: </h3>
					<div>
						<pre class="brush:'.$sLang.'">'.$sCode.'</pre>
					</div>
				</div>';
	}
}
