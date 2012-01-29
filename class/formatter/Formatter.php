<?php
namespace org\opencomb\doccenter\formatter ;

use org\jecat\framework\lang\Object;

class Formatter extends Object{
	public function format($sOriginalText){
		$sPregText = preg_replace($this->arrPattern , $this->arrReplacement , $sOriginalText );
		foreach($this->arrTransformer as $aTransformer){
			$sPattern = $aTransformer->pattern() ;
			preg_match_all($sPattern,$sPregText , $arrMatch , PREG_OFFSET_CAPTURE );
			$nRow = count($arrMatch) ;
			if( $nRow <= 0) continue;
			$nColumn = count($arrMatch[0]);
			for($i=$nColumn-1;$i>=0;--$i){
				$arrMatchRep = array();
				for($j=0;$j<$nRow;++$j){
					$arrMatchRep [] = $arrMatch[$j][$i][0];
				}
				$nStart = $arrMatch[0][$i][1] ;
				$nLength = strlen($arrMatch[0][$i][0]) ;
				$sReplacement = $aTransformer->replacement($arrMatchRep) ;
				$sPregText = substr_replace ( $sPregText , $sReplacement , $nStart , $nLength );
			}
		}
		return $sPregText ;
	}
	
	public function addTransformer(ITransformer $aTransformer){
		$this->arrTransformer [] = $aTransformer ;
	}
	
	public function setParam(array $arrPattern , array $arrReplacement){
		$this->arrPattern = $arrPattern ;
		$this->arrReplacement = $arrReplacement ;
	}
	
	public function readParamFromSetting(){
		$arr = array (
			'pattern' =>
			array (
				0 => '`<`',
				1 => '`>`',
				2 => '`(^|\n)\\s*---\\s*($|\n)`',
				3 => '`(^|\n)\\s*=([^=].*)=\\s*($|\n)`',
				4 => '`(^|\n)\\s*==([^=].*)==\\s*($|\n)`',
				5 => '`(^|\n)\\s*===([^=].*)===\\s*($|\n)`',
				6 => '`\\[a(.*)\\](.*)\\[/a\\]`',
				7 => '`\\[img(.*)\\](.*)\\[/img\\]`',
				8 => '`^(.*)\n\\s*\n`',
				9 => '`\\\\(\\r\\n|\\n|\\r)`',
				10 => '`(\\r\\n|\\n|\\r)`',
			),
			'replacement' =>
			array (
				0 => '&lt;',
				1 => '&gt;',
				2 => '\\1<hr />\\2',
				3 => '\\1<h3>\\2</h3>\\3',
				4 => '\\1<h2>\\2</h2>\\3',
				5 => '\\1<h1>\\2</h1>\\3',
				6 => '<a\\1>\\2</a>',
				7 => '<img\\1>',
				8 => '<p>\\1</p>',
				9 => '',
				10 => '<br />',
			),
			'transformer' => 
			array (
				new FileExampleTransformer,
				new InCodeExampleTransformer,
				new ByTitleExampleTransformer,
				new table\TableBeginTransformer,
				new table\TableEndTransformer,
				new table\TableRowTransformer,
				new table\TableHeadTransformer,
				new table\TableCellTransformer,
				
				new font\SingleTagsTransformer,
				new font\AttrTagsTransformer,
				
				new warning\WarningBlock,
			),
		) ;
		
		$arrPattern = $arr['pattern'] ;
		$arrReplacement = $arr['replacement'] ;
		$arrTransformer = $arr['transformer'] ;
		
		$this->setParam($arrPattern , $arrReplacement);
		
		foreach($arrTransformer as $aTransformer){
			$this->addTransformer($aTransformer);
		}
	}
	
	static public function singleton($bCreateNew=true,$createArgvs=null,$sClass=null){
		if( self::$aInstance === null && $bCreateNew){
			self::$aInstance = parent::singleton();
			self::$aInstance->readParamFromSetting();
		}
		return self::$aInstance ;
	}
	
	private $arrPattern = array();
	private $arrReplacement = array();
	
	private $arrTransformer = array();
	
	static private $aInstance = null ;
}
