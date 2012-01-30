<?php
namespace org\opencomb\doccenter\formatter\example ;

use org\opencomb\doccenter\formatter\AbstractSingleLineTransformer ;
use org\opencomb\platform\ext\ExtensionManager ;
use org\jecat\framework\fs\FileSystem ;

/**
 * @wiki /文档中心/wiki语法
 * {|
 *  ! 语法
 *  ! html
 *  ! 说明
 *  |-- --
 *  | &#91;example lang extName path nBegin nEnd]
 *  | <pre class="brush:lang">code</pre>
 *  | 从名为extName的扩展的安装目录下寻找路径为path的文件，从第nBegin行到第nEnd行作为code
 *  |}
 */

class FileExampleTransformer extends AbstractSingleLineTransformer{
	public function pattern(){
		return '`\[example (\w*) (\w*) ([a-zA-z0-9/.]*) (\d*) (\d*)\]`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll , $sLang , $sExtName , $sPath , $nBegin , $nEnd) = $arrMatch ;
		
		$sFilePath = ExtensionManager::singleton()->extensionMetainfo($sExtName)->installPath().'/'.$sPath;
		$aFile = FileSystem::singleton()->findFile($sFilePath , FileSystem::FIND_AUTO_CREATE_OBJECT);
		if(!$aFile->exists()){
			return '';
		}
		$arrFile = file($aFile->url());
		$str = '';
		for($i = $nBegin ; $i<$nEnd ; ++$i){
			$str .= $arrFile[$i];
		}
		
		$str = htmlentities($str,ENT_QUOTES, "UTF-8");
		return '<pre class="brush:'.$sLang.'">'.$str.'</pre>';
	}
}
