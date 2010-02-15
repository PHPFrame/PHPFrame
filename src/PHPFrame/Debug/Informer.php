<?php
/**
 * PHPFrame/Debug/Informer.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Debug
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * This class implements the "Observer" base class in order to subscribe to updates
 * from "observable" objects (objects of type PHPFrame_Subject).
 * 
 * @category PHPFrame
 * @package  Debug
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
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
