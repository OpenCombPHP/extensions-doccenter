<?php
namespace org\opencomb\doccenter\frame ;

use org\jecat\framework\mvc\model\db\IModel;

use org\jecat\framework\db\DB;
use org\jecat\framework\message\Message;
use org\opencomb\coresystem\mvc\controller\FrontFrame;

class DocFrontFrame extends FrontFrame
{
	public function createBeanConfig()
	{
		return  array(
			'frameview:DocFrameView' => array(
				'template' => 'DocFrame.html',
			) ,
			'model:api'=>array(
				'class'=>'model',
				'list'=>true,
				'orm'=>array(
					'table'=>'class',
					'limit'=>-1,
					'keys'=>array( 'extension','namespace','name','version' ),
				)
			),
		);
	}
	
	public function process(){
		$this->modelApi->load();
		
// 		$this->modelApi->printStruct();
// 		DB::singleton()->executeLog();
		
// 		$this->viewDocFrameView->variables()->set('aModelApi',$this->modelApi) ;

		$arrApiTree = $this->makeApiTree($this->modelApi);
		$this->viewDocFrameView->variables()->set('arrApiTree',json_encode($arrApiTree)) ;
		
// 		$arrWikiTree = $this->makeWikiTree($this->modelTree);
// 		$this->viewDocFrameView->variables()->set('arrWikiTree',$arrWikiTree) ;
		
	}
	
	private function makeApiTree(IModel $aModelApi){
		$arrTree = array();
		$arrParentChildren = &$arrTree;
		foreach($aModelApi->childIterator() as $aClass){
			$arrNamespace = explode('\\',$aClass['namespace']);
			$nKeyFound = -1; //-1 代表 "未找到",因为无法用 0
			//包
			foreach($arrNamespace as $aPath){
				foreach($arrParentChildren as $nKey => $aChild){
					if($aChild['name'] == $aPath){
						$nKeyFound = $nKey;
					}
				}
				if($nKeyFound != -1){
					$arrParentChildren = &$arrParentChildren[$nKeyFound]['children'];
				}else{
					$arrParentChildren[] = array(
							'name' => $aPath,
							'children' => array(),
					);
					$arrParentChildren = &$arrParentChildren[count($arrParentChildren)-1]['children'];
				}
				$nKeyFound=-1;
			}
			//类
			$arrParentChildren[] = array(
					'name' => $aClass['name']
					,'url' => '?c=org.opencomb.doccenter.DocContent&extension='.$aClass['extension'].'&namespace='.$aClass['namespace'].'&name='.$aClass['name']
					,'target' => '_self'
			);
			$arrParentChildren = &$arrTree;
		}
		return $arrTree;
	}
	private function makeWikiTree(IModel $aModelWiki){
		$arrTree = array();
		$arrParentChildren = &$arrTree;
		foreach($aModelWiki->childIterator() as $aClass){
			$arrNamespace = explode('\\',$aClass['namespace']);
			$nKeyFound = -1; //-1 代表 "未找到",因为无法用 0
			foreach($arrNamespace as $aPath){
				foreach($arrParentChildren as $nKey => $aChild){
					if($aChild['name'] == $aPath){
						$nKeyFound = $nKey;
					}
				}
				if($nKeyFound != -1){
					$arrParentChildren = &$arrParentChildren[$nKeyFound]['children'];
				}else{
					$arrParentChildren[] = array(
							'name' => $aPath,
							'children' => array(),
					);
					$arrParentChildren = &$arrParentChildren[count($arrParentChildren)-1]['children'];
				}
				$nKeyFound=-1;
			}
			$arrParentChildren[] = array(
					'name' => $aClass['name']
					,'url' => '?c=org.opencomb.doccenter.DocContent&extension='.$aClass['extension'].'&namespace='.$aClass['namespace'].'&name='.$aClass['name']
					,'target' => '_self'
			);
			$arrParentChildren = &$arrTree;
		}
		return $arrTree;
	}
}
?>