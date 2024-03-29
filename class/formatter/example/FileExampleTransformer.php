<?php
namespace org\opencomb\doccenter\formatter\example;

use org\opencomb\doccenter\formatter\AbstractSingleLineTransformer;
use org\opencomb\platform\ext\ExtensionManager;
use org\jecat\framework\fs\Folder;

/**
 * @wiki /文档中心/wiki语法
 * {|
 * ! 语法
 * ! html
 * ! 说明
 * |-- --
 * | &#91;example lang extName path nBegin nEnd]
 * | <pre class="brush:lang">code</pre>
 * | 从名为extName的扩展的安装目录下寻找路径为path的文件，从第nBegin行到第nEnd行作为code
 * |}
 */

class FileExampleTransformer extends AbstractSingleLineTransformer {
	public function pattern() {
		return '`\[example (\w*) (\w*) ([a-zA-Z0-9/.-]*) (\d*) (\d*)\]`';
	}
	
	public function replacement(array $arrMatch) {
		list ( $sAll, $sLang, $sExtName, $sPath, $nBegin, $nEnd ) = $arrMatch;
		
		$sFilePath = ExtensionManager::singleton ()->extensionMetainfo ( $sExtName )->installPath () . '/' . $sPath;
		$aFile = Folder::singleton ()->findFile ( $sFilePath, Folder::FIND_AUTO_CREATE_OBJECT );
		if (! $aFile->exists ()) {
			return '';
		}
		$arrFile = file ( $aFile->url () );
		$str = '';
		for($i = $nBegin; $i < $nEnd; ++ $i) {
			$str .= $arrFile [$i];
		}
		
		$str = htmlentities ( $str, ENT_QUOTES, "UTF-8" );
		return '<div class="example">
					<h3>例子: 扩展:' . $sExtName . ' 路径:' . $sPath . '</h3>
					<div>
						<pre class="brush:' . $sLang . '">' . $str . '</pre>
					</div>
				</div>';
	}
}
