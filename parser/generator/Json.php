<?php 
namespace generator; 

class Json{ 
    public $exportArr = array() ;  
    public $unik ; 

    public function init(){ 
        $this->exportArr[] =  '{'; 
        $this->unik = 0 ; 
    }

    public function end(){ 
        $this->exportArr[] =  '}'; 
    }

    public function incr(){ 
        $this->unik++; 
    }

}
?> 