<?php
namespace org\opencomb\doccenter\formatter ;

class MockTransformer implements ITransformer{
	public function pattern(){
		return '`wq`';
	}
	
	public function replacement(array $arrMatch){
		var_dump($arrMatch);
		return 'wq';
	}
}
