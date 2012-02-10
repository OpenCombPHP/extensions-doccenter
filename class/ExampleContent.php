<?php
namespace org\opencomb\doccenter;

use org\opencomb\coresystem\auth\Id;
use org\jecat\framework\message\Message;
use org\opencomb\doccenter\frame\DocFrontController;

class ExampleContent extends DocFrontController {
	public function createBeanConfig() {
		return array (
			'title' => '示例代码查看', 
			'view:viewer' => array (
				'template' => 'ExampleContent.html', 
				'class' => 'view',
				'model' => 'example',
			),
			'model:example' => array (
				'class' => 'model',
				'orm' => array (
					'table' => 'example',
				)
			)
		);
	}
	
	public function process() {
		if(!$this->params->has('title')){
			$this->messageQueue ()->create ( Message::error, "无法定位到指定代码,缺少信息" );
		}
		
		$this->modelExample->load(array($this->params->get('title')) , array('title'));
		
	}
}