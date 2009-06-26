<?php
/**
 * PHPFrame/User/User.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage User
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * User Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage User
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_User
{
    /**
     * Row object mapper
     * 
     * @var PHPFrame_Database_Row
     */
    private $_row=null;
    /**
     * Error array
     * 
     * @var array
     */
    private $_error=array();
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct() 
    {
        $this->_row = new PHPFrame_Database_Row("#__users");
    }
    
    /**
     * Magic getter invoked when trying to access an undefined property
     * 
     * @param string $key The property name
     * 
     * @access public
     * @return mixed
     * @since  1.0
     */
    public function __get($key) 
    {
        return $this->get($key);
    }
    
    /**
     * Magic method invoked when trying to use an IdObject as a string.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        return $this->toString();
    }
    
    /**
     * Convert object to string
     * 
     * @param bool $show_keys Boolean to indicate whether we want to show the
     *                        column names. Default is TRUE.
     *                        
     * @access public
     * @return string
     * @since  1.0
     */
    public function toString($show_keys=true)
    {
        return $this->_row->toString($show_keys);
    }
    
    /**
     * Return Row object as associative array
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function toArray()
    {
        return $this->_row->toArray();
    }
    
    
    
    /**
     * Get user property
     * 
     * @param string $key The property name
     * 
     * @access public
     * @return mixed
     * @since  1.0
     */
    public function get($key) 
    {
        return $this->_row->get($key);
    }
    
    /**
     * Set a property in the user object
     * 
     * @param string $key   The property name
     * @param mixed  $value The value we want to set
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function set($key, $value) 
    {
        $this->_row->set($key, $value);
    }
    
    /**
     * Load user row by id
     * 
     * This method overrides the inherited load method.
     * 
     * @param int|PHPFrame_Database_IdObject $id      Normally an integer with the primary 
     *                                                key value of the row we want to load.
     *                                                Alternatively you can pass an IdObject.
     * @param string                         $exclude A list of key names to exclude from 
     *                                                binding process separated by commas.
     * 
     * @access public
     * @return PHPFrame_User
     * @since  1.0
     */
    public function load($id, $exclude='password') 
    {
        // Delegate to row object
        $this->_row->load($id, $exclude);
        
        return $this;
    }
    
    /**
     * Bind array to user object
     * 
     * @param array  $array    The array to bind to the user object
     * @param string $exclude A list of key names to exclude from binding 
     *                        process separated by commas.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function bind($array, $exclude='') 
    {
        $this->_row->bind($array, $exclude);
    }
    
    /**
     * Store user
     * 
     * @access public
     * @return bool   Returns TRUE on success or FALSE on failure.
     * @since  1.0
     */
    public function store() 
    {
        // Before we store new users we check whether email already exists in db
        $id = $this->_row->get('id');
        if (empty($id) && $this->_emailExists($this->_row->get('email'))) {
            $this->_error[] = _PHPFRAME_LANG_EMAIL_ALREADY_REGISTERED;
            return false;
        }
        
        // Encrypt password for storage
        if (!is_null($this->_row->get('password'))) {
            $salt = PHPFrame_Utils_Crypt::genRandomPassword(32);
            $crypt = PHPFrame_Utils_Crypt::getCryptedPassword($this->_row->get('password'), $salt);
            $this->_row->set('password', $crypt.':'.$salt);
        }
        
        // Invoke row store() method to store row in db
        return $this->_row->store();
    }
    
    /**
     * Get last error
     * 
     * @access public
     * @return string
     * @since  1.0
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
    
    /**
     * Is email address already registered?
     * 
     * @param string $email The email address to check
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    private function _emailExists($email) 
    {
        $sql = "SELECT id FROM #__users ";
        $sql .= " WHERE email = ':email' ";
        $sql .= " AND (deleted = '0000-00-00 00:00:00' OR deleted IS NULL) ";
        //echo str_replace("#_", "eo", $query); exit;
        //TODO: FIx me!!
        
        $id = PHPFrame::DB()->fetchColumn($sql, array(":email"=>$email));
        
        return ($id > 0);
    }
}
