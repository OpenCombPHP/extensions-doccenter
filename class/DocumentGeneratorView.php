<?php
namespace org\opencomb\doccenter;

use org\opencomb\coresystem\mvc\controller\ControlPanel;

class DocumentGeneratorView extends ControlPanel {
	public function createBeanConfig() {
		return array ('view:DocumentGeneratorView' => array ('template' => 'DocumentGeneratorView.html' ) );
	}
	
	public function process() {
		$this->aDocumentGenerator = new DocumentGenerator ();
		
		$arrPath = $this->params ['path'];
		$arrResult = array ();
		
		foreach ( $arrPath as $path ) {
			$arrResult [] = $this->aDocumentGenerator->generate ( $path );
		}
		
		$this->DocumentGeneratorView->variables ()->set ( 'result', $arrResult );
	}
	
	private $aDocumentGenerator;
}
