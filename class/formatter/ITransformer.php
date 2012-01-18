<?php
namespace org\opencomb\doccenter\formatter ;

interface ITransformer{
	public function pattern();
	
	public function replacement(array $arrMatch);
}
