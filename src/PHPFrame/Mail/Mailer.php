<?php
/**
 * PHPFrame/Mail/Mailer.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Mail
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * This class wraps around PHPMailer
 *
 * @category PHPFrame
 * @package  Mail
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_Mailer extends PHPMailer
{
    /**
     * Message ID suffix
     *
     * @var string
     */
    private $_messageid_sfx = null;
    /**
     * Options array
     *
     * @var array
     */
    private $_options = array(
        "mailer"      => "mail",
        "host"        => "localhost",
        "port"        => 25,
        "auth"        => false,
        "user"        => null,
        "pass"        => null,
        "fromaddress" => null,
        "fromname"    => null
    );

    /**
     * Constructor
     *
     * @param array $options [Optional] Options: mailer, host, port, auth,
     *                       user, pass, fromaddress, fromname.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        if (!is_null($options)) {
            foreach ($options as $key=>$value) {
                if (array_key_exists($key, $this->_options)) {
                    $this->_options[$key] = $value;
                }
            }
        }

        $this->Mailer   = (string) $this->_options["mailer"];
        $this->Host     = (string) $this->_options["host"];
        $this->Port     = (int)    $this->_options["port"];
        $this->SMTPAuth = (bool)   $this->_options["auth"];
        $this->Username = (string) $this->_options["user"];
        $this->Password = (string) $this->_options["pass"];
        $this->From     = (string) $this->_options["fromaddress"];
        $this->FromName = (string) $this->_options["fromname"];

        // Sets the hostname to use in Message-Id and Received headers and as
        // default HELO string. If empty, the value returned by SERVER_NAME is used
        // or 'localhost.localdomain'.
        $this->Hostname = (string) $this->_options["host"];
    }

    /**
     * Magic method invoked when object is serialised.
     *
     * @return array
     * @since  1.0
     */
    public function __sleep()
    {
        $this->ClearAllRecipients();
        $this->ClearAttachments();
        $this->ClearCustomHeaders();
        $this->ClearReplyTos();

        return array_keys(get_object_vars($this));
    }

    /**
     * This method allows to add a suffix to the message id.
     *
     * This can be very useful when adding data to the message id for processing of
     * replies. The suffix is added to the the headers in $this->CreateHeader() and
     * is encoded in base64.
     *
     * @param string $str The message ID suffix.
     *
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
     * @return string
     * @since  1.0
     */
    public function CreateHeader()
    {
        $header = parent::CreateHeader();

        if (!is_null($this->_messageid_sfx)) {
            $pattern      = "/Message\-Id\: <([a-zA-Z0-9]+)@/i";
            $replacement  = "Message-Id: <$1-";
            $replacement .= base64_encode($this->_messageid_sfx)."@";
            $header       = preg_replace($pattern, $replacement, $header);
        }

        return $header;
    }
}
