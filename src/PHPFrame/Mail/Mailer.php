<?php
/**
 * PHPFrame/Mail/Mailer.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Mail
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Mailer Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Mail
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Mail_Mailer extends PHPMailer
{
    private $_messageid_sfx=null;
    private $_error=array();
    
    /**
     * Constructor
     * 
     * Initialise some PHPMailer default values
     * 
     * @return    void
     * @since    1.0
     */
    public function __construct() 
    {
        $this->Mailer = config::MAILER;
        $this->Host = config::SMTP_HOST;
        $this->Port = config::SMTP_PORT;
        $this->SMTPAuth = config::SMTP_AUTH;
        $this->Username = config::SMTP_USER;
        $this->Password = config::SMTP_PASSWORD;
        $this->From = config::FROMADDRESS;
        $this->FromName = config::FROMNAME;
        
        // Sets the hostname to use in Message-Id and Received headers and as default HELO string. 
        // If empty, the value returned by SERVER_NAME is used or 'localhost.localdomain'.
        $this->Hostname = config::SMTP_HOST;
    }
    
    /**
     * This method allows to add a suffix to the message id.
     * 
     * This can be very useful when adding data to the message id for processing of replies.
     * 
     * The suffix is added to the the headers in $this->CreateHeader() and is encoded in base64.
     * 
     * @param    string    $str
     * @return    void
     */
    public function setMessageIdSuffix($str) 
    {
        $this->_messageid_sfx = (string)$str;
    }
    
    /**
     * Get the message id suffix.
     * 
     * @return    string
     */
    public function getMessageIdSuffix() 
    {
        return $this->_messageid_sfx;
    }
    
    /**
     * This method overrides the parent CreateHeader() method.
     * 
     * This method appends the message id suffix encoded in base64.
     * 
     * @see     src/lib/phpmailer/PHPMailer#CreateHeader()
     * @return    string
     */
    public function CreateHeader() 
    {
        $result = parent::CreateHeader();
        
        if (!is_null($this->_messageid_sfx)) {
            $pattern = "/Message\-Id\: <([a-zA-Z0-9]+)@/i";
            $replacement = "Message-Id: <$1-".base64_encode($this->_messageid_sfx)."@";
            $result = preg_replace($pattern, $replacement, $result);
        }
        
        return $result;
    }
}
