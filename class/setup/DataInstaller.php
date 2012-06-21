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
"CREATE TABLE IF NOT EXISTS `doccenter_class` (
`namespace` varchar(50) NOT NULL,
`name` varchar(50) NOT NULL,
`version` int(11) NOT NULL,
`abstract` bool NOT NULL,
`comment` TEXT,
`extension` varchar(50) NOT NULL
)"
		);
		$aMessageQueue->create(Message::success,'新建数据表： `%s`成功',"doccenter_class");
		
		$aDB->execute(
"CREATE TABLE IF NOT EXISTS `doccenter_method` (
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
)"
		);
		$aMessageQueue->create(Message::success,'新建数据表： `%s`成功',"doccenter_method");
		
		$aDB->execute(
"CREATE TABLE IF NOT EXISTS `doccenter_parameter` (
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
)"
		);
		$aMessageQueue->create(Message::success,'新建数据表： `%s`成功',"doccenter_parameter");
		
		$aDB->execute(
"CREATE TABLE IF NOT EXISTS `doccenter_topic` (
`extension` varchar(50) NOT NULL,
`version` int(11) NOT NULL,
`title` varchar(50) NOT NULL,
`index` varchar(20) NOT NULL,
`text` TEXT NOT NULL,
`sourcePackageNamespace` varchar(50) NOT NULL,
`sourceClass` varchar(50) NOT NULL,
`sourceLine` int(6) NOT NULL
)"
		);
		$aMessageQueue->create(Message::success,'新建数据表： `%s`成功',"doccenter_topic");
		
		$aDB->execute(
"CREATE TABLE IF NOT EXISTS `doccenter_example` (
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
)"
		);
		$aMessageQueue->create(Message::success,'新建数据表： `%s`成功',"doccenter_example");
		
		$aDB->execute(
"CREATE TABLE IF NOT EXISTS `doccenter_example_class` (
`eid` int(11) NOT NULL,
`class` varchar(50) NOT NULL
)"
		);
		$aMessageQueue->create(Message::success,'新建数据表： `%s`成功',"doccenter_example_class");
		
		$aDB->execute(
"CREATE TABLE IF NOT EXISTS `doccenter_example_method` (
`eid` int(11) NOT NULL,
`method` varchar(50) NOT NULL
)"
		);
		$aMessageQueue->create(Message::success,'新建数据表： `%s`成功',"doccenter_example_method");
		
		$aDB->execute(
"CREATE TABLE IF NOT EXISTS `doccenter_example_topic` (
`eid` int(11) NOT NULL,
`topic_title` varchar(50) NOT NULL
)"
		);
		$aMessageQueue->create(Message::success,'新建数据表： `%s`成功',"doccenter_example_topic");
	}
}

