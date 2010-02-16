<?php
/**
 * PHPFrame/Filter/EnumFilter.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Filter
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Enum Filter
 * 
 * @category PHPFrame
 * @package  Filter
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_EnumFilter extends PHPFrame_Filter
{
    
    public function __construct(array $options=null)
    {
        $this->registerOption('enums', array());
        parent::__construct($options);
    }
    
    /**
     * Processes the given enum value against the allowed enums.
     * 
     * @see src/PHPFrame/Filter/PHPFrame_Filter#process($value)
     */
    public function process($value)
    {
        $enums = $this->getOption('enums');
        $found = false;
        foreach ($enums as $enum){
            if ($value == $enum){
                $found = true;
            }
        }
        if (!$found){
            $err_msg = "Argument \$value in ".get_class($this)."::process() is 
            not one of the stored enums";
            $this->fail($err_msg, 'InvalidArgumentException');
            return false;
        }
        else {
            return $value;
        }
    }
    
    public function setEnums(array $enums)
    {
        $this->setOption('enums', $enums);
    }
}