<?php
/**
 * PHPFrame/Mail/Mailer.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Mail
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * This class wraps around PHPMailer and sets up the mailer using the SMTP details 
 * provided in main config (etc/phpframe.ini).
 * 
 * @category PHPFrame
 * @package  Mail
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Mailer extends PHPMailer
{
    private $_messageid_sfx=null;
    
    /**
     * Constructor
     * 
     * @param string $mailer
     * @param string $host
     * @param int    $port
     * @param bool   $auth
     * @param string $user
     * @param string $pass
     * @param string $fromaddress
     * @param string $fromname
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(
        $mailer="mail", 
        $host="localhost", 
        $port="25", 
        $auth=false, 
        $user=null, 
        $pass=null, 
        $fromaddress=null, 
        $fromname=null
    ) 
    {
        
        $this->Mailer   = $mailer;
        $this->Host     = $host;
        $this->Port     = $port;
        $this->SMTPAuth = $auth;
        $this->Username = $user;
        $this->Password = $pass;
        $this->From     = $fromaddress;
        $this->FromName = $fromname;
        
        // Sets the hostname to use in Message-Id and Received headers and as 
        // default HELO string. If empty, the value returned by SERVER_NAME is used 
        // or 'localhost.localdomain'.
        $this->Hostname = $host;
    }
    
    /**
     * This method allows to add a suffix to the message id.
     * 
     * This can be very useful when adding data to the message id for processing of 
     * replies. The suffix is added to the the headers in $this->CreateHeader() and 
     * is encoded in base64.
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setMessageIdSuffix($str) 
    {
        $this->_messageid_sfx = (string) $str;
    }
    
    /**
     * Get the message id suffix.
     * 
     * @access public
     * @return string
     * @since  1.0
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
     * @access public
     * @return string
     * @since  1.0
     */
    public function CreateHeader() 
    {
        $result = parent::CreateHeader();
        
        if (!is_null($this->_messageid_sfx)) {
            $pattern      = "/Message\-Id\: <([a-zA-Z0-9]+)@/i";
            $replacement  = "Message-Id: <$1-";
            $replacement .= base64_encode($this->_messageid_sfx)."@";
            $result       = preg_replace($pattern, $replacement, $result);
        }
        
        return $result;
    }
}
