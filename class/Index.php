<?php
namespace org\opencomb\doccenter;

use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\jecat\framework\lang\oop\ClassLoader;

class Index extends ControlPanel {
	public function createBeanConfig() {
		return array ('view:index' => array ('template' => 'index.html' ) );
	}
}

?>
