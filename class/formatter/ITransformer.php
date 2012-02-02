<?php
namespace org\opencomb\doccenter\formatter;

interface ITransformer {
	public function pattern();
	
	public function replacement(array $arrMatch);
	
	const SINGLE_LINE = 0x10;
	const MULTI_LINE = 0x11;
	
	public function lineType();
}
