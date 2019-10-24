<?php 
namespace generator; 

class Mse{ 
    public $exportArr = array() ;  
    public $unik ; 

    public function init(){ 
        $this->exportArr[] =  '('; 
        $this->unik = 0 ; 
    }

    public function end(){ 
        $this->exportArr[] =  ')'; 
    }

    public function incr(){ 
        $this->unik++; 
    }

    public function tagNamespace($namespace, $id ){ 
        $tmp = " 
        (FAMIX.Namespace (id:".$id.")
            (name '".$namespace."') )
        "
        ; 

        $this->exportArr[] = $tmp  ; 
    }

    public function tagInterface($interface, $id, $super_id = array()){ 
       $tmp = " 
        (FAMIX.Package (id: ".$id.")
            (name '".$interface."')" ; 
        if(count($super_id) > 0 ){ 
            foreach($super_id as $val ){ 
                $tmp .= "
            (parentPackage (ref: ".$val."))" ; }
            }
         $tmp  .= ") 
         ";   
         $this->exportArr[] = $tmp  ;  
    }

    public function tagClassAttribute($attribute, $id){ 
        $this->exportArr[] = " 
        (FAMIX.Attribute 
            (name '".$attribute."')
            (parentType (ref: ".$id.")))
        "
        ; 
    }

    public function tagClassMethod($method, $signature, $id){ 
        $this->exportArr[] = " 
        (FAMIX.Method
            (name '".$method."')
            (signature '".$signature."')
            (parentType (ref: ".$id.")))
        "
        ; 
    }

    public function tagClass($className, $namespace,$interfaces,  $id){ 
        $str = " 
        (FAMIX.Class (id: ".$id.")
            (name '".$className."')" ; 
        if($namespace != '' ){      
           $str .=" 
            (container (ref: ".$namespace."))" ; 
        } 
        if($interfaces != '' && is_array($interfaces)): 
            foreach($interfaces as $indx => $val ){ 
                $str .="
            (parentPackage (ref: ".$val."))" ; 
            } 
        endif; 
       $str .=")
        "; 
        $this->exportArr[]  = $str ; 
        ; 
    }

    public function tagInheritance($sub, $parent){ 
        $this->exportArr[] = " 
        (FAMIX.Inheritance
            (subclass (ref: ".$sub."))
            (superclass (ref: ".$parent.")))
        "
        ; 
    }

}
?> 