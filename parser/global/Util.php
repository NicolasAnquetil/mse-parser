<?php 
trait Util{ 
    public function writeToFile($file, $str){ 
        $fh = fopen($file, 'w');
        fwrite($fh, $str);
        fclose($fh);
    }
}
?> 