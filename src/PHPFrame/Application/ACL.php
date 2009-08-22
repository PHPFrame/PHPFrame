<?php
/**
 * PHPFrame/Application/ACL.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Access Level List Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Application_ACL extends PHPFrame_Mapper_DomainObject
{
    protected $groupid;
    protected $controller;
    protected $action;
    protected $value;
    
    public function getGroupId()
    {
        return $this->groupid;
    }
    
    public function setGroupId($int)
    {
        $this->groupid = (int) $int;
    }
    
    public function getController()
    {
        return $this->controller;
    }
    
    public function setController($str)
    {
        $this->controller = (string) $str;
    }
    
    public function getAction()
    {
        return $this->action;
    }
    
    public function setAction($str)
    {
        $this->action = $str;
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function setValue($str)
    {
        $this->value = $str;
    }
}
