<?php
/**
 * PHPFrame/User/APIUser.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   User
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * API User Class
 * 
 * @category   PHPFrame
 * @package    User
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://github.com/PHPFrame/PHPFrame
 * @since      1.0
 * @deprecated This class is irrelevant and it should be replaced by a userland 
 *             User object.
 */
class PHPFrame_APIUser extends PHPFrame_PersistentObject
{
    /**
     * Constructor.
     * 
     * @param array $options [Optional]
     * 
     * @return void
     * @since  1.0
     */
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
    
    /**
     * Get user.
     * 
     * @return string
     * @since  1.0
     */
    public function getUser()
    {
        return $this->fields["user"];
    }
    
    /**
     * Set user.
     * 
     * @param string $str The API username.
     * 
     * @return string
     * @since  1.0
     */
    public function setUser($str)
    {
        $this->fields["user"] = $this->validate("user", $str);
    }
    
    /**
     * Get API key.
     * 
     * @return string
     * @since  1.0
     */
    public function getKey()
    {
        return $this->fields["key"];
    }
    
    /**
     * Set API key.
     * 
     * @param string $str The API key.
     * 
     * @return void
     * @since  1.0
     */
    public function setKey($str)
    {
        $this->fields["key"] = $this->validate("key", $str);
    }
}
