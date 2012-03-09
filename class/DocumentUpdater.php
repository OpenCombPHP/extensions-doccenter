<?php
namespace org\opencomb\doccenter;

use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\jecat\framework\lang\oop\ClassLoader;
use org\jecat\framework\lang\compile\CompilerFactory;
use org\jecat\framework\fs\Folder;
use org\jecat\framework\fs\FSIterator;

class DocumentUpdater extends ControlPanel {
	public function createBeanConfig() {
		return array ('view:DocumentUpdater' => array ('template' => 'DocumentUpdater.html' ) );
	}
	
	public function process() {
		$aClassLoader = ClassLoader::singleton ();
		$aPackageIterator = $aClassLoader->packageIterator ();
		$arrTree = $this->getNamespaceTree ( $aPackageIterator );
		
		$this->DocumentUpdater->variables ()->set ( 'packageIterator', $aPackageIterator );
		$this->DocumentUpdater->variables ()->set ( 'arrtree', $arrTree );
		$this->DocumentUpdater->variables ()->set ( 'classJson', json_encode ( $this->getTree ( $aPackageIterator ) ) );
	}
	
	private function getNamespaceTree($aPackageIterator) {
		$arrTree = array ();
		foreach ( $aPackageIterator as $package ) {
			$ns = $package->ns ();
			$arrNs = explode ( '\\', $ns );
			$arrExp = &$arrTree;
			foreach ( $arrNs as $ns_cl ) {
				if (empty ( $arrExp [$ns_cl] )) {
					$arrExp [$ns_cl] = array ();
				}
				$arrExp = &$arrExp [$ns_cl];
			}
			$aFolder = $package->folder ();
			$this->getFileTree ( $aFolder, $arrExp );
			$arrExp [] = $ns;
		}
		return $arrTree;
	}
	
	private function getFileTree(Folder $aFolder, &$arr) {
		$aFSIterator = $aFolder->iterator ( (FSIterator::FLAG_DEFAULT ^ FSIterator::RECURSIVE_SEARCH) | FSIterator::RETURN_FSO );
		if (! isset ( $arr ['files'] )) {
			$arr ['files'] = array ();
		}
		foreach ( $aFSIterator as $aFSO ) {
			$arrChild = array ();
			if ($aFSO instanceof Folder) {
				$this->getFileTree ( $aFSO, $arrChild );
				$arr [$aFSO->name ()] = $arrChild;
			} else {
				$arrChild ['ns'] = '';
				$arrChild ['path'] = $aFSO->path ();
				$arrChild ['FSO'] = $aFSO;
				$arrChild ['name'] = $aFSO->name ();
				$arr ['files'] [] = $arrChild;
			}
		}
	}
	
	private function getTree($aPackageIterator) {
		$arrTree = array ();
		foreach ( $aPackageIterator as $package ) {
			$ns = $package->ns ();
			$arrNs = explode ( '\\', $ns );
			$arrExp = &$arrTree;
			foreach ( $arrNs as $ns_cl ) {
				$bFound = false;
				for($i = 0; $i < count ( $arrExp ); $i ++) {
					if (isset ( $arrExp [$i] ['name'] ) && $arrExp [$i] ['name'] == $ns_cl) {
						$arrExp = &$arrExp [$i] ['children'];
						$bFound = true;
						break;
					}
				}
				if (! $bFound) {
					$arrExp [] = array ('name' => $ns_cl, 'children' => array () );
					$arrExp = &$arrExp [count ( $arrExp ) - 1] ['children'];
				}
			}
			$aFolder = $package->folder ();
			$arrExp = $this->buildNode ( $aFolder );
		}
		return $arrTree;
	}
	
	private function buildNode(Folder $aFolder) {
		$arrNode = array ();
		$aFSIterator = $aFolder->iterator ( (FSIterator::FLAG_DEFAULT ^ FSIterator::RECURSIVE_SEARCH) | FSIterator::RETURN_FSO );
		foreach ( $aFSIterator as $aFSO ) {
			if ($aFSIterator->isFolder ()) {
				$arrNode [] ['children'] = $this->buildNode ( $aFSO );
				$arrNode [count ( $arrNode ) - 1] ['name'] = $aFSO->name ();
			} else {
				$arrNode [] = array ('name' => substr ( $aFSO->name (), 0, strlen ( $aFSO->name () ) - 4 ), 'filepath' => $aFSO->path () );
			}
		}
		return $arrNode;
	}
}

?>
