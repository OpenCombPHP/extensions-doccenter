<?php
namespace org\opencomb\doccenter\frame ;

use org\opencomb\coresystem\mvc\controller\Controller;

class DocFrontController extends Controller
{
    public function defaultFrameConfig()
    {
    	return array('class'=>'org\\opencomb\\doccenter\\frame\\DocFrontFrame') ;
    }
}
?>