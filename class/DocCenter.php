<?php 
namespace org\opencomb\doccenter ;

use org\jecat\framework\lang\aop\AOP;
use org\opencomb\platform\ext\Extension ;

class DocCenter extends Extension 
{
	/**
	 * 载入扩展
	 */
	public function load()
	{
		AOP::singleton()->register('org\\opencomb\\doccenter\\aspect\\ControlPanelFrameAspect') ;
	}
}