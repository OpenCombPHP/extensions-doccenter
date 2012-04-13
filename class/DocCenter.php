<?php
namespace org\opencomb\doccenter;

use org\jecat\framework\bean\BeanFactory;
use org\opencomb\platform\mvc\view\widget\Menu;
use org\jecat\framework\lang\aop\AOP;
use org\opencomb\platform\ext\Extension;

class DocCenter extends Extension {
	/**
	 * 载入扩展
	 */
	public function load() {
		// 注册菜单build事件的处理函数
		Menu::registerBuildHandle(
				'org\\opencomb\\coresystem\\mvc\\controller\\ControlPanelFrame'
				, 'frameView'
				, 'mainMenu'
				, array(__CLASS__,'buildControlPanelMenu')
		) ;
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
