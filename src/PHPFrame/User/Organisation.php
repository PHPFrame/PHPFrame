<?php
/**
 * PHPFrame/User/Organisation.php
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
 * Organisation Class
 * 
 * @category PHPFrame
 * @package  User
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Organisation extends PHPFrame_PersistentObject
{
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
    
    public function getName()
    {
        return $this->fields["name"];
    }
    
    public function setName($str)
    {
        $this->fields["name"] = $this->validate("name", $str);
    }
}
