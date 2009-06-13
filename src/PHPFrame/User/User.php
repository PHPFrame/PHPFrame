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
    private $_error=array();
    
    /**
     * Constructor
     * 
     * @return    void
     * @since    1.0
     */
    public function __construct() 
    {
        $this->_row = new PHPFrame_Database_Row("#__users");
    }
    
    public function __get($key) 
    {
        return $this->get($key);
    }
    
    public function get($key) 
    {
        return $this->_row->get($key);
    }
    
    public function set($key, $value) 
    {
        $this->_row->set($key, $value);
    }
    
    /**
     * Load user row by id
     * 
     * This method overrides the inherited load method.
     * 
     * @access    public
     * @param    int        $id         The row id.
     * @param    string    $exclude     A list of key names to exclude from binding process separated by commas.
     * @return    mixed    The loaded row object of FALSE on failure.
     * @since     1.0
     */
    public function load($id, $exclude='password') 
    {
        if (!$this->_row->load($id, $exclude)) {
            return false;
        }
        else {
            return $this;    
        }
    }
    
    public function bind($array, $exclude='', $foreign_keys=array()) 
    {
        $this->_row->bind($array, $exclude, $foreign_keys);
    }
    
    /**
     * Store user
     * 
     * This method overrides the inherited store method in order to encrypt the password before storing.
     * 
     * @access    public
     * @return    bool    Returns TRUE on success or FALSE on failure.
     * @since     1.0
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
    
    public function getLastError() 
    {
        if (is_array($this->_error) && count($this->_error) > 0) {
            return end($this->_error);
        }
        else {
            return false;
        }
    }
    
    private function _emailExists($email) 
    {
        $query = "SELECT id FROM #__users ";
        $query .= " WHERE email = '".$email."' ";
        $query .= " AND (deleted = '0000-00-00 00:00:00' OR deleted IS NULL) ";
        //echo str_replace("#_", "eo", $query); exit;
        //TODO: FIx me!!
        
        $id = PHPFrame::getDB()->loadResult($query);
        
        return ($id > 0);
    }
}
