<?php
class PHPFrame_AppDoc implements IteratorAggregate
{
    private $_controllers = array();
    
    public function __construct()
    {
        $controllers_dir = PHPFRAME_INSTALL_DIR.DS."src".DS."controllers";
        $dir_iterator    = new RecursiveDirectoryIterator($controllers_dir);
        $it_iterator     = new RecursiveIteratorIterator($dir_iterator);
        
        foreach ($it_iterator as $file) {
            if ((end(explode(".", $file))) == "php") {
                $controller_name = substr(
                    $file->getFileName(), 
                    0, 
                    strpos($file->getFileName(), ".")
                );
                
                $class_name = ucfirst($controller_name)."Controller";
                
                $reflection_obj = new ReflectionClass($class_name);
                $controller_doc = new PHPFrame_ControllerDoc($reflection_obj);
                
                $this->_controllers[$controller_name] = $controller_doc;
            }
        }
    }
    
    public function __toString()
    {
        $str = "Controllers:\n\n";
        
        foreach ($this->_controllers as $key=>$controller) {
            $str .= $key."\n";
            for ($i=0; $i<strlen($key); $i++) {
                $str .= "-";
            }
            
            $str .= "\n".$controller."\n\n";
        }
        
        return $str;
    }
    
    public function getIterator()
    {
        $array = array();
        
        return new ArrayIterator($array);
    }
}
