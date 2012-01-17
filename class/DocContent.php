<?php
namespace org\opencomb\doccenter ;

use org\opencomb\doccenter\frame\DocFrontController;
use org\jecat\framework\message\Message;
use org\opencomb\coresystem\mvc\controller\Controller;

class DocContent extends DocFrontController{
	public function createBeanConfig()
	{
		return array(
			'title'=> '文档内容',
			'view:classContent'=>array(
				'template'=>'DocContent.html',
				'class'=>'view',
				'model'=>'api',
			),
			'model:api'=>array(
				'class'=>'model',
				'list'=>true,
				'orm'=>array(
					'table'=>'class',
					'keys'=>array( 'extension','namespace','name','version' ),
					'hasMany:methods'=>array(
						'fromkeys'=>array( 'extension','namespace','name','version' ),
						'tokeys'=>array( 'extension','namespace','name','version' ),
						'table'=>'method',
						'keys'=>array('extension', 'namespace','class','name','version'),
						'orm'=>array(
							'hasMany:parameters'=>array(
								'fromkeys'=>array('version','name','class','namespace'),
								'tokeys'=>array('version','method','class','namespace'),
								'table'=>'parameter',
								'orm'=>array(
									'keys'=>array('extension', 'namespace','class','method','name','version'),
								)
							)
						)
					)
				)
			),
		);
	}
	
	public function process()
	{
		$sExtensionName = $this->params->has('extension') ? $this->params->get('extension') : "";
		$sNamespace = $this->params->has('namespace') ? $this->params->get('namespace') : "";
		$sName = $this->params->has('name') ? $this->params->get('name') : "";
		$sVersion = $this->params->has('version') ? $this->params->get('version') : "";
		
		if(empty($sExtensionName) || empty($sNamespace) ||empty($sName)){
			$this->messageQueue ()->create ( Message::error, "缺少信息,无法定位到指定文档" );
			return;
		}
		
		$this->modelApi->load(array($sExtensionName,$sNamespace,$sName),array('extension','namespace','name'));
		
		$this->viewClassContent->variables()->set('aModelApi',$this->modelApi) ;
	}
}
