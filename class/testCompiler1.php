<?php
namespace org\opencomb\doccenter ;


/**
 * @example /xxx/ooo:name[1]
 * @forclass xxx\ooo
 * @formethod xxx\ooo
 * @forwiki /xxx/ooo
 * 
 *  xxxxxxxxxxxxxx
 *  xxxxxxxxxxxxxx
 *  xxxxxxxxxxxxxx
 */
// asdfj;askdf
// o9234rlkagv
abstract class testCompiler1{
	/**
	 * @example /xxx/ooo:name[2]
	 * @forclass xxx\ooo
	 * @formethod xxx\ooo
	 * @forwiki /xxx/ooo
	 * 
	 *  xxxxxxxxxxxxxx
	 *  xxxxxxxxxxxxxx
	 *  xxxxxxxxxxxxxx
	 */

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
	
	
	
	/**
	 * @example
	 *
	 *
	 */
	
	// xxx
	private function &testReturnByRef(){
		return $this;
		
		
		/**
		 * @example
		 *
		 *
		 */
		
		$this = null ;
		
		if( xxx ){
		}
	}
}

/**
 * @example /xxx/ooo2:name[1]
 * @forclass xxx\ooo2
 * @formethod xxx\ooo2
 * @forwiki /xxx/ooo2
 * 
 *  xxxxxxxxxxxxxx
 *  xxxxxxxxxxxxxx
 *  xxxxxxxxxxxxxx
 */
class A{
}

/**
 * @example /xxx2/ooo:name[1]
 * @forclass xxx2\aaa
 * @formethod xxx2\aaa
 * @forwiki /xxx2/ooo
 *
 *  xxxxxxxxxxxxxx
 *  xxxxxxxxxxxxxx
 *  xxxxxxxxxxxxxx
 */
class B{
}
/**
 * @example /xxx2/ccc:name[1]
 * @forclass xxx2\aaa
 * @formethod xxx2\aaa
 * @forwiki /xxx2/ccc
 *
 *  xxxxxxxxxxxxxx
 *  xxxxxxxxxxxxxx
 *  xxxxxxxxxxxxxx
 */
class C{
}
/**
 * @example /xxx2/ccc/ddd:name[1]
 * @forclass xxx2\ccc
 * @formethod xxx2\ccc
 * @forwiki /xxx2/ccc/ddd
 *
 *  xxxxxxxxxxxxxx
 *  xxxxxxxxxxxxxx
 *  xxxxxxxxxxxxxx
 */
class D{
}