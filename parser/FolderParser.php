<?php
namespace parser; 

include_once 'generator/Mse.php'; 
include_once 'global/Util.php'; 

use generator\Mse; 

class FolderParser{

    use \Util; 

    public $rules;  
    public $dirFiles ; 
    public $exportSyntax ; 
    public $interfaces_inheritance; 
    public $global_namespaces;  
    public $global_interfaces;  
    public $global_inheritance;  
    public $global_classes;  

    public function __construct(){
        $this->rules= $this->dirFiles = $this->interfaces_inheritance= $this->global_inheritance= $this->global_classes=  $this->global_interfaces= $this->global_namespaces= array(); 
        $this->rules['skip']['singleLine']=array('documentStart'=>'<?php' , 'documentEnd'=>'?>','lineComment'=>'//');
        $this->rules['skip']['multipleLines']=array('commentStart'=>'/*', 'commentEnd'=>'*/');
        $this->rules['skip']['folder']=array('.git');
    }

    public function absPath($dir){ 
        $path = $_SERVER['SCRIPT_FILENAME']; 
        $pathArr = explode('/',$path);
        $path = explode($pathArr[(count($pathArr)-1)], $path)[0].$dir;  
        return $path; 
    }

    public function getDirFiles($dir)
    {
        if(!is_dir(strval($dir))) return false;
        $pathinfo = '';
        $root = scandir(strval($dir));
        foreach($root as $value)
        {
            if($value === '.' || $value === '..') {continue;}
            if(is_file("$dir/$value")) {
                $pathinfo = pathinfo($dir.'/'.$value);
                if($pathinfo['extension'] == 'php') {
                    $result[]="$dir/$value";
                }
                continue;
            }
            foreach($this->getDirFiles("$dir/$value") as $value)
            {
                $result[]=$value;
            }
        }
        return $result;
    }

    public function initDirFiles($dir){
        $dir = $this->absPath($dir); 
        $this->dirFiles = $this->getDirFiles($dir); 
    }

    private function dumpInterfacesInheritacnceTags($gen){ 
        foreach($this->interfaces_inheritance as $id_ii => $vl_ii){
            if($id_ii != null){
                if(is_array($vl_ii) && $vl_ii != '' ){ 
                    $parent_packages = array() ; 
                    foreach($vl_ii as $indx => $super_i){ 
                        if(!isset($this->global_namespaces[$super_i])){ 
                            $gen->tagInterface($super_i, $gen->unik);   
                            $super_id= $this->global_interfaces[$super_i] = $gen->unik ;
                            $gen->incr() ; 
                           }else { 
                               $super_id =  $this->global_namespaces[$super_i] ;
                           }
                           $parent_packages[] = $super_id ;  
                       }
                       $gen->tagInterface($id_ii, $gen->unik, $parent_packages);
                       $this->global_interfaces[$id_ii] = $gen->unik ; 
                       $gen->incr() ;
                   } 
               }
           }
    }

    private function dumpNamespaceClassTag($gen, $attrValue){
        if(!isset($this->global_namespaces[$attrValue])){  
            $gen->tagNamespace($attrValue, $gen->unik);
            $this->global_namespaces[$attrValue] = $gen->unik ; 
            $return = $gen->unik ; 
            $gen->incr() ; 
       }else { 
        $return =  $this->global_namespaces[$attrValue] ;
       }
       return $return ;
    }

    private function dumpInterfaceClassTag($gen, $attrValue){
        $return = array();
        foreach($attrValue as $interface_indx => $interface_val){    
            if($interface_val != '' ){ 
                if(!isset($this->global_interfaces[$interface_val])){  
                    $gen->tagInterface($interface_val, $gen->unik) ; 
                    $this->global_interfaces[$interface_val] = $gen->unik ; 
                   $return[] = $gen->unik ;  
                    $gen->incr(); 
                } else { 
                    $return[] = $this->global_interfaces[$interface_val] ;  
                }
            } 
        }
        return $return ; 
    }

    public function generateMSE($fileStructure){ 
        $gen = new Mse(); 
        $gen->init();
        $this->dumpInterfacesInheritacnceTags($gen) ; 
        foreach($fileStructure as $file => $class_arr){
            foreach($class_arr as $className => $classAttributesArr){
                if(!isset($this->global_classes[$className])){ 
                    $this->global_classes[$className] = $gen->unik ;  
                    $class_attrs[$className]['id'] = $gen->unik ; 
                    $gen->incr() ; 
                }
                foreach($classAttributesArr as $attrName => $attrValue){
                    switch($attrName){ 
                            case 'namespace':   
                                 $class_attrs[$className]['namespace'] = $this->dumpNamespaceClassTag($gen, $attrValue);
                            break ;
                            case 'interfaces':
                                $class_attrs[$className]['interface'] = $this->dumpInterfaceClassTag($gen, $attrValue); 
                            break ; 
                            case 'traits':  
                                    // traits functions merged into classes methods 
                            break ; 
                            case 'parent':  
                                $this->global_inheritance[$className] = $attrValue ; 
                            break ; 
                            case 'class_properties':
                                foreach($attrValue as $prop_indx => $prop_val){ 
                                     $gen->tagClassAttribute($prop_val, $this->global_classes[$className]); 
                                }
                            break ; 
                            case 'methods':
                                foreach($attrValue as $prop_indx => $prop_val){ 
                                    $method =  $gen->tagClassMethod($prop_indx, $prop_indx.'()',$this->global_classes[$className] ) ; 
                                } 
                            break; 
                    }
                }
                $class_interfaces = isset($class_attrs[$className]['interface'])?$class_attrs[$className]['interface']:'';
                $class_namespace = isset($class_attrs[$className]['namespace'])?$class_attrs[$className]['namespace']:'' ; 
                $gen->tagClass($className, $class_namespace,$class_interfaces,  $this->global_classes[$className] ) ; 
            }
        }
        foreach($this->global_inheritance as $sub => $parent ){ 
            if($parent != '' ){ 
                $gen->tagInheritance($this->global_classes[$sub], $this->global_classes[$parent] ); 
            }   
        }
        $gen->end(); 
        $this->exportSyntax = join('', $gen->exportArr) ; 
        //echo $this->exportSyntax ; 
        $this->writeToFile($this->absPath('../output/module.mse'), $this->exportSyntax  ); 
        echo "\n  ##### Output/module.mse was generated Successfully! #####"; 
    }

}

?> 