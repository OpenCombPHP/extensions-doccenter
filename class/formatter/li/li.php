<?php
namespace org\opencomb\doccenter\formatter\li ;

use org\opencomb\doccenter\formatter\AbstractMultiLineTransformer ;

class li extends AbstractMultiLineTransformer{
	public function pattern(){
		return '`^.*$`';
	}
	
	public function replacement(array $arrMatch){
		list($sAll ) = $arrMatch ;
		$arrLine = explode('<br />',$sAll);
		$nArr = count($arrLine);
		
		$sAll = '';
		$nDepth = 0;
		foreach($arrLine as $sLine){
			$sTrim = trim($sLine);
			$sNoStar = trim($sTrim,'*');
			$nStar = strlen($sTrim) - strlen($sNoStar);
			if($nStar === $nDepth){
				if( 0 === $nStar){
					$sAll .= $sLine.'<br />';
				}else{
					$sAll .= '<li>'.$sNoStar.'</li>';
				}
			}else if( $nStar > $nDepth){
				while($nStar > $nDepth){
					$sAll .= '<ul>';
					++$nDepth ;
				}
				$sAll .= '<li>'.$sNoStar.'</li>' ;
			}else if( $nStar < $nDepth){
				while($nStar < $nDepth){
					$sAll .= '</ul>';
					--$nDepth ;
				}
				if( 0 === $nStar){
					$sAll .= $sLine.'<br />';
				}else{
					$sAll .= '<li>'.$sNoStar.'</li>';
				}
			}else{
				var_dump($nStar , $nDepth);
				$sAll .= $sLine.'<br />';
			}
		}
		return $sAll;
	}
}
