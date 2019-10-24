<?php
namespace parser; 
include_once 'FileParser.php'; 

class MainParser extends FileParser{

    public $line ; 
    public $pauseParsing ; 
    public $skipLine ; 

    public function __construct(){
        parent::__construct();
        $this->pauseParsing= $this->skipLine  = false ; 
        $this->line = '' ; 
    }

    private function normalize(){ 
        $this->line = preg_replace('/\s+/',' ', $this->line); 
        return $this;
    }
    
    private function evalRule($rule, $lineArr){ 
        if(is_array($rule)){  
             $intersect = array_intersect($rule, $lineArr); 
             $rs = !empty($intersect);
             $return['element']= $intersect; 
        } elseif(!(strpos($lineArr,$rule) === false)){  
            $rs = true; 
            $return['element']= $rule;
        }else{
            $rs = false; 
        }
        $return['found'] = $rs; 
        return $return; 
    }

    private function switchParsing($status){
        switch($status){
            case true: 
                $this->pauseParsing = true; 
            break; 
            case false:
                 $this->pauseParsing = false; 
                 $this->skipLine = true;    
            break; 
        }
    }

    private function parseBlock(){ 
        $block_skip_rule = ($this->pauseParsing)? $this->rules['skip']['multipleLines']['commentEnd']: $this->rules['skip']['multipleLines']['commentStart']; 
        $found = $this->evalRule($block_skip_rule, $this->line); 
        if($found['found']){ 
            if($this->pauseParsing == false): 
                $this->switchParsing(true) ;
            else:   
                $this->switchParsing(false) ; 
            endif; 
        }
        if($this->pauseParsing || $this->skipLine ){ 
            $this->skipLine = true ;    
        }
    }

    private function parseLine(){ 
        $line_arr = explode(' ', $this->line); 
        $line_skip_rules = $this->rules['skip']['singleLine']; 
        $found = $this->evalRule($line_skip_rules, $line_arr); 
        if($found['found']){   
            $line_offset = explode(array_values($found['element'])[0],$this->line );    
            if( is_array($line_offset) && $line_offset[0] != '' ){  
                $this->line = $line_offset[0] ; 
            }else {
                $this->skipLine = true ;   
            }
        }
    }

    private function parse(){ 
        $this->parseBlock(); 
        if(!$this->skipLine){  $this->parseLine() ; }  
        return $this;
    }

    private function approveExistance($keyword){ 
        switch($keyword){
            case 'class': 
                $delimiter = '{'; 
            break ; 
            case 'namespace':
                $delimiter = ';'; 
            break; 
        }
        return !(strpos(explode($keyword,$this->line)[1], $delimiter) === false)?true:false;
    } 

    private function needleVal($keyword){
        $line_arr = explode(' ', $this->line); 
        $indx     = array_search($keyword, $line_arr) ; 
        $keyval   = $line_arr[($indx + 1)]; 
        $return = '' ; 
        if($this->approveExistance($keyword)){ 
            $return = ($keyword == 'class')?str_replace('{','',$keyval):str_replace(';','',$keyval); 
        }
        return  $return;  
    }

    private function includeFile(){ 
        $include_name= $this->file ; 
        if($this->file_already_included){
            $file_name = explode('/',$this->file); 
            $tmp_file = 'temp/'.$file_name[(count($file_name) - 1 )]; 
            $str = implode("", $this->fileContents);
            $absFile = $this->absPath($tmp_file) ; 
            $this->writeToFile($absFile, $str ); 
            $include_name = $tmp_file ; 
        } 
        include_once($include_name); 
    }

    public function strPosition($key){ 
       return strpos($this->line, $key); 
    }

    public function analyze(){
        if(isset($this->fileContents) && is_array($this->fileContents) && count($this->fileContents)>1 ) {
            $NAMESPACE = $CLASS = '' ; 
            $this->includeFile();  

            foreach($this->fileContents as $lineIndex => $lineContents){ 
                $this->line = trim($lineContents) ; 
                if(!$this->normalize()->parse()->skipLine){

                    if (!($this->strPosition('namespace ') === false)) {
                        $NAMESPACE = $this->needleVal('namespace'); 
                        if($NAMESPACE != '' ):$NAMESPACE= $NAMESPACE.'\\' ; endif; 
                    } 

                    if (!($this->strPosition('class ') === false)) {   
                        $CLASS = $this->needleVal('class'); 
                    }else { 
                        continue ; 
                    } 

                    if(isset($CLASS) && $CLASS != ''  ){
                        $CLASSNAME = (string) $NAMESPACE.$CLASS ; 
                        $output[$CLASSNAME] =  $this->generateReflectionParameters($CLASSNAME) ; 
                        echo "\nFile: ".$this->file ."parsed Successfully"; 
                    }
                } 
                $this->skipLine = false ; 
            }
            if(isset($output) && $output != null ){ 
              $this->arrangeFileArray($output);
            }  
        }
    }

    private function dumpInterfacesInheritance($ref_interfaces){ 
        if(is_array($ref_interfaces) && $ref_interfaces != '' ){ 
            foreach($ref_interfaces as $elem_interface){ // only one level up 
                $super_interfaces = $elem_interface->getInterfaces();
                if(is_array($super_interfaces) && count($super_interfaces)){
                    foreach($super_interfaces as $obj ){
                        $this->interfaces_inheritance[strval($elem_interface->name)][] = strval($obj->name ); 
                    } 
                }
            }
        }
    }

    private function wrapMethodsParameters($reflectionClass, $methods){ 
        $ref_methods = array() ; 
        foreach($methods as $indx => $value){
            $ref_methods[$value->name]['arguments'] = array();  
            $arguments = $reflectionClass->getMethod($value->name)->getParameters(); 
             if(count($arguments)> 0 ){ 
                    foreach($arguments as $arg_indx => $arg_val){
                        $ref_methods[$value->name]['arguments'][] = $arg_val->name ;  
                    }
             }
            $ref_methods[$value->name]['static_vars'] = array() ; 
            $static_args = $reflectionClass->getMethod($value->name)->getStaticVariables(); 
            if(count($static_args)> 0 ){ 
                foreach($static_args as $static_indx => $staic_var){
                    $ref_methods[$value->name]['static_vars'][] = $static_indx ;  
                }
            }
        }
        return $ref_methods ; 
    }
    
    public function generateReflectionParameters($CLASSNAME){
         
        $reflectionClass = new \ReflectionClass($CLASSNAME);
        $ref_parent = $reflectionClass->getParentClass();
        $ref_interfaces = $reflectionClass->getInterfaces();
        $ref_traits = $reflectionClass->getTraits();
        $ref_properties = $reflectionClass->getProperties(); 
        $methods = $reflectionClass->getMethods();
        
        $this->dumpInterfacesInheritance($ref_interfaces); 
        
        $output['namespace']  = $reflectionClass->getNamespaceName();
        $output['parent']     = ($ref_parent != '' )?$ref_parent->name:''; 
        $output['methods']    = $this->wrapMethodsParameters($reflectionClass, $methods); 
        $output['interfaces'] = $this->extractPatterns($ref_interfaces);
        $output['traits']     = $this->extractPatterns($ref_traits);
        $output['class_properties'] = $this->extractPatterns($ref_properties); 
        return $output ; 
    }

    public function extractPatterns( $prop){ 
        $arr = array() ; 
        if(count($prop )>0 ):
            foreach($prop as $p_ind => $p_val){
                $arr[] =  $p_val->name ; 
            }
        else: 
            $arr[] = '' ;  
        endif; 

        return $arr ; 
    }

    public function recap(){
        if(!(is_array($this->dirFiles) && count($this->dirFiles)>0)){
            echo "no files found!"; 
            return true ;
        } 
        foreach($this->dirFiles as $indx => $pathToFile){ 
            $this->getFileContents($pathToFile); 
            $this->analyze() ;
        }
        $this->generateMSE($this->fileStructure); 
    }
}
?> 