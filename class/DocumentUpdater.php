<?php
namespace org\opencomb\doccenter ;

use org\opencomb\coresystem\mvc\controller\ControlPanel ;
use org\jecat\framework\lang\oop\ClassLoader;
use org\jecat\framework\lang\compile\CompilerFactory;

class DocumentUpdater  extends ControlPanel
{
	public function createBeanConfig()
	{
		return array(
			'view:DocumentUpdater' => array(
				'template' => 'DocumentUpdater.html' ,
			),
		);
	}

	public function process(){
		$aClassLoader = ClassLoader::singleton();
		$aPackageIterator = $aClassLoader->packageIterator();
		$arrTree = $this->getNamespaceTree($aPackageIterator);
		
		$this->DocumentUpdater->variables()->set('packageIterator',$aPackageIterator) ;
		$this->DocumentUpdater->variables()->set('arrtree',$arrTree);
	}
	
	private function getNamespaceTree($aPackageIterator){
		$arrTree =  array();
		foreach($aPackageIterator as $package){
			$ns = $package->ns();
			$arrNs = explode('\\',$ns);
			$arrExp = &$arrTree;
			foreach($arrNs as $ns_cl){
				if(empty($arrExp[$ns_cl])){
					$arrExp[$ns_cl] = array();
				}
				$arrExp = &$arrExp[$ns_cl];
			}
			$aFolder = $package->folder();
			$this->getFileTree($aFolder->url(false),$arrExp,$aFolder->path());
			$arrExp[] = $ns;
		}
		return $arrTree;
	}
	
	private function getFileTree($pathname , &$arr,$path){
		$aDirectoryIterator = new \DirectoryIterator($pathname);
		foreach($aDirectoryIterator as $fileinfo){
			if($fileinfo->isDot()) continue;
			
			$arrChild = array();
			if($fileinfo->isDir()){
				$this->getFileTree($fileinfo->getPathname(),$arrChild,$path.'/'.$fileinfo->getFilename());
			}else{
				$arrChild['ns'] = '';
				$arrChild['path'] = $path.'/'.$fileinfo->getFilename();
				$arrChild['fileinfo'] = $fileinfo;
			}
			$arr[$fileinfo->getFileName()] = $arrChild;
		}
	}
}

?>
