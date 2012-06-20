<?php
namespace org\opencomb\doccenter\generator;

use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\db\DB;
use org\jecat\framework\db\sql\Insert;

class WikiGenerator implements IGenerator {
	public function generate(TokenPool $aTokenPool, FileInfo $aFileInfo) {
		$arrWikis = array ();
		$aIterator = $aTokenPool->iterator ();
		foreach ( $aIterator as $aToken ) {
			if ($aToken->tokenType () === T_DOC_COMMENT) {
				if (! $aToken instanceof \org\jecat\framework\lang\compile\object\DocCommentDeclare) {
					$aToken = new \org\jecat\framework\lang\compile\object\DocCommentDeclare ( $aToken );
				}
				$aDocComment = $aToken->docComment ();
				if (! $aDocComment->hasItem ( 'wiki' ))
					continue;
				
				$sText = $aDocComment->description ();
				
				$iLine = $aToken->line ();
				
				$arrCommentItemWiki = $aDocComment->items ( 'wiki' );
				foreach ( $arrCommentItemWiki as $sCommentItemWiki ) {
					$arrWiki = array ();
					/**
					 * @wiki /文档中心/wiki语法[wiki]
					 * wiki语法是 @wiki title[index]
					 * 如果没有index ，请省略方括号
					*/
					if(preg_match( '|^(.*)\[(.*)\]$|' , $sCommentItemWiki , $arrWikiMatch)){
						$arrWiki['title'] = $arrWikiMatch[1];
						$arrWiki['index'] = $arrWikiMatch[2];
					}else{
						$arrWiki['title'] = $sCommentItemWiki ;
						$arrWiki['index'] = '';
					}
					$arrWiki ['text'] = $sText;
					$arrWiki ['extension'] = $aFileInfo->extension ();
					$arrWiki ['version'] = $aFileInfo->version ();
					$arrWiki ['sourceLine'] = $iLine;
					
					$arrWiki ['sourcePackageNamespace'] = $aFileInfo->sourcePackageNamespace ();
					$arrWiki ['sourceClass'] = $aFileInfo->sourceClass ();
					
					$arrWikis [] = $arrWiki;
				}
			}
		}
		return $arrWikis;
	}
	
	public function cleanInDB(FileInfo $aFileInfo, DB $aDB) {
		$extension = $aFileInfo->extension() ;
		$version = $aFileInfo->version() ;
		$sourcePackageNamespace = $aFileInfo->sourcePackageNamespace() ;
		$sourceClass = $aFileInfo->sourceClass() ;
		$aDB->execute ( 'DELETE FROM
					doccenter_topic
				WHERE (
					`extension` = "' . $extension . '"
					AND `version` = "' . $version . '"
					AND `sourcePackageNamespace` = "' . addslashes ( $sourcePackageNamespace ) . '"
					AND `sourceClass` = "' . addslashes ( $sourceClass ) . '"
				)' );
		return TRUE;
	}
	
	public function saveInDB(array $arrGenerate, DB $aDB) {
		$arrKeyExample = array ('extension', 'version', 'title', 'index', 'text', 'sourcePackageNamespace', 'sourceClass', 'sourceLine' );
		foreach ( $arrGenerate as $generate ) {
			// wiki
			$aWikiInsert = new Insert ( 'doccenter_topic' );
			foreach ( $arrKeyExample as $sKey ) {
				$aWikiInsert->setData ( $sKey , $generate [$sKey] );
			}
			$aDB->execute ( $aWikiInsert );
		}
		return TRUE;
	}
}
