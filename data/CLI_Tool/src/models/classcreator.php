<?php
class ClassCreator
{
    private $_tmpl_path;
    
    public function __construct($tmpl_path)
    {
        if (!is_dir($tmpl_path)) {
            $msg  = "Template path passed to ".get_class($this)." is not ";
            $msg .= "valid. Directory '".$tmpl_path."' doesn't exist or is ";
            $msg .= "not readable.";
            throw new InvalidArgumentException($msg);
        }
        
        $this->_tmpl_path = $tmpl_path;
    }
    
    public function create($tmpl, array $replace=null)
    {
        $tmpl = $this->_tmpl_path.DS.$tmpl.".php";
        if (!is_file($tmpl)) {
            $msg = "Template file not found.";
            throw new RuntimeException($msg);
        }
        
        if (!is_null($replace)) {
            $array_obj = new PHPFrame_Array($replace);
            if (!$array_obj->isAssoc()) {
                $msg  = "Argument 'replace' passed to ".get_class($this)."::";
                $msg .= __FUNCTION__."() must be an asociative array."; 
                throw new InvalidArgumentException($msg);
            }
        }
        
        $class = file_get_contents($tmpl);
        
        if (is_array($replace) && count($replace) > 0) {
            $patterns     = array();
            $replacements = array();
            
            foreach ($replace as $key=>$value) {
                $patterns[]     = "/".$key."/s";
                $replacements[] = $value;
            }
            
            $class = preg_replace($patterns, $replacements, $class);
        }
        
        return $class;
    }
}
