<?php
namespace org\opencomb\doccenter\generator;

use org\opencomb\coresystem\mvc\controller\ControlPanel;
use org\jecat\framework\fs\FileSystem;
use org\jecat\framework\lang\compile\CompilerFactory;
use org\jecat\framework\db\DB;

// /?c=org.opencomb.doccenter.generator.DocumentGenerator&noframe=1&path[]=/framework/class/util/match/ResultSet.php&path[]=/framework/class/ui/xhtml/compiler/node/IfCompiler.php

class DocumentGenerator extends ControlPanel {
	public function process() {
		$arrPath = $this->params ['path'];
		
		if (empty ( $arrPath )) {
			$arrPath = array ();
		}
		
		$aDB = DB::singleton ();
		
		$arrGenerator = array (new ClassGenerator (), new WikiGenerator (), new ExampleGenerator () );
		echo count ( $arrPath ), "\n";
		foreach ( $arrPath as $sPath ) {
			echo "path:", $sPath, "\n";
			$aTokenPool = $this->getTokenPool ( $sPath );
			$aFileInfo = FileInfo::create ( $aTokenPool, $sPath );
			
			foreach ( $arrGenerator as $aGenerator ) {
				$arrGenerate = $aGenerator->generate ( $aTokenPool, $aFileInfo );
				if (TRUE == $aGenerator->cleanInDB ( $arrGenerate, $aDB )) {
					$aGenerator->saveInDB ( $arrGenerate, $aDB );
				}
			}
		}
		$this->mainView ()->disable ();
	}
	
	private function getTokenPool($path) {
		$aFile = FileSystem::singleton ()->findFile ( $path );
		if ($aFile === null)
			return null;
		$aInputStream = $aFile->openReader ();
		$aCompiler = CompilerFactory::singleton ()->create ();
		$aTokenPool = $aCompiler->scan ( $aInputStream );
		$aCompiler->interpret ( $aTokenPool );
		return $aTokenPool;
	}
}
