<?php
namespace org\opencomb\doccenter\formatter;

use org\jecat\framework\lang\Object;

class Formatter extends Object {
	public function format($sOriginalText) {
		$sPregText = preg_replace ( $this->arrPattern, $this->arrReplacement, $sOriginalText );
		foreach ( $this->arrTransformer as $aTransformer ) {
			switch ($aTransformer->lineType ()) {
				case ITransformer::SINGLE_LINE :
					$arrLine = explode ( '<br />', $sPregText );
					$sPregText = '';
					$sPattern = $aTransformer->pattern ();
					foreach ( $arrLine as $sLine ) {
						preg_match_all ( $sPattern, $sLine, $arrMatch, PREG_OFFSET_CAPTURE );
						$nRow = count ( $arrMatch );
						if ($nRow <= 0)
							continue;
						$nColumn = count ( $arrMatch [0] );
						for($i = $nColumn - 1; $i >= 0; -- $i) {
							$arrMatchRep = array ();
							for($j = 0; $j < $nRow; ++ $j) {
								$arrMatchRep [] = $arrMatch [$j] [$i] [0];
							}
							$nStart = $arrMatch [0] [$i] [1];
							$nLength = strlen ( $arrMatch [0] [$i] [0] );
							$sReplacement = $aTransformer->replacement ( $arrMatchRep );
							$sLine = substr_replace ( $sLine, $sReplacement, $nStart, $nLength );
						}
						if ($nColumn <= 0 and strlen ( $sLine ) > 0) {
							$sPregText .= $sLine . '<br />';
						} else {
							$sPregText .= $sLine;
						}
					}
					break;
				case ITransformer::MULTI_LINE :
					$sPattern = $aTransformer->pattern ();
					preg_match_all ( $sPattern, $sPregText, $arrMatch, PREG_OFFSET_CAPTURE );
					$nRow = count ( $arrMatch );
					if ($nRow <= 0)
						continue;
					$nColumn = count ( $arrMatch [0] );
					for($i = $nColumn - 1; $i >= 0; -- $i) {
						$arrMatchRep = array ();
						for($j = 0; $j < $nRow; ++ $j) {
							$arrMatchRep [] = $arrMatch [$j] [$i] [0];
						}
						$nStart = $arrMatch [0] [$i] [1];
						$nLength = strlen ( $arrMatch [0] [$i] [0] );
						$sReplacement = $aTransformer->replacement ( $arrMatchRep );
						$sPregText = substr_replace ( $sPregText, $sReplacement, $nStart, $nLength );
					}
					break;
			}
		}
		return $sPregText;
	}
	
	public function addTransformer(ITransformer $aTransformer) {
		$this->arrTransformer [] = $aTransformer;
	}
	
	public function setParam(array $arrPattern, array $arrReplacement) {
		$this->arrPattern = $arrPattern;
		$this->arrReplacement = $arrReplacement;
	}
	
	/**
	 * @wiki /文档中心/wiki语法
	 * {|
	 * ! 语法
	 * ! html
	 * ! 说明
	 * |-- --
	 * | <
	 * | &lt;
	 * |-- --
	 * | >
	 * | &gt;
	 * |-- --
	 * | ---
	 * | <hr />
	 * | 独占一行，前后可以包含空格。
	 * |-- --
	 * | =xxx=
	 * | <h3><a name="xxx">xxx</a></h3>
	 * | 独占一行，前后可以包含空格。
	 * |-- --
	 * | ==xxx==
	 * | <h2><a name="xxx">xxx</a></h2>
	 * | 独占一行，前后可以包含空格。
	 * |-- --
	 * | ===xxx===
	 * | <h1><a name="xxx">xxx</a></h1>
	 * | 独占一行，前后可以包含空格。
	 * |-- --
	 * | &#91;a xxx1]xxx2[/a]
	 * | <a xxx1>xxx2</a>
	 * |-- --
	 * | &#91;img xxx1]xxx2[/img]
	 * | <img xxx1 />
	 * |-- --
	 * | \
	 * |
	 * | 行末尾的反斜线将两行接成一行
	 * |-- --
	 * | \r\n或\n或\r
	 * | <br />
	 * |}
	 */
	
	public function readParamFromSetting() {
		$arr = array (
			'pattern' =>
			array (
				0 => '`<`',
				1 => '`>`',
				2 => '`(^|\n)\\s*---\\s*($|\n)`',
				3 => '`(^|\n)\\s*=\s*([^=].*?)\s*=\\s*($|\n)`',
				4 => '`(^|\n)\\s*==\s*([^=].*?)\s*==\\s*($|\n)`',
				5 => '`(^|\n)\\s*===\s*([^=].*?)\s*===\\s*($|\n)`',
				6 => '`\\[a (.*)\\](.*)\\[/a\\]`',
				7 => '`\\[img (.*)\\](.*)\\[/img\\]`',
				8 => '`\\\\(\\r\\n|\\n|\\r)`',
				9 => '`(\\r\\n|\\n|\\r)`',
			),
			'replacement' =>
			array (
				0 => '&lt;',
				1 => '&gt;',
				2 => '\\1<hr />\\2',
				3 => '\\1<h3><a name="\\2">\\2</a></h3>\\3',
				4 => '\\1<h2><a name="\\2">\\2</a></h2>\\3',
				5 => '\\1<h1><a name="\\2">\\2</a></h1>\\3',
				6 => '<a \\1>\\2</a>',
				7 => '<img \\1 />',
				8 => '',
				9 => '<br />',
			),
			'transformer' => 
			array (
				new example\FileExampleTransformer,
				new example\InCodeExampleTransformer,
				new example\ByTitleExampleTransformer,

				new table\TableBeginTransformer,
				new table\TableEndTransformer,
				new table\TableRowTransformer,
				new table\TableHeadTransformer,
				new table\TableCellTransformer,

				new font\SingleTagsTransformer,
				new font\AttrTagsTransformer,

				new warning\WarningBlock,
				new warning\WarningIcon,

				new li\li,

				new other\see,
				new other\rmbr,
			),
		) ;
		
		$arrPattern = $arr ['pattern'];
		$arrReplacement = $arr ['replacement'];
		$arrTransformer = $arr ['transformer'];
		
		$this->setParam ( $arrPattern, $arrReplacement );
		
		foreach ( $arrTransformer as $aTransformer ) {
			$this->addTransformer ( $aTransformer );
		}
	}
	
	static public function singleton($bCreateNew = true, $createArgvs = null, $sClass = null) {
		if (self::$aInstance === null && $bCreateNew) {
			self::$aInstance = parent::singleton ();
			self::$aInstance->readParamFromSetting ();
		}
		return self::$aInstance;
	}
	
	private $arrPattern = array ();
	private $arrReplacement = array ();
	
	private $arrTransformer = array ();
	
	private static $aInstance = null;
}
