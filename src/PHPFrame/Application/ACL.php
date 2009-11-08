<?php
/**
 * PHPFrame/Application/ACL.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Application
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Access Level List Class
 * 
 * @category PHPFrame
 * @package  Application
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @internal
 */
class PHPFrame_ACL extends PHPFrame_PersistentObject
{
    public function __construct(array $options=null)
    {
        $this->addField(
           "groupid", 
           null, 
           false,  
           new PHPFrame_IntFilter()
        );
        $this->addField(
           "controller", 
           null, 
           false,  
           new PHPFrame_RegexpFilter(array(
               "regexp"=>"/^[a-zA-Z]{1,50}$/", 
               "min_length"=>1, 
               "max_length"=>50
           ))
        );
        $this->addField(
           "action", 
           "*", 
           false,  
           new PHPFrame_RegexpFilter(array(
               "regexp"=>"/^[a-zA-Z\*]{1,50}$/",
               "min_length"=>1, 
               "max_length"=>50
           ))
        );
        $this->addField(
           "value", 
           "own", 
           false,  
           new PHPFrame_RegexpFilter(array(
               "regexp"=>"/^(own|all)$/",
               "min_length"=>3, 
               "max_length"=>3
           ))
        );
        
        parent::__construct($options);
    }
    
    public function getGroupId()
    {
        return $this->fields["groupid"];
    }
    
    public function setGroupId($int)
    {
        $this->fields["groupid"] = $this->validate("groupid", $int);
    }
    
    public function getController()
    {
        return $this->fields["controller"];
    }
    
    public function setController($str)
    {
        $this->fields["controller"] = $this->validate("controller", $str);
    }
    
    public function getAction()
    {
        return $this->fields["action"];
    }
    
    public function setAction($str)
    {
        $this->fields["action"] = $this->validate("action", $str);
    }
    
    public function getValue()
    {
        return $this->fields["value"];
    }
    
    public function setValue($str)
    {
        $this->fields["value"] = $this->validate("value", $str);
    }
}
