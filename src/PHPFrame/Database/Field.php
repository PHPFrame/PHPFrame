<?php
/**
 * PHPFrame/Database/Field.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Database
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */

/**
 * Field Class
 * 
 * The "Field" class is an abstraction of a table column in a database.
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Database
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Database_Field
{
    private $_field=null;
    private $_type=null;
    private $_null=null;
    private $_key=null;
    private $_default=null;
    private $_extra=null;
    private $_value=null;
    
    public function __construct($options=array())
    {
        foreach ($options as $key=>$value) {
            $setter_method = "_set".ucfirst(strtolower($key));
            
            if (!is_callable(array($this, $setter_method))) {
                $msg = "Wrong option passed to constructor.";
                throw new PHPFrame_Exception_Database($msg);
            }
            
            $this->$setter_method($value);
        }
    }
    
    public function __get($property)
    {
        $property = "_".strtolower($property);
        
        if (!property_exists($this, $property)) {
            $msg = "Tried to get property '".$property;
            $msg .= "' that doesn't exist in object.";
            throw new PHPFrame_Exception_Database($msg);
        }
        
        return $this->$property;
    }
    
    public function __toString()
    {
        return "FIX ME!!!";
    }
    
    public function getField()
    {
        return $this->_field;
    }
    
    public function isPrimaryKey()
    {
        return ($this->_key == 'PRI');
    }
    
    public function setPrimaryKey($value)
    {
        $this->_setKey($value);
    }
    
    public function setValue($value)
    {
        // Handle NULL values
        $value = ereg_replace("'NULL'", "NULL", $value);
        
        $this->_value = $value;
    }
    
    public function getValue()
    {
        return $this->_value;
    }
    
    /**
     * Check columns data types and required fields before saving to db.
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function isValid()
    {
        // If column is empty and auto_increment we set to null and return
        if (empty($this->_value) && $this->_extra == 'auto_increment') {
            $this->_value = null;
            return true;
        }
            
        // If no assigned value
        if (is_null($this->_value)) {
            // Set default value if any
            if (!is_null($this->_default)) {
                $this->_value = $this->_default;
                return true;
            }
            
            // If column is timestamp and default value is CURRENT_TIMESTAMP 
            // replace with current date
            if ($this->_default == "CURRENT_TIMESTAMP") {
                $this->_value = date("Y-m-d H:i:s");
            }
        }
        
        // If value is still null (after setting default) and field allows null
        // we simply skip the test
        if (is_null($this->_value) && $this->_null == "YES") {
            return true;
        }
        
        // Get type and length from input type string
        preg_match('/([a-zA-Z]+)(\((.+)\))?/i', $this->_type, $matches);
        
        $type = strtolower($matches[1]); // make string lower case
        if (isset($matches[3])) {
            $length = $matches[3];    
        }
        
        // Make type variation prefix (ie: tinyint to int or longtext to long)
        $prefixes = array('tiny', 'small', 'medium', 'big', 'long');
        $type = str_replace($prefixes, '', $type);
        
        // Perform validation depending on data type
        switch ($type) {
            case 'int' : 
                $pattern = '/^-?\d+$/';
                break;
                    
            case 'float' :
            case 'double' :
            case 'decimal' :
                $pattern = '/^-?\d+\.?\d+$/';
                break;
                    
            case 'char' :
            case 'varchar' :
                $pattern = '/^.{0,'.$length.'}$/';
                break;
                
            case 'text' :
            case 'blob' :
            case 'enum' :
            case 'datetime' :
            case 'date' :
            case 'time' :
            case 'year' :
            case 'timestamp' :
            case 'binary' :
            case 'bool' :
            default : 
                $pattern = '/^.*$/im';
                break;
        }
        
        if (isset($this->_value) && !preg_match($pattern, $this->_value)) {
            $exception_msg = "Wrong type for column '".$this->getField()."'. ";
            $exception_msg .= "Expected '".$this->_type."' and got '";
            $exception_msg .= $this->getValue()."'";
            $exception_code = PHPFrame_Exception::WARNING;
            throw new PHPFrame_Exception($exception_msg, $exception_code);
        }
    }
    
    private function _setField($str)
    {
        $this->_field = $str;
    }
    
    private function _setType($str)
    {
        $this->_type = $str;
    }
    
    private function _setNull($str)
    {
        $this->_null = $str;
    }
    
    private function _setKey($str)
    {
        $this->_key = $str;
    }
    
    private function _setDefault($str)
    {
        $this->_default = $str;
    }
    
    private function _setExtra($str)
    {
        $this->_extra = $str;
    }
}