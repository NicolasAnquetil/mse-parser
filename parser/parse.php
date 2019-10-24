<?php
use parser\MainParser; 
include_once 'MainParser.php'; 
$WEB_DEBUG=false ; 
$PLAIN_DEBUG=false; 

if($WEB_DEBUG){ 
header("Content-Type: text/html");
} 
if($PLAIN_DEBUG){ 
header('Content-type: text/plain');
} 

$obj = new MainParser() ;  
$obj->initDirFiles('../source') ; 
$obj->recap() ; 

?> 