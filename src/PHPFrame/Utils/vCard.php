<?php
/**
 * PHPFrame/Utils/vCard.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Utils
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * vCard Class
 * 
 * Class to manipulate with vCard information, according vCard v2.1 and vCard v3.0.
 * References: http://www.imc.org/pdi/
 * 
 * This class wraps around the VCARD class by Viatcheslav Ivanov, E-Witness Inc., Canada;
 * mail: ivanov@e-witness.ca, v_iv@hotmail.com;
 * web: www.e-witness.ca; www.coolwater.ca; www.strongpost.net;
 * version: 1.00 /09.20.2002
 *
 * @category PHPFrame
 * @package  Utils
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class PHPFrame_vCard
{
    /**
     * Viatcheslav Ivanov's VCARD object
     * 
     * @var VCARD
     */
    private $_vcard = null;
    
    /**
     * Constructor
     * 
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        $this->_vcard = new VCARD;
    }
    
    /**
     * Convert vCard object to string
     * 
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        return $this->_vcard->getvCard();
    }
    
    /**
     * Set name properties.
     * 
     * @param string $last_name    The last name.
     * @param string $first_name   The first name.
     * @param string $middle_names Any middle names.
     * @param string $prefixes     Name prefixes
     * @param string $suffixes     Name suffixes
     * 
     * @return void
     * @since  1.0
     */
    public function setName(
        $last_name, 
        $first_name, 
        $middle_names="", 
        $prefixes="", 
        $suffixes=""
    ) {
        $this->_vcard->setName(
            $last_name, 
            $first_name, 
            $middle_names, 
            $prefixes, 
            $suffixes
        );
    }
    
    /**
     * Get Name
     * 
     * @param string $attr Accepted values: "LAST", "FIRST", "MIDDLE", "PREF", 
     * "SUFF"
     * 
     * @return string
     * @since  1.0
     */
    public function getName($attr="LAST")
    {
        return $this->_vcard->getName($attr);
    }
    
    /**
     * Set formatted name
     * 
     * @param string $formatted_name A stringf with the formatted name.
     * 
     * @return void
     * @since  1.0
     */
    public function setFormattedName($formatted_name)
    {
        $this->_vcard->setFormattedName($formatted_name);
    }
    
    /**
     * Get formatted name.
     * 
     * @return string
     * @since  1.0
     */
    public function getFormattedName()
    {
        $this->_vcard->getFormattedName();
    }
    
    /**
     * Set email.
     * 
     * @param string $email The email address.
     * @param string $attr  [Optional] Default value is INTERNET
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setEmail($email, $attr="INTERNET")
    {
        $this->_vcard->setEmail($email, $attr);
    }
    
    /**
     * Set photo.
     * 
     * @param string $photo_url URL to the photo.
     * 
     * @return void
     * @since  1.0
     */
    public function setPhoto($photo_url)
    {
        $this->_vcard->setBinary("PHOTO", $photo_url, "URL");
    }
}
