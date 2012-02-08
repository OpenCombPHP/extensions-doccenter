<?php
namespace org\opencomb\doccenter\formatter\other;

use org\opencomb\doccenter\formatter\AbstractMultiLineTransformer;

class rmbr extends AbstractMultiLineTransformer {
	public function pattern() {
		return '`(</[^<>]*?>|<[^<>]*?/>)<br />`';
	}
	
	public function replacement(array $arrMatch) {
		list ( $sAll, $sPrefix ) = $arrMatch;
		
		return $sPrefix ;
	}
}
