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
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * User Class
 * 
 * @category PHPFrame
 * @package  User
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
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
     * @var PHPFrame_vCard
     */
    private $_vcard=null;
    
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
           false, 
           new PHPFrame_StringFilter(array("min_length"=>1, "max_length"=>50))
        );
        $this->addField(
           "lastname", 
           null, 
           false, 
           new PHPFrame_StringFilter(array("min_length"=>1, "max_length"=>50))
        );
        $this->addField(
           "email", 
           null, 
           false, 
           new PHPFrame_EmailFilter(array("min_length"=>7, "max_length"=>100))
        );
        $this->addField(
           "photo", 
           "default.png", 
           false, 
           new PHPFrame_StringFilter(array("min_length"=>5, "max_length"=>128))
        );
        $this->addField(
           "notifications", 
           true, 
           false, 
           new PHPFrame_BoolFilter()
        );
        $this->addField(
           "show_email", 
           false, 
           false, 
           new PHPFrame_BoolFilter()
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
           "activation", 
           null, 
           true, 
           new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>100))
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
        $this->addField(
           "openid_urls", 
           null, 
           true, 
           new PHPFrame_StringFilter()
        );
        
        // If we are passed a vCard object we deal with this first
        if (
            isset($options['vcard'])
            && $options['vcard'] instanceof PHPFrame_vCard
        ) {
            $this->_vcard = $options['vcard'];
            unset($options['vcard']);
        } else {
            $this->_vcard = new PHPFrame_vCard();
        }
        
        // Once we have set the vCard object we call the parent's constructor
        // to process the options array
        parent::__construct($options);
    }
    
    /**
     * Get groupid
     * 
     * @access public
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
     * @param int $int
     * 
     * @access public
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
     * @access public
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
     * @param string $str
     * 
     * @access public
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
     * @access public
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
     * @param string $str
     * 
     * @access public
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
     * @access public
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
     * @param string $str
     * 
     * @access public
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
     * @param string $str
     * 
     * @access public
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
     * @access public
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
     * @param string $str
     * 
     * @access public
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
     * @access public
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
     * @param string $str
     * 
     * @access public
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
     * @access public
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
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setEmail($str)
    {
        $str = $this->validate("email", $str);
        
        // Set last name in vCard object making sure we dont overwrite first name)
        $this->_vcard->setEmail($str);
        
        // Set property
        $this->fields["email"] = $str;
    }
    
    /**
     * Get photo
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getPhoto()
    {
        return $this->fields["photo"];
    }
    
    /**
     * Set photo
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setPhoto($str)
    {
        $str = $this->validate("photo", $str);
        
        // Set last name in vCard object making sure we dont overwrite first name)
        $this->_vcard->setPhoto($str);
        
        // Set property
        $this->fields["photo"] = $str;
    }
    
    /**
     * Get notifications flag
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function getNotifications()
    {
        return $this->fields["notifications"];
    }
    
    /**
     * Set notifications flag
     * 
     * @param bool $bool
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setNotifications($bool)
    {
        $bool = $this->validate("notifications", $bool);
        
        // Set local property
        $this->fields["notifications"] = (bool) $bool;
    }
    
    /**
     * Get show_email flag
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function getShowEmail()
    {
        return $this->fields["show_email"];
    }
    
    /**
     * Set show_email flag
     * 
     * @param bool $bool
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setShowEmail($bool)
    {
        $bool = $this->validate("show_email", $bool);
        
        // Set property
        $this->fields["show_email"] = (bool) $bool;
    }
    
    /**
     * Get block flag
     * 
     * @access public
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
     * @param bool $bool
     * 
     * @access public
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
     * @access public
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
     * @param int $int
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setLastVisit($int)
    {
        $this->fields["last_visit"] = $this->validate("last_visit", $int);
    }
    
    /**
     * Get activation key
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getActivation()
    {
        return $this->fields["activation"];
    }
    
    /**
     * Set activation key
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setActivation($str)
    {
        $this->fields["activation"] = $this->validate("activation", $str);
    }
    
    /**
     * Get params
     * 
     * @access public
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
     * @param string|array $params Either a serialised string or an array
     * 
     * @access public
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
     * @access public
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
     * @param int $int
     * 
     * @access public
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
     * @access public
     * @return PHPFrame_vCard
     * @since  1.0
     */
    public function getVCard()
    {
        return $this->_vcard;
    }
    
    /**
     * Set vCard object for this user
     * 
     * @param PHPFrame_vCard $vcard
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setVCard(PHPFrame_vCard $vcard)
    {
        //TODO: Here we have to parse the vCard data 
        // and update the firstname and lastname proerties
        $this->_vcard = $vcard;
    }
    
    /**
     * Get OpenId URLs
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getOpenidUrls()
    {
        return $this->fields["openid_urls"];
    }
    
    /**
     * Add an OpenId URL for this user
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function addOpenidUrl($str)
    {
        $str = $this->validate("openid_urls", $str);
        
        if (!in_array($str, $this->fields["openid_urls"])) {
            $this->fields["openid_urls"][] = $str;
        }
    }
    
    /**
     * Remove a given URL from the openid_urls array
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function removeOpenidUrl($str)
    {
        $str = $this->validate("openid_urls", $str);
        
        foreach ($this->fields["openid_urls"] as $url) {
            if ($str != $url) {
                $array[] = $url;
            }
        }
        
        $this->fields["openid_urls"] = $array;
    }
    
    /**
     * Return Row object as associative array
     * 
     * @access public
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
