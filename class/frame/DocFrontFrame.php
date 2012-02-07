<?php
namespace org\opencomb\doccenter\frame;

use org\jecat\framework\mvc\model\db\IModel;
use org\jecat\framework\db\DB;
use org\jecat\framework\message\Message;
use org\opencomb\coresystem\mvc\controller\FrontFrame;

class DocFrontFrame extends FrontFrame {
	public function createBeanConfig() {
		return array ('frameview:DocFrameView' => array ('template' => 'DocFrame.html' ), 'model:api' => array ('class' => 'model', 'list' => true, 'orm' => array ('table' => 'class', 'limit' => - 1, 'keys' => array ('extension', 'namespace', 'name', 'version' ) ) ), 'model:wiki' => array ('class' => 'model', 'list' => true, 'orm' => array ('table' => 'topic', 'limit' => - 1 ) ) );
	}
	
	public function process() {
		$this->modelApi->load ();
		$arrApiTree = $this->makeApiTree ( $this->modelApi );
		$this->viewDocFrameView->variables ()->set ( 'arrApiTree', json_encode ( $arrApiTree ) );
		$this->modelWiki->load ();
		$arrWikiTree = $this->makeWikiTree ( $this->modelWiki );
		$this->viewDocFrameView->variables ()->set ( 'arrManualTree', json_encode ( $arrWikiTree ) );
	}
	
	private function makeApiTree(IModel $aModelApi) {
		$arrTree = array ();
		$arrParentChildren = &$arrTree;
		foreach ( $aModelApi->childIterator () as $aClass ) {
			$arrNamespace = explode ( '\\', $aClass ['namespace'] );
			$nKeyFound = - 1; // -1 代表 "未找到",因为无法用 0
			                 // 包
			foreach ( $arrNamespace as $aPath ) {
				foreach ( $arrParentChildren as $nKey => $aChild ) {
					if ($aChild ['name'] == $aPath) {
						$nKeyFound = $nKey;
					}
				}
				if ($nKeyFound != - 1) {
					$arrParentChildren = &$arrParentChildren [$nKeyFound] ['children'];
				} else {
					$arrParentChildren [] = array ('name' => $aPath, 'wholeName' => $aClass ['namespace'], 'children' => array (), 'iconOpen' => 'extensions/doccenter/0.1/public/img/package_open.png', 'iconClose' => 'extensions/doccenter/0.1/public/img/package_close.gif' );
					$arrParentChildren = &$arrParentChildren [count ( $arrParentChildren ) - 1] ['children'];
				}
				$nKeyFound = - 1;
			}
			// 类
			$arrParentChildren [] = array ('name' => $aClass ['name'], 'wholeName' => $aClass ['namespace'], 'url' => '?c=org.opencomb.doccenter.ApiContent&extension=' . $aClass ['extension'] . '&namespace=' . $aClass ['namespace'] . '&name=' . $aClass ['name'], 'target' => '_self', 'icon' => 'extensions/doccenter/0.1/public/img/class.gif' );
			$arrParentChildren = &$arrTree;
		}
		return $arrTree;
	}
	private function makeWikiTree(IModel $aModelWiki) {
		$arrTree = array ();
		$arrParentChildren = &$arrTree;
		foreach ( $aModelWiki->childIterator () as $aTopic ) {
			$arrNamespace = explode ( '/', $aTopic ['title'] );
			array_shift ( $arrNamespace ); // 弹出空的项
			$sTitle = array_pop ( $arrNamespace ); // 弹出标题项并另外保存
			$nKeyFound = - 1; // -1 代表 "未找到",因为无法用 0 做if判断
			$sPathUrl = '';
			foreach ( $arrNamespace as $aPath ) {
				$sPathUrl.='/'.$aPath;
				if($arrParentChildren){
					foreach ( $arrParentChildren as $nKey => $aChild ) {
						if ($aChild ['name'] == $aPath) {
							$nKeyFound = $nKey;
						}
					}
				}
				if ($nKeyFound != - 1) {
					$arrParentChildren = &$arrParentChildren [$nKeyFound] ['children'];
				} else {
					$arrParentChildren [] = array (
							'name' => $aPath, 
							'wholeName' => $sPathUrl, 
							'children' => array (), 
							'url' => '?c=org.opencomb.doccenter.WikiContent&title=' . $sPathUrl, 
							'target' => '_self' 
							);
					$arrParentChildren = &$arrParentChildren [count ( $arrParentChildren ) - 1] ['children'];
				}
				$nKeyFound = - 1;
			}
			// 如果有同名的文档就不再重复添加
			$bHasThisDoc = false;
			if (is_array ( $arrParentChildren )) {
				foreach ( $arrParentChildren as $aChild ) {
					$bHasThisDoc = $bHasThisDoc || $aChild ['name'] == $sTitle;
				}
			}
			if ($bHasThisDoc) {
				$arrParentChildren = &$arrTree;
				continue;
			}
			// 添加新文档
			$arrParentChildren [] = array (
					'name' => $sTitle, 
					'wholeName' => $aTopic ['title'], 
					'url' => '?c=org.opencomb.doccenter.WikiContent&title=' . $aTopic ['title'], 
					'target' => '_self' 
					);
			$arrParentChildren = &$arrTree;
		}
		return $arrTree;
	}
}
?>