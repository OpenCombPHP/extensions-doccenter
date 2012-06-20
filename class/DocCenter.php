<?php
namespace org\opencomb\doccenter;

use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\jecat\framework\bean\BeanFactory;
use org\opencomb\platform\mvc\view\widget\Menu;
use org\jecat\framework\lang\aop\AOP;
use org\opencomb\platform\ext\Extension;

class DocCenter extends Extension {
	/**
	 * 载入扩展
	 */
	public function load() {
		ControlPanel::registerMenuHandler( array(__CLASS__,'buildControlPanelMenu') ) ;
	}
	
	static public function buildControlPanelMenu(array & $arrConfig)
	{
		// 合并配置数组，增加菜单
		BeanFactory::mergeConfig(
				$arrConfig
				, BeanFactory::singleton()->findConfig('widget/control-panel-frame-menu','doccenter')
		) ;
	}
}
