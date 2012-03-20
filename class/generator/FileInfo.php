<?php
namespace org\opencomb\doccenter\generator;

use org\jecat\framework\lang\compile\object\TokenPool;
use org\opencomb\platform\ext\ExtensionManager;
use org\jecat\framework\util\Version;
use org\opencomb\platform\Platform;
use org\jecat\framework\fs\Folder;
use org\jecat\framework\lang\oop\ClassLoader;
use org\jecat\framework\fs\FSO;

class FileInfo {
	static public function create(TokenPool $aTokenPool, $sPath) {
		// file
		$aFile = Folder::singleton ()->findFile ( $sPath );
		if ($aFile === null)
			return null;
			
			// fileInfo
		$aFileInfo = new FileInfo ();
		
		// path
		$aFileInfo->sPath = $sPath;
		
		// namespace
		foreach ( $aTokenPool->iterator () as $aToken ) {
			$aNamespace = $aToken->belongsNamespace ();
			if (! empty ( $aNamespace )) {
				$aFileInfo->sNamespace = $aNamespace->name ();
				break;
			}
		}
		
		// ExtensionManager
		$aExtensionManager = ExtensionManager::singleton ();
		
		// extension
		$sExtensionName = $aExtensionManager->extensionNameByNamespace ( $aFileInfo->ns () );
		if (empty ( $sExtensionName )) {
			$strFrameworkNs = 'org\\jecat\\framework';
			$nFNLength = strlen ( $strFrameworkNs );
			$strPlatformNs = 'org\\opencomb\\platform';
			$nPNLength = strlen ( $strPlatformNs );
			if (substr ( $aFileInfo->ns (), 0, $nFNLength ) === $strFrameworkNs) {
				$sExtensionName = 'framework';
			} else if (substr ( $aFileInfo->ns (), 0, $nPNLength ) === $strPlatformNs) {
				$sExtensionName = 'platform';
			} else {
				$sExtensionName = 'error';
			}
		}
		$aFileInfo->sExtension = $sExtensionName;
		
		// version
		$aVersion = null;
		switch ($sExtensionName) {
			case 'framework' :
				$aVersion = Version::FromString ( \org\jecat\framework\VERSION );
				break;
			case 'platform' :
				$aVersion = Platform::singleton ()->version ();
				break;
			default :
				$aVersion = $aExtensionManager->extensionMetainfo ( $sExtensionName )->version ();
				break;
		}
		$aFileInfo->nVersion = $aVersion->to32Integer ();
		
		// PackageNamespace
		$sPackageNamespace = '';
		$aClassLoader = ClassLoader::singleton ();
		foreach ( $aClassLoader->packageIterator () as $aPackage ) {
			if (self::isInFolder ( $aFile, $aPackage->folder () )) {
				$sPackageNamespace = $aPackage->ns ();
				break;
			}
		}
		$aFileInfo->sSourcePackageNamespace = $sPackageNamespace;
		
		// sourceClass
		$sSourceClass = '';
		foreach ( $aTokenPool->classIterator () as $aClassToken ) {
			$sSourceClass = $aClassToken->fullName ();
			break;
		}
		$aFileInfo->sSourceClass = $sSourceClass;
		
		return $aFileInfo;
	}
	
	public function path() {
		return $this->sPath;
	}
	
	public function version() {
		return $this->nVersion;
	}
	
	public function ns() {
		return $this->sNamespace;
	}
	
	public function extension() {
		return $this->sExtension;
	}
	
	public function sourcePackageNamespace() {
		return $this->sSourcePackageNamespace;
	}
	
	public function sourceClass() {
		return $this->sSourceClass;
	}
	
	static private function isInFolder(FSO $aFSO, Folder $aFolder) {
		while ( $aFSO !== null ) {
			if ($aFSO === $aFolder) {
				return true;
			}
			$aFSO = $aFSO->directory ();
		}
		return false;
	}
	
	private $sPath;
	private $sNamespace;
	private $sExtension;
	private $nVersion;
	private $sSourcePackageNamespace;
	private $sSourceClass;
	private $arrSourceClassNameList=array();
}
