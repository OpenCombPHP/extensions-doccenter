<?php
namespace org\opencomb\doccenter ;

use org\opencomb\coresystem\mvc\controller\ControlPanel ;
use org\jecat\framework\mvc\model\db\orm\Prototype ;
use org\jecat\framework\mvc\model\db\Model ;

// /?c=org.opencomb.doccenter.DocumentView&version=4194304&class=testCompiler1&ns=org\opencomb\doccenter

class DocumentView extends ControlPanel{
	public function createBeanConfig(){
		return array(
			'view:DocumentView' => array(
				'template' => 'DocumentView.html' ,
			),
		);
	}
	
	public function process(){
		// params
		$sClassName = $this->params['class'];
		$sNamespace = $this->params['ns'];
		$nVersion = $this->params['version'];
		// model
		$aModel = $this->getModel($sClassName,$sNamespace,$nVersion);
		// variable for template
		$this->DocumentView->variables()->set('model',$aModel) ;
		$this->DocumentView->variables()->set('className',$sClassName) ;
		$this->DocumentView->variables()->set('namespace',$sNamespace) ;
		$this->DocumentView->variables()->set('version',$nVersion) ;
	}
	
	private function getModel($sClassName,$sNamespace,$nVersion){
		$aModel = new Model($this->getPrototype());
		$aModel->load(
			array(
				$sClassName,
				$sNamespace,
				$nVersion,
			),
			array(
				'name',
				'namespace',
				'version'
			)
		);
		return $aModel ;
	}
	
	private function getPrototype(){
		if(null === $this->aPrototype){
			$this->aPrototype =
			Prototype::create("doccenter_class",null,array('version','name','abstract','namespace','comment'))
				->setName('class')
				->setKeys(array('namespace','name','version'))
	
				->hasMany('doccenter_method',array('version','name','namespace'),array('version','class','namespace'))
					->setName('function')
					->setKeys(array('namespace','class','name','version'))
					->addColumns('version','name','class','namespace','access','abstract','static','returnType','returnByRef','comment')

					->hasMany('doccenter_parameter',array('version','name','class','namespace'),array('version','method','class','namespace'))
						->setName('param')
						->setKeys(array('version','namespace','class','method','name'))
						->addColumns('version','namespace','class','method','name','type','default','byRef','comment')
					->done()
				->done()
			->done() ;
		}
		return $this->aPrototype;
	}
	
	private $aPrototype = null ;
}
