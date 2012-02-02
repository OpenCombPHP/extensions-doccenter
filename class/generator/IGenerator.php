<?php
namespace org\opencomb\doccenter\generator;

use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\db\DB;

interface IGenerator {
	public function generate(TokenPool $aTokenPool, FileInfo $aFileInfo);
	public function cleanInDB(array $arrGenerate, DB $aDB);
	public function saveInDB(array $arrGenerate, DB $aDB);
}
