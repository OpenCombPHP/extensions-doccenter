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
				'config'=>'model/api'
			),
		);
	}
	
	public function process()
	{
// 		if($this->params->has("aid")){
// 			if(!$this->modelArticle->load(array($this->params->get("aid")),array('aid'))){
// 				$this->messageQueue ()->create ( Message::error, "错误的文章编号" );
// 			}
// 		}else{
// 			$this->messageQueue ()->create ( Message::error, "未指定文章" );
// 		}
	
// 		//浏览次数
// 		$this->modelArticle->setData("views",(int)$this->modelArticle->data("views")+1);
// 		$this->modelArticle->save();
	
// 		$this->viewArticle->variables()->set('article',$this->modelArticle) ;
	
// 		$this->setTitle($this->modelArticle->title);
	
// 		//把cid传给frame
// 		$this->frame()->params()->set('cid',$this->modelArticle->cid);
	}
}
