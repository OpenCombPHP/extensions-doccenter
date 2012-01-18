<?php
namespace org\opencomb\doccenter ;

use org\jecat\framework\db\DB;

use org\opencomb\doccenter\frame\DocFrontController;
use org\jecat\framework\message\Message;
use org\opencomb\coresystem\mvc\controller\Controller;

class WikiContent extends DocFrontController{
	public function createBeanConfig()
	{
		return array(
			'title'=> '文档内容',
			'view:wikiContent'=>array(
				'template'=>'WikiContent.html',
				'class'=>'view',
				'model'=>'wiki',
			),
			'model:wiki'=>array(
				'class'=>'model',
				'list'=>true,
				'orm'=>array(
					'table'=>'topic',
					'hasAndBelongsToMany:examples'=>array(
						'fromkeys'=>array( 'title' ),
						'tobridgekeys'=>array( 'topic_title' ),
						'frombridgekeys'=>'eid',
						'tokeys'=>'eid',
						'table'=>'example',
						'bridge'=>'example_topic',
					)
				)
			),
		);
	}
	
	public function process()
	{
		$sTid = $this->params->has('tid') ? $this->params->get('tid') : "";
		$sVersion = $this->params->has('version') ? $this->params->get('version') : "";
		
		if(empty($sTid)){
			$this->messageQueue ()->create ( Message::error, "缺少信息,无法定位到指定文档" );
			return;
		}
		
		if($sVersion == ''){
			$this->modelWiki->load(array($sTid),array('tid'));
		}else{
			$this->modelWiki->load(array($sTid,$sVersion),array('tid','version'));
		}
		
// 		DB::singleton()->executeLog();
// 		$this->modelWiki->printStruct();
		
		$this->viewWikiContent->variables()->set('aModelWiki',$this->modelWiki) ;
	}
}
