<?php
/**
 * PHPFrame/Mapper/DomainObject.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Mapper
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * DomainObject Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Mapper
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
abstract class PHPFrame_Mapper_DomainObject extends PHPFrame_Base_Object
{
    /**
     * Constructor
     * 
     * @param array $options
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        // Process options argument if passed
        if (!is_null($options)) {
            // Make sure that we have been passed an array
            $this->ensureType("array", $options);
            
            // Create reflection object
            $reflectionObj = new ReflectionClass($this);
            
            foreach ($options as $key=>$value) {
                // Build string with setter name
                $setter_name = "set".ucfirst($key);
                
                if ($reflectionObj->hasMethod($setter_method)) {
                    // Get reflection method for setter
                    $setter_method = $reflectionObj->getMethod($setter_method);
                    
                    // Invoke setter if it takes only one required argument
                    if ($setter_method->getNumberOfRequiredParameters() == 1) {
                        $this->$setter_method($value);
                    }
                }
            }
        }
    }
    
    /**
     * Bind array data to object
     * 
     * @param array $array
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    abstract public function bind(array $array);
    
    /**
     * Convert object to array
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    abstract public function toArray();
}