<?php
namespace org\opencomb\doccenter\generator;

use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\db\DB;
use org\jecat\framework\lang\compile\object\NamespaceDeclare;
use org\jecat\framework\db\sql\StatementFactory;

class ClassGenerator implements IGenerator {
	public function generate(TokenPool $aTokenPool, FileInfo $aFileInfo) {
		$arrGenerate = array ();
		
		foreach ( $aTokenPool->classIterator () as $aClassToken ) {
			$classInfo = array ();
			$classInfo ['name'] = $aClassToken->name ();
			$classInfo ['version'] = $aFileInfo->version ();
			$classInfo ['extension'] = $aFileInfo->extension ();
			$classInfo ['abstract'] = $aClassToken->isAbstract ();
			$classInfo ['namespace'] = $aClassToken->belongsNamespace ()->name ();
			$classInfo ['comment'] = (null === $aClassToken->docToken ()) ? '' : $aClassToken->docToken ()->docComment ()->description ();
			
			$arrFunction = array ();
			foreach ( $aTokenPool->functionIterator ( $aClassToken->fullName () ) as $aFunctionToken ) {
				// comment of function
				// pick up function return type
				// pick up param type and comment
				$arrInfoFromFunctionComment = array ('return' => '', 'paramlist' => array () );
				if (null !== $aFunctionToken->docToken ()) {
					$aDocComment = $aFunctionToken->docToken ()->docComment ();
					foreach ( $aDocComment->itemIterator ( 'return' ) as $strReturn ) {
						$arrInfoFromFunctionComment ['return'] = $strReturn;
					}
					foreach ( $aDocComment->itemIterator ( 'param' ) as $strParam ) {
						$arrParam = preg_split ( '|\\s+|', $strParam, 3 );
						$arrParamInfo = array ();
						if (count ( $arrParam ) === 1) {
							$arrParamInfo ['name'] = $arrParam [0];
						} else if (count ( $arrParam ) === 2) {
							$arrParamInfo ['name'] = $arrParam [0];
							$arrParamInfo ['comment'] = $arrParam [1];
						} else if (count ( $arrParam ) === 3) {
							$arrParamInfo ['name'] = $arrParam [0];
							$arrParamInfo ['type'] = $arrParam [1];
							$arrParamInfo ['comment'] = $arrParam [2];
						}
						$arrInfoFromFunctionComment ['paramlist'] [$arrParamInfo ['name']] = $arrParamInfo;
					}
				}
				// function info
				$functionInfo = array ();
				$functionInfo ['name'] = $aFunctionToken->name ();
				$functionInfo ['version'] = $classInfo ['version'];
				$functionInfo ['class'] = $classInfo ['name'];
				$functionInfo ['namespace'] = $classInfo ['namespace'];
				$functionInfo ['extension'] = $classInfo ['extension'];
				$functionInfo ['access'] = ( string ) $aFunctionToken->accessToken ();
				$functionInfo ['abstract'] = ( int ) (null !== $aFunctionToken->abstractToken ());
				$functionInfo ['static'] = ( int ) (null !== $aFunctionToken->staticToken ());
				$functionInfo ['returnType'] = $this->getTypeName ( $arrInfoFromFunctionComment ['return'], $aTokenPool, $aFunctionToken->belongsNamespace (), self::IN_COMMENT );
				$functionInfo ['returnByRef'] = ( int ) $aFunctionToken->isReturnByRef ();
				$functionInfo ['comment'] = (null === $aFunctionToken->docToken ()) ? '' : $aFunctionToken->docToken ()->docComment ()->description ();
				
				// parameter list
				$parameterlist = array ();
				foreach ( $aFunctionToken->parameterIterator () as $parameterToken ) {
					if ($parameterToken->belongsFunction () !== $aFunctionToken) {
						throw new Exception ( 'parameter belongs function not match' );
					}
					$parameterInfo = array ();
					$parameterInfo ['version'] = $classInfo ['version'];
					$parameterInfo ['namespace'] = $classInfo ['namespace'];
					$parameterInfo ['class'] = $classInfo ['name'];
					$parameterInfo ['method'] = $functionInfo ['name'];
					$parameterInfo ['extension'] = $functionInfo ['extension'];
					$parameterInfo ['default'] = ('' === $parameterToken->defaultValue ()) ? '' : $parameterToken->defaultValue ();
					
					// param type
					$sType = '';
					$iFrom = 0;
					if ($parameterToken->type () !== '') {
						$sType = $parameterToken->type ();
						$iFrom = self::IN_CODE;
					} else if (isset ( $arrInfoFromFunctionComment ['paramlist'] [$parameterToken->name ()] ['type'] )) {
						$sType = $arrInfoFromFunctionComment ['paramlist'] [$parameterToken->name ()] ['type'];
						$iFrom = self::IN_COMMENT;
					} else {
						$sType = '';
					}
					$parameterInfo ['type'] = $this->getTypeName ( $sType, $aTokenPool, $parameterToken->belongsFunction ()->belongsNamespace (), $iFrom );
					
					$parameterInfo ['name'] = $parameterToken->name ();
					$parameterInfo ['byRef'] = ( int ) $parameterToken->isReference ();
					// comment
					if (isset ( $arrInfoFromFunctionComment ['paramlist'] [$parameterToken->name ()] ) and isset ( $arrInfoFromFunctionComment ['paramlist'] [$parameterToken->name ()] ['comment'] )) {
						$parameterInfo ['comment'] = $arrInfoFromFunctionComment ['paramlist'] [$parameterToken->name ()] ['comment'];
					} else {
						$parameterInfo ['comment'] = '';
					}
					
					$parameterlist [] = $parameterInfo;
				}
				$functionInfo ['parameterlist'] = $parameterlist;
				$arrFunction [] = $functionInfo;
			}
			$classInfo ['functionlist'] = $arrFunction;
			
			$arrGenerate [] = $classInfo;
		}
		return $arrGenerate;
	}
	
	public function cleanInDB(FileInfo $aFileInfo, DB $aDB) {
		try {
			$version = $aFileInfo->version() ;
			$namespace = $aFileInfo->ns() ;
			
			$extname = $aFileInfo->extension() ;
			
			foreach($aFileInfo->sourceClassNameList() as $name){
			
			// execute
			$aDB->execute ( 'DELETE doccenter_class,
			doccenter_method,
			doccenter_parameter FROM  `doccenter_class` LEFT JOIN (
				`doccenter_method`
					LEFT JOIN `doccenter_parameter` ON (
						`doccenter_method`.`version` =  `doccenter_parameter`.`version`
						AND  `doccenter_method`.`name` =  `doccenter_parameter`.`method`
						AND  `doccenter_method`.`class` =  `doccenter_parameter`.`class`
						AND  `doccenter_method`.`namespace` = `doccenter_parameter`.`namespace`
						AND  `doccenter_method`.`extension` = `doccenter_parameter`.`extension`
					)
				) ON (
					`doccenter_class`.`name` =  `doccenter_method`.`class`
					AND `doccenter_class`.`namespace` = `doccenter_method`.`namespace`
					AND `doccenter_class`.`version` = `doccenter_method`.`version`
					AND `doccenter_class`.`extension` = `doccenter_method`.`extension`
				)
			WHERE (
				`doccenter_class`.`name` = "' . $name . '"
				AND `doccenter_class`.`namespace` = "' . addslashes ( $namespace ) . '"
				AND `doccenter_class`.`version` = "' . $version . '"
				AND `doccenter_class`.`extension` = "' . $extname . '")' );
			}
		} catch ( ExecuteException $e ) {
			echo $e->message ();
			echo $aDB->executeLog ( false );
		} catch ( Exception $e ) {
			echo $e->message ();
		} catch ( \Exception $e ) {
			echo $e->message ();
		}
		return TRUE;
	}
	
	public function saveInDB(array $arrGenerate, DB $aDB) {
		try {
			foreach ( $arrGenerate as $classInfo ) {
				$aClassInsert = StatementFactory::singleton ()->createInsert ( 'doccenter_class' );
				$aClassInsert->setData ( 'namespace', $classInfo ['namespace'] );
				$aClassInsert->setData ( 'name', $classInfo ['name'] );
				$aClassInsert->setData ( 'version', $classInfo ['version'] );
				$aClassInsert->setData ( 'abstract', $classInfo ['abstract'] );
				$aClassInsert->setData ( 'comment', $classInfo ['comment'] );
				$aClassInsert->setData ( 'extension', $classInfo ['extension'] );
				DB::singleton ()->execute ( $aClassInsert );
				
				// insert function
				foreach ( $classInfo ['functionlist'] as $functionInfo ) {
					$aFunctionInsert = StatementFactory::singleton ()->createInsert ( 'doccenter_method' );
					foreach ( $functionInfo as $key => $value ) {
						if (! is_array ( $value )) {
							$aFunctionInsert->setData ( $key, $value );
						}
					}
					DB::singleton ()->execute ( $aFunctionInsert );
					
					// insert param
					foreach ( $functionInfo ['parameterlist'] as $paramInfo ) {
						$aParamInsert = StatementFactory::singleton ()->createInsert ( 'doccenter_parameter' );
						foreach ( $paramInfo as $key => $value ) {
							if (! is_array ( $value )) {
								$aParamInsert->setData ( '`' . $key . '`', $value );
							}
						}
						DB::singleton ()->execute ( $aParamInsert );
					}
				}
			}
		} catch ( ExecuteException $e ) {
			echo $e->message ();
			echo DB::singleton ()->executeLog ( false );
		} catch ( Exception $e ) {
			echo $e->message ();
		} catch ( \Exception $e ) {
			echo $e->message ();
		}
		return TRUE;
	}
	
	const IN_COMMENT = 0x371;
	const IN_CODE = 0x372;
	
	private function getTypeName($sTypeName, TokenPool $aTokenPool, NamespaceDeclare $aNamespace, $iFrom) {
		$sTypeRtn = $sTypeName;
		$sTypeNameFromTokenPool = $aTokenPool->findName ( $sTypeName, $aNamespace );
		switch ($iFrom) {
			case self::IN_COMMENT :
				if (in_array ( $sTypeName, array ('', 'integer', 'array', 'boolean', 'string', 'int', 'bool' ) )) {
					$sTypeRtn = $sTypeName;
				} else if (strpos ( $sTypeName, "\\" ) !== false) {
					$sTypeRtn = $sTypeName;
				} else {
					$sTypeRtn = $sTypeNameFromTokenPool;
				}
				break;
			case self::IN_CODE :
				if (in_array ( $sTypeName, array ('', 'array' ) )) {
					$sTypeRtn = $sTypeName;
				} else if (substr ( $sTypeName, 0, 1 ) === "\\") {
					$sTypeRtn = $sTypeName;
				} else {
					$sTypeRtn = $sTypeNameFromTokenPool;
				}
				break;
		}
		return $sTypeRtn;
	}
}
