<?php
namespace org\opencomb\doccenter\frame ;

use org\opencomb\doccenter\frame\DocFrontFrame;
use org\opencomb\coresystem\mvc\controller\Controller;

class DocFrontController extends Controller
{
    public function createFrame()
    {
    	return new DocFrontFrame($this->params()) ;
    }
}
?>