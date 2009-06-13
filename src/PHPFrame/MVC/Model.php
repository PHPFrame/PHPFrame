<?php
/**
 * PHPFrame/MVC/Model.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage MVC
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Model Class
 * 
 * This class is used to implement the MVC (Model/View/Controller) architecture 
 * in the components.
 * 
 * Models are implemented to represent information on which the application operates.
 * In most cases many models will be created for each component in order to represent
 * the different logical elements.
 * 
 * This class should be extended when creating component models as it is an 
 * abstract class. See the built in components (dashboard, user, admin, ...) 
 * for examples.
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage MVC
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame_MVC_ActionController, PHPFrame_MVC_View
 * @since      1.0
 * @abstract
 */
abstract class PHPFrame_MVC_Model
{
    /**
     * An array containing strings with internal error messages if any
     * 
     * @var array
     */
    protected $_error=array();
    
    /**
     * Get last error in model
     * 
     * This method returns a string with the error message or FALSE if no errors.
     * 
     * @return mixed
     */
    public function getLastError() 
    {
        if (is_array($this->_error) && count($this->_error) > 0) {
            return end($this->_error);
        }
        else {
            return false;
        }
    }
}
