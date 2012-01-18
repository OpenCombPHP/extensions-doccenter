<?php
namespace org\opencomb\doccenter\formatter ;

use org\opencomb\platform\ext\ExtensionManager ;
use org\jecat\framework\fs\FileSystem ;

class FileExampleTransformer implements ITransformer{
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
		
		return '<pre class="brush:'.$sLang.'">'.$str.'</pre>';
	}
}
