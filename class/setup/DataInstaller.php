<?php
namespace org\opencomb\doccenter\setup;

use org\jecat\framework\db\DB;
use org\jecat\framework\message\Message;
use org\jecat\framework\message\MessageQueue;
use org\opencomb\platform\ext\ExtensionMetainfo;
use org\opencomb\platform\ext\IExtensionDataInstaller;

class DataInstaller implements IExtensionDataInstaller
{
	public function install(MessageQueue $aMessageQueue,ExtensionMetainfo $aMetainfo)
	{
		// create data table
		$aDB = DB::singleton() ;
		
		$aDB->execute(
"CREATE TABLE IF NOT EXISTS `".$aDB->transTableName("doccenter:class")."` (
`namespace` varchar(50) NOT NULL,
`name` varchar(50) NOT NULL,
`version` int(11) NOT NULL,
`abstract` bool NOT NULL,
`comment` TEXT,
`extension` varchar(50) NOT NULL
)ENGINE=MyISAM DEFAULT CHARSET=utf8"
		);
		$aMessageQueue->create(Message::success,'新建数据表： `%s`成功',$aDB->transTableName("doccenter:class"));
		
		$aDB->execute(
"CREATE TABLE IF NOT EXISTS `".$aDB->transTableName("doccenter:method")."` (
`name` varchar(50) NOT NULL,
`version` int(11) NOT NULL,
`class` varchar(50) NOT NULL,
`namespace` varchar(50) NOT NULL,
`extension` varchar(50) NOT NULL,
`access` varchar(15) NOT NULL,
`abstract` bool NOT NULL,
`static` bool NOT NULL,
`returnType` varchar(20),
`returnByRef` bool NOT NULL,
`comment` TEXT
)ENGINE=MyISAM DEFAULT CHARSET=utf8"
		);
		$aMessageQueue->create(Message::success,'新建数据表： `%s`成功',$aDB->transTableName("doccenter:method"));
		
		$aDB->execute(
"CREATE TABLE IF NOT EXISTS `".$aDB->transTableName("doccenter:parameter")."` (
`version` int(11) NOT NULL,
`namespace` varchar(50) NOT NULL,
`class` varchar(50) NOT NULL,
`method` varchar(50) NOT NULL,
`extension` varchar(50) NOT NULL,
`default` varchar(50),
`type` varchar(50),
`name` varchar(30) NOT NULL,
`byRef` bool NOT NULL,
`comment` TEXT
)ENGINE=MyISAM DEFAULT CHARSET=utf8"
		);
		$aMessageQueue->create(Message::success,'新建数据表： `%s`成功',$aDB->transTableName("doccenter:parameter"));
		
		$aDB->execute(
"CREATE TABLE IF NOT EXISTS `".$aDB->transTableName("doccenter:topic")."` (
`extension` varchar(50) NOT NULL,
`version` int(11) NOT NULL,
`title` varchar(50) NOT NULL,
`index` varchar(20) NOT NULL,
`text` TEXT NOT NULL,
`sourcePackageNamespace` varchar(50) NOT NULL,
`sourceClass` varchar(50) NOT NULL,
`sourceLine` int(6) NOT NULL
)ENGINE=MyISAM DEFAULT CHARSET=utf8"
		);
		$aMessageQueue->create(Message::success,'新建数据表： `%s`成功',$aDB->transTableName("doccenter:topic"));
		
		$aDB->execute(
"CREATE TABLE IF NOT EXISTS `".$aDB->transTableName("doccenter:example")."` (
`eid` int(11) NOT NULL AUTO_INCREMENT,
`extension` varchar(50) NOT NULL,
`version` int(11) NOT NULL,
`title` varchar(50) NOT NULL,
`name` varchar(20),
`index` int(6) ,
`code` TEXT NOT NULL,
`sourcePackageNamespace` varchar(50) NOT NULL,
`sourceClass` varchar(50) NOT NULL,
`sourceLine` int(6) NOT NULL,
`sourceEndLine` int(6) NOT NULL,
PRIMARY KEY (`eid`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8"
		);
		$aMessageQueue->create(Message::success,'新建数据表： `%s`成功',$aDB->transTableName("doccenter:example"));
		
		$aDB->execute(
"CREATE TABLE IF NOT EXISTS `".$aDB->transTableName("doccenter:example_class")."` (
`eid` int(11) NOT NULL,
`class` varchar(50) NOT NULL
)ENGINE=MyISAM DEFAULT CHARSET=utf8"
		);
		$aMessageQueue->create(Message::success,'新建数据表： `%s`成功',$aDB->transTableName("doccenter:example_class"));
		
		$aDB->execute(
"CREATE TABLE IF NOT EXISTS `".$aDB->transTableName("doccenter:example_method")."` (
`eid` int(11) NOT NULL,
`method` varchar(50) NOT NULL
)ENGINE=MyISAM DEFAULT CHARSET=utf8"
		);
		$aMessageQueue->create(Message::success,'新建数据表： `%s`成功',$aDB->transTableName("doccenter:example_method"));
		
		$aDB->execute(
"CREATE TABLE IF NOT EXISTS `".$aDB->transTableName("doccenter:example_topic")."` (
`eid` int(11) NOT NULL,
`topic_title` varchar(50) NOT NULL
)ENGINE=MyISAM DEFAULT CHARSET=utf8"
		);
		$aMessageQueue->create(Message::success,'新建数据表： `%s`成功',$aDB->transTableName("doccenter:example_topic"));
	}
}

