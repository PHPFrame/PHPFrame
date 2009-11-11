<?php
/**
 * PHPFrame/User/APIUser.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   User
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * API User Class
 * 
 * @category PHPFrame
 * @package  User
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_APIUser extends PHPFrame_PersistentObject
{
    public function __construct(array $options=null)
    {
        $this->addField(
           "user", 
           null, 
           false,  
           new PHPFrame_StringFilter(array("min_length"=>6, "max_length"=>50))
        );
        $this->addField(
           "key", 
           null, 
           false,  
           new PHPFrame_StringFilter(array("min_length"=>50, "max_length"=>50))
        );
        
        parent::__construct($options);
    }
    
    public function getUser()
    {
        return $this->fields["user"];
    }
    
    public function setUser($str)
    {
        $this->fields["user"] = $this->validate("user", $str);
    }
    
    public function getKey()
    {
        return $this->fields["key"];
    }
    
    public function setKey($str)
    {
        $this->fields["key"] = $this->validate("key", $str);
    }
}
