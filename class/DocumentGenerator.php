<?php
namespace org\opencomb\doccenter ;

use org\opencomb\coresystem\mvc\controller\ControlPanel ;
use org\jecat\framework\lang\compile\CompilerFactory ;
use org\jecat\framework\lang\compile\Compiler ;
use org\jecat\framework\fs\FileSystem ;
use org\jecat\framework\lang\Exception ;
use org\opencomb\platform\ext\ExtensionManager ;
use org\jecat\framework\db\DB ;
use org\jecat\framework\db\sql\StatementFactory ;
use org\jecat\framework\db\ExecuteException ;

// /?c=org.opencomb.doccenter.DocumentGenerator&noframe=1&path[]=/framework/class/util/match/ResultSet.php&path[]=/framework/class/util/match/RegExp.php&path[]=/framework/class/util/match/Result.php
// /?c=org.opencomb.doccenter.DocumentGenerator&debug=1&noframe=1&path[]=/extensions/doccenter/0.1/class/testCompiler.php

class DocumentGenerator  extends ControlPanel
{
	public function process(){
		$arrPath = $this->params['path'];
		$arrResultList = array();
		
		foreach($arrPath as $path){
			$arrGenerate = $this->generate($path);
			$arrResult = $this->cleanInDatabase($arrGenerate);
			if($arrResult['success']){
				$arrResult = $this->saveToDatabase($arrGenerate);
			}
			$arrResultList[] = $arrResult ;
		}
		echo json_encode($arrResultList);
		$this->mainView()->disable(); 
	}
	
	public function generate($path){
		$arrGenerate = array();
		$arrGenerate['path'] = $path;
		$arrGenerate['classlist'] = array();
		
		$aTokenPool = $this->getTokenPool($path);
		if(null === $aTokenPool){
			return array(
				'path' => $path,
				'error' => true,
				'errorString' => 'can not open token pool',
			);
		}
		foreach($aTokenPool->classIterator() as $aClassToken){
			$classInfo = array();
			$classInfo['name'] = $aClassToken->name();
			$classInfo['version'] = $this->getVersionByClassName($aClassToken->fullName())->to32Integer();
			$classInfo['abstract'] = $aClassToken->isAbstract();
			$classInfo['namespace'] = $aClassToken->belongsNamespace()->name();
			$classInfo['comment'] = ( null === $aClassToken->docToken() )?'':$aClassToken->docToken()->docComment()->description();
			
			$arrFunction = array();
			foreach($aTokenPool->functionIterator($aClassToken->fullName()) as $aFunctionToken){
				// comment of function 
				// pick up function return type
				// pick up param type and comment
				$arrInfoFromFunctionComment = array(
					'return' => '',
					'paramlist' => array(),
				);
				if( null !== $aFunctionToken->docToken() ){
					$aDocComment = $aFunctionToken->docToken()->docComment();
					foreach($aDocComment->itemIterator('return') as $strReturn){
						$arrInfoFromFunctionComment['return'] = $strReturn ;
					}
					foreach($aDocComment->itemIterator('param') as $strParam){
						$arrParam = preg_split('|\\s+|',$strParam,3);
						$arrParamInfo = array ();
						if(count($arrParam) === 2){
							$arrParamInfo['name'] = $arrParam[0] ;
							$arrParamInfo['comment'] = $arrParam[1] ;
						}else if(count($arrParam) === 3){
							$arrParamInfo['name'] = $arrParam[0] ;
							$arrParamInfo['type'] = $arrParam[1] ;
							$arrParamInfo['comment'] = $arrParam[2] ;
						}
						$arrInfoFromFunctionComment['paramlist'][$arrParamInfo['name']] = $arrParamInfo ;
					}
				}
				// function info
				$functionInfo = array();
				$functionInfo['name'] = $aFunctionToken->name();
				$functionInfo['version'] = $classInfo['version'];
				$functionInfo['class'] = $classInfo['name'];
				$functionInfo['namespace'] = $classInfo['namespace'];
				$functionInfo['access'] = (string)$aFunctionToken->accessToken();
				$functionInfo['abstract'] = (int)(null !== $aFunctionToken->abstractToken());
				$functionInfo['static'] = (int)(null !== $aFunctionToken->staticToken());
				// @todo
				$functionInfo['returnType'] = $aTokenPool->findName($arrInfoFromFunctionComment['return'],$aFunctionToken->belongsNamespace());
				$functionInfo['returnByRef'] = (int)$aFunctionToken->isReturnByRef();
				$functionInfo['comment'] = ( null === $aFunctionToken->docToken() )?'':$aFunctionToken->docToken()->docComment()->description();
				
				// parameter list
				$parameterlist = array();
				foreach($aFunctionToken->parameterIterator() as $parameterToken){
					if($parameterToken->belongsFunction() !== $aFunctionToken){
						throw new Exception('parameter belongs function not match');
					}
					$parameterInfo = array();
					$parameterInfo['version'] = $classInfo['version'];
					$parameterInfo['namespace'] = $classInfo['namespace'];
					$parameterInfo['class'] = $classInfo['name'];
					$parameterInfo['method'] = $functionInfo['name'];
					$parameterInfo['default'] = ('' === $parameterToken->defaultValue())?'':$parameterToken->defaultValue();
					
					// param type
					if($parameterToken->type() !== ''){
						$parameterInfo['type'] = $parameterToken->type() ;
					}else if(isset($arrInfoFromFunctionComment['paramlist'][$parameterToken->name()])){
						$parameterInfo['type'] = $arrInfoFromFunctionComment['paramlist'][$parameterToken->name()]['type'] ;
					}else{
						$parameterInfo['type'] = '' ;
					}
					
					$parameterInfo['name'] = $parameterToken->name();
					$parameterInfo['byRef'] = (int)$parameterToken->isReference();
					// comment
					if(isset($arrInfoFromFunctionComment['paramlist'][$parameterToken->name()])){
						$parameterInfo['comment'] = $arrInfoFromFunctionComment['paramlist'][$parameterToken->name()]['comment'] ;
					}else{
						$parameterInfo['comment'] = '' ;
					}
					
					$parameterlist[] = $parameterInfo;
				}
				$functionInfo['parameterlist'] = $parameterlist;
				$arrFunction[] = $functionInfo;
			}
			$classInfo['functionlist'] = $arrFunction;
			
			$arrGenerate['classlist'][] = $classInfo;
		}
		return $arrGenerate;
	}
	
	private function getTokenPool($path){
		$aFile = FileSystem::singleton()->findFile($path);
		if($aFile === null) return null;
		$aInputStream = $aFile->openReader();
		$aCompiler = CompilerFactory::singleton()->create();
		$aTokenPool = $aCompiler->scan($aInputStream);
		$aCompiler->interpret($aTokenPool);
		return $aTokenPool;
	}
	
	private function getVersionByClassName($classname){
		if(null === $this->aExtensionManager){
			$this->aExtensionManager = ExtensionManager::singleton() ;
		}
		$extensionName = $this->aExtensionManager->extensionNameByClass($classname);
		$extensionMetainfo = $this->aExtensionManager->extensionMetainfo($extensionName);
		return $extensionMetainfo->version();
	}
	
	private function cleanInDatabase($arrGenerate){
		$arrResult = array(
			'success' => true,
			'errorString' => 'clean succeed',
		);
		$version = $arrGenerate['classlist'][0]['version'];
		$namespace = $arrGenerate['classlist'][0]['namespace'];
		$name = $arrGenerate['classlist'][0]['name'];
		
		// model
		try{
			$aDB = DB::singleton();
			
			// create table
			$aClassTable = StatementFactory::singleton()->createTable('doccenter_class');
			$aFunctionTable = StatementFactory::singleton()->createTable('doccenter_method');
			$aParamTable = StatementFactory::singleton()->createTable('doccenter_parameter');
			// create join
			$aClassFunctionJoin = StatementFactory::singleton()->createTablesJoin();
			$aFunctionParamJoin = StatementFactory::singleton()->createTablesJoin();
			// create restriction
			$aClassFunctionRestriction = StatementFactory::singleton()->createRestriction();
			$aFunctionParamRestriction = StatementFactory::singleton()->createRestriction();
			// set restriction
			$aClassFunctionRestriction->eqColumn('`doccenter_class`.`name`','`doccenter_method`.`class`');
			$aClassFunctionRestriction->eqColumn('`doccenter_class`.`namespace`','`doccenter_method`.`namespace`');
			$aClassFunctionRestriction->eqColumn('`doccenter_class`.`version`','`doccenter_method`.`version`');
			
			$aFunctionParamRestriction->eqColumn('`doccenter_method`.`version`','`doccenter_parameter`.`version`');
			$aFunctionParamRestriction->eqColumn('`doccenter_method`.`name`','`doccenter_parameter`.`method`');
			$aFunctionParamRestriction->eqColumn('`doccenter_method`.`class`','`doccenter_parameter`.`class`');
			$aFunctionParamRestriction->eqColumn('`doccenter_method`.`namespace`','`doccenter_parameter`.`namespace`');
			// join them !
			$aClassTable->addJoin($aClassFunctionJoin);
			$aClassFunctionJoin->addTable($aFunctionTable,$aClassFunctionRestriction);
			$aFunctionTable->addJoin($aFunctionParamJoin);
			$aFunctionParamJoin->addTable($aParamTable,$aFunctionParamRestriction);
			// create delete
			$aDelete = StatementFactory::singleton()->createDelete();
			$aDelete->addTable($aClassTable);
			// execute
			$aDB->execute('DELETE doccenter_class , doccenter_method , doccenter_parameter FROM  `doccenter_class` LEFT JOIN ( `doccenter_method` LEFT JOIN ( `doccenter_parameter` ) ON ((`doccenter_method`.`version` = `doccenter_parameter`.`version` AND `doccenter_method`.`name` = `doccenter_parameter`.`method` AND `doccenter_method`.`class` = `doccenter_parameter`.`class` AND `doccenter_method`.`namespace` = `doccenter_parameter`.`namespace`)) ) ON ((`doccenter_class`.`name` = `doccenter_method`.`class` AND `doccenter_class`.`namespace` = `doccenter_method`.`namespace` AND `doccenter_class`.`version` = `doccenter_method`.`version`)) ');
		}catch(ExecuteException $e){
			$arrResult['success'] = false;
			$arrResult['errorString'] = $e->message();
			$arrResult['executeLog'] = DB::singleton()->executeLog(false);
		}catch(Exception $e){
			$arrResult['success'] = false;
			$arrResult['errorString'] = $e->message();
		}catch(\Exception $e){
			$arrResult['success'] = false;
			$arrResult['errorString'] = $e->message();
		}
		
		// result
		return $arrResult;
	}
	
	private function saveToDatabase($arrGenerate){
		$arrResult = array(
			'success' => true,
			'errorString' => 'save succeed',
		);
		
		try{
			foreach($arrGenerate['classlist'] as $classInfo){
				$aClassInsert = StatementFactory::singleton()->createInsert('doccenter_class');
				$aClassInsert->setData('namespace',$classInfo['namespace']);
				$aClassInsert->setData('name',$classInfo['name']);
				$aClassInsert->setData('version',$classInfo['version']);
				$aClassInsert->setData('abstract',$classInfo['abstract']);
				$aClassInsert->setData('comment',$classInfo['comment']);
				DB::singleton()->execute($aClassInsert);
				
				// insert function
				foreach($classInfo['functionlist'] as $functionInfo){
					$aFunctionInsert = StatementFactory::singleton()->createInsert('doccenter_method');
					foreach($functionInfo as $key=>$value){
						if(!is_array($value)){
							$aFunctionInsert->setData($key,$value);
						}
					}
					DB::singleton()->execute($aFunctionInsert);
					
					// insert param
					foreach($functionInfo['parameterlist'] as $paramInfo){
						$aParamInsert = StatementFactory::singleton()->createInsert('doccenter_parameter');
						foreach($paramInfo as $key=>$value){
							if(!is_array($value)){
								$aParamInsert->setData('`'.$key.'`',$value);
							}
						}
						DB::singleton()->execute($aParamInsert);
					}
				}
			}
		}catch(ExecuteException $e){
			$arrResult['success'] = false;
			$arrResult['errorString'] = $e->message();
			$arrResult['executeLog'] = DB::singleton()->executeLog(false);
		}catch(Exception $e){
			$arrResult['success'] = false;
			$arrResult['errorString'] = $e->message();
		}catch(\Exception $e){
			$arrResult['success'] = false;
			$arrResult['errorString'] = $e->message();
		}
		
		// result
		return $arrResult;
	}
	
#	private function getPrototype(){
#		if(null === $this->aPrototype){
#			$this->aPrototype =
#			Prototype::create("doccenter_class",null,array('version','name','abstract','namespace','comment'))
#				->setName('class')
#				->setKeys(array('namespace','name','version'))
#	
#				->hasMany('doccenter_method',array('version','name','namespace'),array('version','class','namespace'))
#					->setName('function')
#					->setKeys(array('namespace','class','name','version'))
#					->addColumns('version','name','class','namespace','access','abstract','static','returnType','returnByRef','comment')

#					->hasMany('doccenter_parameter',array('version','name','class','namespace'),array('version','method','class','namespace'))
#						->setName('param')
#						->setKeys(array('version','namespace','class','method','name'))
#						->addColumns('version','namespace','class','method','name','type','default','byRef','comment')
#					->done()
#				->done()
#			->done() ;
#		}
#		return $this->aPrototype;
#	}
	
	private $aExtensionManager = null ;
	private $aPrototype = null ;
}
