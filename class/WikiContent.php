<?php
namespace org\opencomb\doccenter ;

use org\jecat\framework\util\Version;
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
			'model:versions'=>array(
				'class'=>'model',
				'list'=>true,
				'orm'=>array(
					'table'=>'topic',
					'orderAsc'=>'version',
					'columns'=>array('version'),
					'keys'=>'version',
				)
			)
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
		
		//搜集版本列表
		$arrVersions = array();
		$sLastVersion = '';
		$this->modelVersions->load(array($sTitle),array('title'));
		foreach($this->modelVersions->childIterator() as $aVersion){
			$sStringVersion = (string)Version::from32Integer($aVersion['version']);
			$arrVersions[$sStringVersion]= $aVersion['version'];
			if(!$sLastVersion){
				$sLastVersion = $sStringVersion;
			}
		}
		
		//如果没有指定版本就显示最新版本
		if(empty($sVersion)){
			$sVersion = $sLastVersion;
		}
		//
		if(isset( $arrVersions[$sVersion] )){
			$s32Version = $arrVersions[$sVersion];
		}else{
			$this->messageQueue ()->create ( Message::error, "无法定位到指定文档,不存在的版本号" );
			return;
		}
		
		$this->modelWiki->load(array($sTitle,$s32Version),array('title','version'));
		if(!$this->modelWiki){
			$this->messageQueue ()->create ( Message::error, "无法定位到指定文档,不存在的title" );
			return;
		}
		
		$this->viewWikiContent->variables()->set('sTitle',array_pop(explode('/',$sTitle)) ) ;
			
		$this->viewWikiContent->variables()->set('arrVersions',array_keys($arrVersions) );
		$this->viewWikiContent->variables()->set('sSelectedVersion',$sVersion) ;
	}
}
