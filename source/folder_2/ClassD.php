<?php
namespace source\foldertwo; 

class ClassD{
    private $vard_private = ''; 
    public $vard_public = ''; 
    protected $vard_protected = ''; 

    public function __construct(){
        $this->class_var = 'Class A identifier'; 
    }
    
    function defineD($temp = '', $url = '' ){ 
        static $var = 2 ; 
        echo $this->class_var ."</br>" ; 
        $obj = new ClassX() ; 
        $obj->classFunction(); 
    } 

}


?>
