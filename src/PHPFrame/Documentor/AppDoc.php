<?php
/**
 * PHPFrame/Documentor/AppDoc.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Documentor
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * Application Documentor Class
 * 
 * @category PHPFrame
 * @package  Documentor
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_AppDoc implements IteratorAggregate
{
    private $_controllers = array();
    
    public function __construct($install_dir)
    {
        $controllers_dir = $install_dir.DS."src".DS."controllers";
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
