<?php
namespace org\opencomb\doccenter ;

use org\opencomb\coresystem\mvc\controller\ControlPanel ;
use org\jecat\framework\lang\oop\ClassLoader;
use org\jecat\framework\lang\compile\CompilerFactory;
use org\jecat\framework\fs\IFolder ;
use org\jecat\framework\fs\FSIterator ;

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
			$this->getFileTree($aFolder,$arrExp);
			$arrExp[] = $ns;
		}
		return $arrTree;
	}
	
	private function getFileTree(IFolder $aFolder, &$arr){
		$aFSIterator = $aFolder->iterator( ( FSIterator::FLAG_DEFAULT ^ FSIterator::RECURSIVE_SEARCH ) | FSIterator::RETURN_FSO );
		foreach($aFSIterator as $aFSO){
			$arrChild = array();
			if($aFSO instanceof IFolder ){
				$this->getFileTree($aFSO,$arrChild);
			}else{
				$arrChild['ns'] = '';
				$arrChild['path'] = $aFSO->path();
				$arrChild['FSO'] = $aFSO;
			}
			$arr[$aFSO->name()] = $arrChild;
		}
	}
}

?>
