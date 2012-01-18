<?php
namespace org\opencomb\doccenter ;

use org\opencomb\doccenter\frame\DocFrontController;
use org\jecat\framework\message\Message;

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
					'orderAsc'=>'version',
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
		$sTitle = $this->params->has('title') ? $this->params->get('title') : "";
		$sVersion = $this->params->has('version') ? $this->params->get('version') : "";
		
		if(empty($sTitle)){
			$this->messageQueue ()->create ( Message::error, "无法定位到指定文档,缺少信息" );
			return;
		}
		
		$this->modelWiki->load(array($sTitle),array('title'));
		if(!$this->modelWiki){
			$this->messageQueue ()->create ( Message::error, "无法定位到指定文档,不存在的title" );
			return;
		}
// 		$this->modelWiki->printStruct();
		$this->viewWikiContent->variables()->set('sTitle',array_pop(explode('/',$sTitle)) ) ;
		$this->viewWikiContent->variables()->set('sVersion',$sVersion) ;
	}
}
