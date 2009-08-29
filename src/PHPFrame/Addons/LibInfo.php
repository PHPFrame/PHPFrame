<?php
/**
 * PHPFrame/Addons/Lib.php
 * 
 * PHP version 5
 * 
 * @category PHPFrame
 * @package    PHPFrame
 * @subpackage Addons
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Addons Lib Class
 * 
 * @category PHPFrame
 * @package    PHPFrame
 * @subpackage Addons
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Addons_LibInfo extends PHPFrame_Addons_Info
{
    protected $name;
    protected $class_name;
    protected $path;
    protected $type;
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($str)
    {
        $this->name = (string) $str;
    }
    
    public function getClassName()
    {
        return $this->class_name;
    }
    
    public function setClassName($str)
    {
        $this->class_name = (string) $str;
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function setPath($str)
    {
        $this->path = $str;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setType($str)
    {
        $this->type = $str;
    }
}
