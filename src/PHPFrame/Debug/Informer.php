<?php
/**
 * PHPFrame/Debug/Informer.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Debug
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * This class implements the "Observer" base class in order to subscribe to updates
 * from "observable" objects (objects of type PHPFrame_Subject).
 * 
 * @category PHPFrame
 * @package  Debug
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Observer
 * @since    1.0
 */
class PHPFrame_Informer extends PHPFrame_Observer
{
    /**
     * Reference to PHPMailer object used by this informer
     * 
     * @var PHPFrame_Mailer
     */
    private $_mailer;
    
    /**
     * Constructor
     * 
     * @param PHPFrame_Mailer $mailer     Reference to a mailer object.
     * @param array           $recipients An array containing email addresses 
     *                                    of the recipients of this informer.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Mailer $mailer, array $recipients)
    {
        $this->_mailer = $mailer;
        
        foreach ($recipients as $recipient) {
            $recipient = filter_var($recipient, FILTER_VALIDATE_EMAIL);
            $this->_mailer->AddAddress($recipient);
        }
    }
    
    /**
     * Handle observed objects updates
     * 
     * @param PHPFrame_Subject $subject The subject issuing the update
     * 
     * @return void
     * @since  1.0
     */
    protected function doUpdate(PHPFrame_Subject $subject)
    {
        list($msg, $type) = $subject->getLastEvent();
        
        $informer_level = PHPFrame::Config()->get("debug.informer_level");
        if ($type <= $informer_level) {
            // Build the email
            $this->_mailer->Subject = "PHPFrame Informer notification";
            $this->_mailer->Body    = $msg;
            
            // Send the email
            $this->_mailer->Send();
        }
    }
}
