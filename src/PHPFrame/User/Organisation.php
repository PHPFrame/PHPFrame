<?php
/**
 * PHPFrame/User/Organisation.php
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
 * Organisation Class
 * 
 * @category PHPFrame
 * @package  User
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_Organisation extends PHPFrame_PersistentObject
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
            "name", 
            null, 
            false,  
            new PHPFrame_StringFilter(array("min_length"=>3, "max_length"=>50))
        );
        
        parent::__construct($options);
    }
    
    /**
     * Get the organisation name.
     * 
     * @return string
     * @since  1.0
     */
    public function getName()
    {
        return $this->fields["name"];
    }
    
    /**
     * Set the organisation name.
     * 
     * @param string $str The organisation name.
     * 
     * @return void
     * @since  1.0
     */
    public function setName($str)
    {
        $this->fields["name"] = $this->validate("name", $str);
    }
}
