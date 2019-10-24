<?php
namespace source\folderone; 


class ClassA{
    private $vara_private = ''; 
    public $vara_public = ''; 
    protected $vara_protected = ''; 

    public function __construct(){
        $this->class_var = 'Class A identifier'; 
    }
    
    function defineA($temp = '', $url = '' ){ 
        static $var = 2 ; 
        echo $this->class_var ."</br>" ; 
    } 

}


?>
