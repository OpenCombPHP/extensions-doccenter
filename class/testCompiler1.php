<?php
namespace org\opencomb\doccenter ;

/**
  * aoskdjfa
	sdfasd
	fas
	df
	asd
	fa
	sdf
	as
	df
*/
// asdfj;askdf
// o9234rlkagv
abstract class testCompiler1{
	// text
	public function text(){
		return true;
	}
	
	/*
	 * text2 comment
	 * @param 	   $haaa  	  interger    	   nothing to be done 
	 * @param      $b         string           nothing to bbbb
	 * @param      $c         array            an array param
	 * @return       boolean
	*/
	public function text2($haaa = 1, $b = 'xxxasd , lkfjasldkfja' , $c = array( ' asdf' , 'aa' => 'bbb' )){
		return true;
	}
	
	/**
		wucharbulin
	*/
	public function text3(DocumentGenerator $a){
		return true;
	}
	
	/* drilar fishow */
	public function text4(){
		return true;
	}
	
	// napilidies
	abstract public function text5();
	
	// tibalu
	/**
		tibalu
	*/
	static public function text6();
	
	private function &testReturnByRef(){
		return $this;
	}
}
