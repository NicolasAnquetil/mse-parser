<?php
namespace source\folderone; 


include('ClassA.php'); 

class ClassSubA extends ClassA{

    public function __construct(){
        $this->class_var = 'Class A Sub identifier'; 
    }
    
    public function defineSubA($var = '' ){ 
        // do nothing 
        
    } 

}


?>
