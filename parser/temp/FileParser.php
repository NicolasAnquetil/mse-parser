<?php
namespace parser; 

//include_once 'FolderParser.php'; 

class FileParser extends FolderParser {

    public $file; 
    public $file_already_included; 
    public $fileContents; 
    public $fileStructure; 

    public function __construct(){
        parent::__construct();
        $this->fileContents = $this->fileStructure = array();
        $this->file_already_included = false;  
    }

    private function reset(){ 
        $this->file_already_included = false ;
        if(isset($this->fileContents)):unset($this->fileContents);endif;  
        $this->fileContents = array() ; 
    }

    public function sliceIncludedFiles($item)
    {
        $temp = explode('/',$item); 
        $item = $temp[(count($temp)-1)];
        return $item ; 
    }

    private function includedFiles(){ 
        $included_files = \get_included_files(); 
        return array_map(array('parser\FileParser', 'sliceIncludedFiles'),$included_files) ; 
    }

    public function getFileContents($file){ 
        $this->file = $file ; 
        $this->reset();   
        $contents = file($file); 
        $included_files = $this->includedFiles() ; 
        foreach($contents as $line)
        {
            unset($line_class); $line_class = array() ; 
            $lineCheck = $this->prelimParse($line); 
            if($lineCheck['pass']){
                $lineArr = explode($lineCheck['delimiter'], $line ); 
                foreach($lineArr as $p_indx => $p_val ){
                    if (!(strpos($p_val, ".php") === false)){ $line_class[] = $p_val ; }   
                }
                $dupl = array_intersect(array_map('strtolower', $included_files), array_map('strtolower', $line_class)) ; 
                if($dupl != '' && count($dupl) >0 ): 
                    $line = '//'.$line ;  
                    $this->file_already_included=true; 
                endif;
            } 
            $this->fileContents[] = $line;
        }
    }

    private function prelimParse($line){
        $includeTags = array('include', 'include_once', 'require', 'require_once'); 
        $return['pass']  = false ;
        foreach ($includeTags as $tag){
            if(strpos($line, $tag) !== FALSE) { 
                if (!(strpos($line, "'") === false)) {  
                    $delim = "'"; 
                }else{ 
                     $delim = "\""; 
                } 
                $return['pass']  = true ; 
                $return['delimiter'] = $delim; 
                return $return;
            }
        }
        return $return;
    }

    public function arrangeFileArray($output){
            $this->fileStructure[$this->file] = $output ; 
    }
    
}
?> 