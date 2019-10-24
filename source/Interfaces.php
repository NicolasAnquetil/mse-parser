<?php 
interface TopInterface{
    public function topFunct(); 
}

interface BaseInterface{
    public function baseFunct(); 
}

interface DemoInterface  extends BaseInterface, TopInterface{  // only this part of line will be ignored  
    public function fnct(); 
}

interface ProjectInterface {  
    public function incrementVarTwo($attr); 
}
?> 