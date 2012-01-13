<?php
namespace org\opencomb\doccenter\frame ;

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
				'orm'=>array(
					'table'=>'class',
					'name'=>'class',
					'keys'=>array( 'extension','namespace','name','version' ),
				)
			),
		);
	}
	
	public function process(){
		$sExtensionName = $this->params->has('extension') ? $this->params->has('extension') : "";
		$sNamespace = $this->params->has('namespace') ? $this->params->has('namespace') : "";
		$sName = $this->params->has('name') ? $this->params->has('name') : "";
		$sVersion = $this->params->has('version') ? $this->params->has('version') : "";
		
		if(empty($sExtensionName) || empty($sNamespace) ||empty($sName) ||empty($sVersion)){
			$this->messageQueue ()->create ( Message::error, "缺少信息,无法定位到指定文档" );
			return;
		}
		
		$this->modelApi->load(array($sExtensionName,$sNamespace,$sName,$sVersion),array('extension','namespace','name','version'));
		
		$this->viewDocFrameView->variables()->set('aModelApi',$this->modelApi) ;
	}
}
?>