<?php
namespace org\opencomb\doccenter\example ;

use org\jecat\framework\lang\Object;
use org\jecat\framework\mvc\model\IModel ;
use org\jecat\framework\mvc\model\db\orm\Prototype ;
use org\jecat\framework\mvc\model\db\Model ;

class Example extends Object{
	
	/**
	 * @return array
	 */
	static public function processModel(IModel $aModel){
		$arrExample = array();
		foreach($aModel->childIterator() as $aChildModel){
			$sName = $aChildModel->data('name');
			$aSnippet = new Snippet($aChildModel) ;
			if(empty($sName)){
				$aExample = new Example ;
				$aExample->addSnippet($aSnippet) ;
				$arrExample [] = $aExample ;
			}else{
				if(isset($arrExample[$sName])){
					$aExample = $arrExample[$sName] ;
					$aExample->addSnippet($aSnippet);
				}else{
					$aExample = new Example ;
					$aExample->addSnippet($aSnippet);
					$arrExample[$sName] = $aExample ;
				}
			}
		}
		return $arrExample ;
	}
	
	/**
	 * @return IModel
	 */
	static public function createModel($sTitle , $sName = ''){
		$aPrototype = 
			Prototype::create(
				"doccenter_example",
				'eid',
				array(
					'extension',
					'version',
					'title',
					'name',
					'index',
					'code',
					'sourcePackageNamespace',
					'sourceClass',
					'sourceLine',
				)
			)
			->done() ;
		$aModel = new Model($aPrototype , true);
		if(empty($sName)){
			$aModel->load(array($sTitle),array('title',));
		}else{
			$aModel->load(array($sTitle,$sName),array('title','name'));
		}
		return $aModel ;
	}
	
	public function addSnippet(Snippet $aSnippet){
		$this->arrSnippet[] = $aSnippet ;
	}
	
	public function iterator(){
		return new \ArrayIterator($this->arrSnippet) ;
	}
	
	private $arrSnippet = array();
}
