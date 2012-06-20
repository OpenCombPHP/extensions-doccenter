<?php
namespace org\opencomb\doccenter\generator;

use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\db\DB;
use org\jecat\framework\db\sql\Insert;

class ExampleGenerator implements IGenerator {
	public function generate(TokenPool $aTokenPool, FileInfo $aFileInfo) {
		$arrDoc = array ();
		$aIterator = $aTokenPool->iterator ();
		foreach ( $aIterator as $aToken ) {
			if ($aToken->tokenType () === T_DOC_COMMENT) {
				if (! $aToken instanceof \org\jecat\framework\lang\compile\object\DocCommentDeclare) {
					$aToken = new \org\jecat\framework\lang\compile\object\DocCommentDeclare ( $aToken );
				}
				$aDocComment = $aToken->docComment ();
				if (! $aDocComment->hasItem ( 'example' ))
					continue;
				
				$arrExample = array ();
				
				$sTitle = '';
				$sName = '';
				$iIndex = 0;
				$sCode = '';
				
				$sExample = $aDocComment->item ( 'example' );
				$arrExampleMatch = array ();
				if (preg_match ( '|^(.*):(.*)\[(.*)\]$|', $sExample, $arrExampleMatch )) {
					$sTitle = $arrExampleMatch [1];
					$sName = $arrExampleMatch [2];
					$iIndex = ( int ) $arrExampleMatch [3];
				} else if (preg_match ( '|^(.*):(.*)$|', $sExample, $arrExampleMatch )) {
					$sTitle = $arrExampleMatch [1];
					$sName = $arrExampleMatch [2];
				} else {
					$arrExampleMatch [] = $sExample;
					$sTitle = $sExample;
				}
				$arrExample ['title'] = $sTitle;
				$arrExample ['name'] = $sName;
				$arrExample ['index'] = $iIndex;
				
				$sForClass = $aDocComment->item ( 'forclass' );
				$arrExample ['forclass'] = $sForClass;
				
				$sForMethod = $aDocComment->item ( 'formethod' );
				$arrExample ['formethod'] = $sForMethod;
				
				$arrForWikiList = $aDocComment->items ( 'forwiki' );
				$arrExample ['forwiki'] = $arrForWikiList;
				
				$arrExample ['extension'] = $aFileInfo->extension ();
				$arrExample ['version'] = $aFileInfo->version ();
				
				$iLine = $aToken->line ();
				$arrExample ['sourceLine'] = $iLine;
				
				$aUseInExample = new UseInExample ();
				
				$aCodeBeginToken = null;
				$aCodeEndToken = null;
				$aFolIterator = clone $aIterator;
				while ( $aFolIterator->valid () ) {
					$aFolToken = $aFolIterator->current ();
					switch ($aFolToken->tokenType ()) {
						case Token::T_BRACE_OPEN :
							if ($aCodeBeginToken === null) {
								$aCodeBeginToken = $aFolToken;
							}
							break;
						case Token::T_BRACE_CLOSE :
							if ($aCodeBeginToken !== null and $aCodeBeginToken->theOther () === $aFolToken) {
								$aCodeEndToken = $aFolToken;
								break;
							}
							break;
						case T_STRING :
							$aUseInExample->processToken ( $aFolToken, $aTokenPool );
							break;
					}
					$sCode .= $aFolToken->__toString ();
					if ($aCodeEndToken !== null) {
						break;
					}
					
					$aFolIterator->next ();
				}
				
				$iEndLine = $aCodeEndToken->line();
				$arrExample ['sourceEndLine'] = $iEndLine;
				
				$arrExample ['code'] = $aUseInExample->codeForUseList () . "\n" . $sCode;
				
				$arrExample ['sourcePackageNamespace'] = $aFileInfo->sourcePackageNamespace ();
				$arrExample ['sourceClass'] = $aFileInfo->sourceClass ();
				
				$arrDoc [] = $arrExample;
			}
		}
		return $arrDoc;
	}
	
	public function cleanInDB(FileInfo $aFileInfo, DB $aDB) {
		$extension = $aFileInfo->extension() ;
		$version = $aFileInfo->version() ;
		$sourcePackageNamespace = $aFileInfo->sourcePackageNamespace() ;
		$sourceClass = $aFileInfo->sourceClass() ;
		$aDB->execute ( 'DELETE 
					doccenter_example,
					doccenter_example_class,
					doccenter_example_method ,
					doccenter_example_topic
				FROM 
					`doccenter_example`
						LEFT JOIN `doccenter_example_class` ON `doccenter_example`.`eid` = `doccenter_example_class`.`eid`
						LEFT JOIN `doccenter_example_method` ON `doccenter_example`.`eid` = `doccenter_example_method`.`eid`
						LEFT JOIN `doccenter_example_topic` ON `doccenter_example`.`eid` = `doccenter_example_topic`.`eid`
				WHERE (
					`doccenter_example`.`extension` = "' . $extension . '"
					AND `doccenter_example`.`version` = "' . $version . '"
					AND `doccenter_example`.`sourcePackageNamespace` = "' . addslashes ( $sourcePackageNamespace ) . '"
					AND `doccenter_example`.`sourceClass` = "' . addslashes ( $sourceClass ) . '"
				)' );
		return TRUE;
	}
	
	public function saveInDB(array $arrGenerate, DB $aDB) {
		$arrKeyExample = array ('extension', 'version', 'title', 'name', 'index', 'code', 'sourcePackageNamespace', 'sourceClass', 'sourceLine' , 'sourceEndLine' );
		foreach ( $arrGenerate as $generate ) {
			// example
			$aExampleInsert = new Insert ( 'doccenter_example' );
			foreach ( $arrKeyExample as $sKey ) {
				$aExampleInsert->setData ( $sKey , $generate [$sKey] );
			}
			$aDB->execute ( $aExampleInsert );
			$eid = $aDB->lastInsertId ();
			
			// class
			if (! empty ( $generate ['forclass'] )) {
				$aExampleClassInsert = new Insert ( 'doccenter_example_class' );
				$aExampleClassInsert->setData ( 'eid', $eid );
				$aExampleClassInsert->setData ( 'class', $generate ['forclass'] );
				$aDB->execute ( $aExampleClassInsert );
			}
			
			// method
			if (! empty ( $generate ['formethod'] )) {
				$aExampleMethodInsert = new Insert ( 'doccenter_example_method' );
				$aExampleMethodInsert->setData ( 'eid', $eid );
				$aExampleMethodInsert->setData ( 'method', $generate ['formethod'] );
				$aDB->execute ( $aExampleMethodInsert );
			}
			
			// topic
			if (! empty ( $generate ['forwiki'] )) {
				foreach ( $generate ['forwiki'] as $sForWiki ) {
					$aExampleTopicInsert = new Insert ( 'doccenter_example_topic' );
					$aExampleTopicInsert->setData ( 'eid', $eid );
					$aExampleTopicInsert->setData ( 'topic_title', $sForWiki );
					$aDB->execute ( $aExampleTopicInsert );
				}
			}
		}
		return TRUE;
	}
}
