<?php 
namespace essential\root; 
//include 'interfaces.php';
/* 
Software: test 
Developer: none 

Random text ... 
*/ 

// THis line comment would be ignored by parser 
// another line comment to get ignored 
 


trait MyTrait {
    public function sayHello() {
        echo 'Hello World!';
    }
}

/**
 * Class documentation
 *        this is class first 
 */

class FirstClass implements \DemoInterface, \ProjectInterface{ // adding comment here won't affect detecting class !

    use MyTrait ; 

    const MyConst = 0;
    protected $foo = '' ; 
    public $varOne = '' ; 
    public $varTwo = '' ; 
     
    
    public function __construct($attr='') {
        $this->varOne = $attr;
    } 

    public function incrementVarTwo($attr=0) {
        $this->varTwo += $attr ; 
        return $this->varTwo ; 
    }

    public function fnct(){
        // do nothing 
        echo 'First class saying hello '; 
    }

    public function baseFunct(){ 
        // do nothing 
    }
    
    public function topFunct(){ 
        // do nothing 
    }



} 

namespace essential\mySecondProject; // comment won't affect detecting namespace 

/**
 * Class documentation
 *   this is class Second 
 */

class SecondClass {

    public $secondClassVar = '' ;

    public function setSecondClassVar($attr='') {
        $this->secondClassVar = $attr ; 
    }
} 
 

?> 