<?php
/**
 * PHPFrame/User/User.php
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
 * User Class
 * 
 * @category PHPFrame
 * @package  User
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_User extends PHPFrame_PersistentObject
{
    /**
     * String representation of the groupid.
     * 
     * @var string
     */
    private $_groupname;
    /**
     * vCard object used to store user details 
     * 
     * @var PHPFrame_VCard
     */
    private $_vcard=null;
    
    /**
     * Constructor
     * 
     * @param array $options
     * 
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        // before we construct the parent we add the necessary fields
        $this->addField(
            "groupid", 
            0, 
            false, 
            new PHPFrame_IntFilter()
        );
        $this->addField(
            "username", 
            "guest", 
            false, 
            new PHPFrame_RegexpFilter(array(
                "regexp"=>'/^[a-zA-Z\.]{3,20}$/', 
                "min_length"=>3, 
                "max_length"=>20
            ))
        );
        $this->addField(
            "password", 
            null, 
            false, 
            new PHPFrame_RegexpFilter(array(
                "regexp"=>'/^.{6,100}$/',
                "min_length"=>6, 
                "max_length"=>100
            ))
        );
        $this->addField(
            "firstname", 
            null, 
            true, 
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>50))
        );
        $this->addField(
            "lastname", 
            null, 
            true, 
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>50))
        );
        $this->addField(
            "email", 
            null, 
            false, 
            new PHPFrame_EmailFilter(array("min_length"=>7, "max_length"=>100))
        );
        $this->addField(
            "block", 
            false, 
            false, 
            new PHPFrame_BoolFilter()
        );
        $this->addField(
            "last_visit", 
            null, 
            true, 
            new PHPFrame_IntFilter()
        );
        $this->addField(
            "params", 
            array(), 
            true, 
            new PHPFrame_StringFilter()
        );
        $this->addField(
            "deleted", 
            0, 
            true, 
            new PHPFrame_IntFilter()
        );
        
        // If we are passed a vCard object we deal with this first
        if (isset($options['vcard'])
            && $options['vcard'] instanceof PHPFrame_VCard
        ) {
            $this->_vcard = $options['vcard'];
            unset($options['vcard']);
        } else {
            $this->_vcard = new PHPFrame_VCard();
        }
        
        // Once we have set the vCard object we call the parent's constructor
        // to process the options array
        parent::__construct($options);
    }
    
    /**
     * Get groupid
     * 
     * @return int
     * @since  1.0
     */
    public function getGroupId()
    {
        return $this->fields["groupid"];
    }
    
    /**
     * Set groupid
     * 
     * @param int $int The group ID.
     * 
     * @return void
     * @since  1.0
     */
    public function setGroupId($int)
    {
        $this->fields["groupid"] = $this->validate("groupid", $int);
    }
    
    /**
     * Get groupname
     * 
     * @return string
     * @since  1.0
     */
    public function getGroupName()
    {
        return $this->_groupname;
    }
    
    /**
     * Set groupname
     * 
     * @param string $str The group name.
     * 
     * @return void
     * @since  1.0
     */
    public function setGroupName($str)
    {
        $this->_groupname = trim((string) $str);
    }
    
    /**
     * Get username
     * 
     * @return string
     * @since  1.0
     */
    public function getUserName()
    {
        return $this->fields["username"];
    }
    
    /**
     * Set username
     * 
     * @param string $str The username.
     * 
     * @return void
     * @since  1.0
     */
    public function setUserName($str)
    {
        $this->fields["username"] = $this->validate("username", $str);
    }
    
    /**
     * Get password
     * 
     * @return string
     * @since  1.0
     */
    public function getPassword()
    {
        return $this->fields["password"];
    }
    
    /**
     * Set password
     * 
     * @param string $str The password.
     * 
     * @return void
     * @since  1.0
     */
    public function setPassword($str)
    {
        $this->fields["password"] = $this->validate("password", $str);
    }
    
    /**
     * Create enrypted password
     * 
     * @param string $str The unencrypted password.
     * 
     * @return string
     * @since  1.0
     */
    public function encryptPassword($str)
    {
        $str = $this->validate("password", $str);
        
        // Get random 32 char salt
        $salt = PHPFrame_Crypt::genRandomPassword(32);
        // Encrypt password using salt
        $crypt = PHPFrame_Crypt::getCryptedPassword($str, $salt);
        
        // Set password to encrypted string
        return $crypt.':'.$salt;
    }
    
    /**
     * Get first name
     * 
     * @return string
     * @since  1.0
     */
    public function getFirstName()
    {
        return $this->_vcard->getName("FIRST");
    }
    
    /**
     * Set first name
     * 
     * @param string $str The first name.
     * 
     * @return void
     * @since  1.0
     */
    public function setFirstName($str)
    {
        $str = $this->validate("firstname", $str);
        
        // Set first name in vCard object making sure we dont overwrite last name)
        $this->_vcard->setName($this->getLastName(), $str, null, null, null);
        
        // Set property
        $this->fields["firstname"] = $str;
    }
    
    /**
     * Get last name
     * 
     * @return string
     * @since  1.0
     */
    public function getLastName()
    {
        return $this->_vcard->getName("LAST");
    }
    
    /**
     * Set last name
     * 
     * @param string $str The last name.
     * 
     * @return void
     * @since  1.0
     */
    public function setLastName($str)
    {
        $str = $this->validate("lastname", $str);
        
        // Set last name in vCard object making sure we dont overwrite first name)
        $this->_vcard->setName($str, $this->getFirstName(), null, null, null);
        
        // Set property
        $this->fields["lastname"] = $str;
    }
    
    /**
     * Get email
     * 
     * @return string
     * @since  1.0
     */
    public function getEmail()
    {
        return $this->fields["email"];
    }

    /**
     * Set email
     * 
     * @param string $str The email.
     * 
     * @return void
     * @since  1.0
     */
    public function setEmail($str)
    {
        $str = $this->validate("email", $str);
        
        // Set last name in vCard object making sure we dont overwrite first name)
        $this->_vcard->addEmail($str);
        
        // Set property
        $this->fields["email"] = $str;
    }
    
    /**
     * Get block flag
     * 
     * @return bool
     * @since  1.0
     */
    public function getBlock()
    {
        return $this->fields["block"];
    }
    
    /**
     * Set block flag
     * 
     * @param bool $bool Flag indicating whether user is blocked (no access).
     * 
     * @return void
     * @since  1.0
     */
    public function setBlock($bool)
    {
        $bool = $this->validate("block", $bool);
        
        // Set property
        $this->fields["block"] = (bool) $bool;
    }
    
    /**
     * Get last visit timestamp
     * 
     * @return int
     * @since  1.0
     */
    public function getLastVisit()
    {
        return $this->fields["last_visit"];
    }
    
    /**
     * Set last visit datetime
     * 
     * @param int $int UNIX timestamp.
     * 
     * @return void
     * @since  1.0
     */
    public function setLastVisit($int)
    {
        $this->fields["last_visit"] = $this->validate("last_visit", $int);
    }
    
    /**
     * Get params
     * 
     * @return string
     * @since  1.0
     */
    public function getParams()
    {
        return $this->fields["params"];
    }
    
    /**
     * Set params
     * 
     * @param string|array $params Either a serialised string or an array.
     * 
     * @return void
     * @since  1.0
     */
    public function setParams($params)
    {
        if (is_string($params) && !empty($params)) {
            $params = unserialize($params);
        }
        
        if (!is_array($params)) {
                $params = array();
        }
        
        if (!is_array($params)) {
            $msg  = "Argument \$params must be either a serialised string or ";
            $msg .= "an array";
            throw new InvalidArgumentException($msg);
        }
        
        $this->fields["params"] = $params;
    }
    
    /**
     * Get deleted timestamp
     * 
     * @return int
     * @since  1.0
     */
    public function getDeleted()
    {
        return $this->fields["deleted"];
    }
    
    /**
     * Set deleted datetime
     * 
     * @param int $int Either 0 (deleted) or 1.
     * 
     * @return void
     * @since  1.0
     */
    public function setDeleted($int)
    {
        if (empty($int)) {
            return;
        }
        
        $this->fields["deleted"] = $this->validate("deleted", $int);
    }
    
    /**
     * Get vCard object for this user
     * 
     * @return PHPFrame_VCard
     * @since  1.0
     */
    public function getVCard()
    {
        return $this->_vcard;
    }
    
    /**
     * Set vCard object for this user
     * 
     * @param PHPFrame_VCard $vcard An instance of PHPFrame_VCard.
     * 
     * @return void
     * @since  1.0
     */
    public function setVCard(PHPFrame_VCard $vcard)
    {
        //TODO: Here we have to parse the vCard data 
        // and update the firstname and lastname proerties
        $this->_vcard = $vcard;
    }
    
    /**
     * Return Row object as associative array
     * 
     * @return array
     * @since  1.0
     */
    public function getIterator()
    {
        $array = array();
        
        foreach ($this->fields as $key=>$value) {
            if (($key == "params" || $key == "openid_urls")) {
                if (is_array($value) && count($value) > 0) {
                    $value = serialize($value);
                } else {
                    $value = "";
                }
            }
            
            $array[$key] = $value;
        }
        
        return new ArrayIterator($array);
    }
}
