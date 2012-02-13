<?php
namespace org\opencomb\doccenter;

use org\opencomb\platform\ext\Extension;
use org\jecat\framework\mvc\model\IModel;
use org\jecat\framework\db\DB;
use org\jecat\framework\util\Version;
use org\opencomb\doccenter\frame\DocFrontController;
use org\jecat\framework\message\Message;

class WikiContent extends DocFrontController {
	public function createBeanConfig() {
		return array (
			'title' => '手册内容', 
			'view:wikiContent' => array (
					'template' => 'WikiContent.html', 
					'class' => 'view', 
					'model' => 'wiki' ), 
			'model:wiki' => array (
					'class' => 'model', 
					'list' => true, 
					'orm' => array (
							'table' => 'topic', 
							'orderAsc' => 'version',
							'hasAndBelongsToMany:examples' => array (
									'fromkeys' => array ('title' ), 
									'tobridgekeys' => array ('topic_title' ), 
									'frombridgekeys' => 'eid', 
									'tokeys' => 'eid', 
									'table' => 'example', 
									'bridge' => 'example_topic', 
									'orderAsc'=>'index'
									)
							)
					),
			'model:versions' => array (
					'class' => 'model', 
					'list' => true, 
					'orm' => array (
							'table' => 'topic',
							'orderAsc' => 'version', 
							'columns' => array (
									'version'
									 ), 
							'keys' => 'version'
							) 
					) 
			);
	}
	
	public function process() {
		$sTitle = $this->params->has ( 'title' ) ? $this->params->get ( 'title' ) : "";
		$sVersion = $this->params->has ( 'version' ) ? $this->params->get ( 'version' ) : "";
		
		if (empty ( $sTitle )) {
			$this->messageQueue ()->create ( Message::error, "无法定位到指定文档,缺少信息" );
			return;
		}
		// 搜集版本列表
		$arrVersions = array ();
		$sLastVersion = '';
		$this->modelVersions->load ( array ($sTitle ), array ('title' ) );
		foreach ( $this->modelVersions->childIterator () as $aVersion ) {
			$sStringVersion = ( string ) Version::from32Integer ( $aVersion ['version'] );
			$arrVersions [$sStringVersion] = $aVersion ['version'];
			if (! $sLastVersion) {
				$sLastVersion = $sStringVersion;
			}
		}
		
		// 如果没有指定版本就显示最新版本
		if (empty ( $sVersion )) {
			$sVersion = $sLastVersion;
		}
		//
		if (isset ( $arrVersions [$sVersion] )) {
			$s32Version = $arrVersions [$sVersion];
		} else {
			$this->messageQueue ()->create ( Message::error, "无法定位到指定文档,不存在的版本号" );
			return;
		}
		
		$this->modelWiki->load ( array ($sTitle, $s32Version ), array ('title', 'version' ) );
		if (! $this->modelWiki) {
			$this->messageQueue ()->create ( Message::error, "无法定位到指定文档,不存在的title" );
			return;
		}
		
		$this->setTitle($sTitle . " -- Opencomb Wiki");
		
		$this->viewWikiContent->variables ()->set ( 'sTitle', array_pop ( explode ( '/', $sTitle ) ) );
		
		$this->viewWikiContent->variables ()->set ( 'arrVersions', array_keys ( $arrVersions ) );
		
		$this->viewWikiContent->variables ()->set ( 'sSelectedVersion', $sVersion );
	}
	/**
	 * 整理例子,合并例子代码
	 * @return array 
	 */
	public function mergeExamples(IModel $aExamplesModel){
		$arrModels = array();
		foreach($aExamplesModel->childIterator() as $aModel){
			$arrModels[$aModel['name']][] = $aModel;
		}
		return $arrModels;
	}
	
	/**
	 * 来源提示,依赖提示
	 */
	public function translateExtension($aContentModel){
		$sTranslatedExtension = "<div class='extensioninfo'><label>来源: </label><span class='docversion'>";
		$sTranslatedExtensionEnd = '';
		switch($aContentModel['extension'])
		{
			case "framework":
				$sTranslatedExtension .= "Jecat框架";
				break;
			case "platform":
				$sTranslatedExtension .= "蜂巢平台";
				break;
			default:
				$sExtensionName = '未知扩展';
				if($aExtension = Extension::flyweight($aContentModel['extension'],false)){
					$sExtensionName = $aExtension->metainfo()->title();
				}
				$sTranslatedExtension.= $sExtensionName .  "(" . $aContentModel['extension'] . ")";
				$sTranslatedExtensionEnd = "<div class='extensionWarning'><span>*依赖扩展：" . $sExtensionName .  "(" . $aContentModel['extension'] . ")</span></div>";
		}
		
		$sTranslatedExtension.=" </span><label>版本: </label>".Version::from32Integer($aContentModel['version']);
		$sTranslatedExtension.=" <label>类: </label><a href='#'>".$aContentModel['sourceClass']."</a>";
		$sTranslatedExtension.=" <label>行: </label>".$aContentModel['sourceLine']."</div>";
		$sTranslatedExtension.=$sTranslatedExtensionEnd;
		return $sTranslatedExtension;
	}
	/**
	 * 获取例子所在文件的路径
	 * @return string 
	 */
	public function getExamplePath(IModel $aExampleModel){
		$sSourceClass = $aExampleModel['sourceClass'];
		$sSourcePackageNamespace = $aExampleModel['sourcePackageNamespace'];
		$sExtension = $aExampleModel['extension'];
		foreach(Extension::flyweight($sExtension)->metainfo()->packageIterator() as $arrPackage){
			list($sNamespace,$sPackagePath) = $arrPackage ;
			if($sNamespace == $sSourcePackageNamespace){
				return Extension::flyweight($sExtension)->metainfo()->installPath().$sPackagePath . str_replace( '\\','/', substr($sSourceClass , strLen($sSourcePackageNamespace)) ).'.php';
			}
		}
	}
}

/*
 * 暂时废除的版本切换部分
<label>版本: </label>
<select class='version'>
<foreach for='$arrVersions' item='sVersions'>
<option value="{='?c=org.opencomb.doccenter.WikiContent&version=' . $sVersions . '&title=' . $theParams->get('title')}"
{=$sSelectedVersion == $sVersions ? 'selected' : ''}>{
	=$sVersions}</option>
	</foreach>
	</select>
	*/