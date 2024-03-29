<?php
namespace org\opencomb\doccenter\formatter\example;

use org\opencomb\doccenter\formatter\AbstractSingleLineTransformer;
use org\opencomb\platform\ext\ExtensionManager;
use org\jecat\framework\fs\Folder;
use org\opencomb\doccenter\example\Example;

/**
 * @wiki /文档中心/wiki语法
 * {|
 * ! 语法
 * ! html
 * ! 说明
 * |-- --
 * | &#91;example title="\xxx\ooo"]
 * | <pre class="brush:php">code</pre>
 * | code 为 title 是 \xxx\ooo 的 example 的代码
 * |}
 */

class ByTitleExampleTransformer extends AbstractSingleLineTransformer {
	public function pattern() {
		return '`\[example title=["\'](.*?)["\']\]`';
	}
	
	public function replacement(array $arrMatch) {
		list ( $sAll, $sTitle ) = $arrMatch;
		
		$arr = Example::loadByTitle ( $sTitle );
		
		$str = '';
		foreach ( $arr as $aExample ) {
			foreach ( $aExample->iterator () as $aS ) {
				$str .= $aS->code ();
			}
		}
		
		$str = htmlentities ( $str, ENT_QUOTES, "UTF-8" );
		return '<div class="example">
					<h3><a href="#">例子:' . $sTitle . '</a></h3>
					<div>
						<pre class="brush: php;">
' . $str . '
						</pre>
					</div>
				</div>';
	}
}
