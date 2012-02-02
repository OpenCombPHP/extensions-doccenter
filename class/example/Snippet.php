<?php
namespace org\opencomb\doccenter\example;

use org\jecat\framework\lang\Object;
use org\jecat\framework\mvc\model\db\Model;

class Snippet extends Object {
	public function __construct(Model $aModel) {
		$arrKeys = array ('extension', 'version', 'title', 'name', 'index', 'code', 'sourcePackageNamespace', 'sourceClass', 'sourceLine' );
		foreach ( $arrKeys as $sKey ) {
			$this->$sKey = $aModel->data ( $sKey );
		}
	}
	
	public function extension() {
		return $this->extension;
	}
	
	public function version() {
		return $this->version;
	}
	
	public function title() {
		return $this->title;
	}
	
	public function name() {
		return $this->name;
	}
	
	public function index() {
		return $this->index;
	}
	
	public function code() {
		return $this->code;
	}
	
	public function sourcePackageNamespace() {
		return $this->sourcePackageNamespace;
	}
	
	public function sourceClass() {
		return $this->sourceClass;
	}
	
	public function sourceLine() {
		return $this->sourceLine;
	}
	
	private $extension = '';
	
	private $version = '';
	
	private $title = '';
	
	private $name = '';
	
	private $index = '';
	
	private $code = '';
	
	private $sourcePackageNamespace = '';
	
	private $sourceClass = '';
	
	private $sourceLine = '';
}
