<?php
/**
 * @version       SVN: $Id$
 * @package       PHPFrame
 * @subpackage    mail
 * @copyright     2009 E-noise.com Limited
 * @license       http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * IMAP Class
 * 
 * @package        PHPFrame
 * @subpackage     mail
 * @since         1.0
 */
class PHPFrame_Mail_IMAP 
{
    private $_stream=null;
    private $_host=null;
    private $_port=null;
    private $_user=null;
    private $_password=null;
    private $_mailbox_name=null;
    private $_error=array();
    
    /**
     * Constructor
     * 
     * @return    void
     * @since    1.0
     */
    public function __construct($host, $port, $user, $password, $mailbox_name="INBOX") 
    {
        $this->_host = $host;
        $this->_port = $port;
        $this->_user = $user;
        $this->_password = $password;
        $this->_mailbox_name = $mailbox_name;
        
        // Build mailbox name
        $mbox = '{'.$this->_host.':'.$this->_port.'/novalidate-cert}'.$this->_mailbox_name;
        
          // Open mailbox stream
          $this->_stream = @imap_open($mbox, $this->_user, $this->_password);
          if ($this->_stream === false) {
              $this->_error[] = imap_last_error();
              return false;
          }
    }
    
    /**
     * This function gets messages in the current mailbox.
     * 
     * @return    array    An array of message objects.
     */
    public function getMessages() 
    {
          if (!$this->_stream) {
              return false;
          }
        
          // Get mailbox info
        $mbox_check = imap_check($this->_stream);
        if ($mbox_check === false) {
            return false;
        }
        
        // Get overview for messages in mailbox
        $messages = imap_fetch_overview($this->_stream, "1:".$mbox_check->Nmsgs, 0);
        foreach ($messages as $overview) {
            $body_structure = imap_fetchstructure($this->_stream, $overview->msgno);
            $parts = $this->_createPartArray($body_structure);
            //var_dump($parts);
            for ($i=0; $i<count($parts); $i++) {
                if ($parts[$i]['part_object']->subtype == 'PLAIN' || $parts[$i]['part_object']->subtype == 'HTML') {
                    $key = strtoupper($parts[$i]['part_object']->subtype);
                    $overview->body[$key] .= imap_fetchbody($this->_stream, $overview->msgno, $parts[$i]['part_number']);
                }
                else {
                    $attachment_info = array();
                    $attachment_info['filename'] = $parts[$i]['part_object']->dparameters[0]->value;
                    $attachment_info['disposition'] = $parts[$i]['part_object']->disposition;
                    $attachment_info['bytes'] = $parts[$i]['part_object']->bytes;
                    $attachment_info['subtype'] = $parts[$i]['part_object']->subtype;
                    $overview->body['ATTACHMENTS'][] = $attachment_info;
                    unset($attachment_info);
                }
                
            }
        }
        
        return $messages;
    }
    
    /**
     * This function deletes a message from the current mailbox
     *
     * @param int $uid Can contain a list of ids separated by commas
     */
    function deleteMessage($uid) 
    {
        if (!empty($uid)) {
            imap_delete($this->_stream, $uid, FT_UID);
        }
    }
    
    function expunge() 
    {
        @imap_expunge($this->_stream);
    }
    
    /**
     * Close the current IMAP connection.
     * 
     * @return    void
     */
    public function close() 
    {
        // Close IMAP stream
        imap_close($this->_stream);
    }
    
    private function _createPartArray($structure, $prefix="") 
    {
        if (sizeof($structure->parts) > 0) {
            foreach ($structure->parts as $count => $part) {
                $this->_addPartToArray($part, $prefix.($count+1), $part_array);
            }
        }
        else {
            // Email does not have a seperate mime attachment for text
            $part_array[] = array('part_number' => $prefix.'1', 'part_object' => $structure);
        }
        
        return $part_array;
    }
    
    private function _addPartToArray($obj, $partno, &$part_array) 
    {
        $part_array[] = array('part_number' => $partno, 'part_object' => $obj);
        // Check to see if the part is an attached email message, as in the RFC-822 type
        if ($obj->type == 2) {
            // Check to see if the email has parts
            if (sizeof($obj->parts) > 0) {
                // Iterate here again to compensate for the broken way that imap_fetchbody() handles attachments
                foreach ($obj->parts as $count => $part) {
                    if (sizeof($part->parts) > 0) {
                        foreach ($part->parts as $count2 => $part2) {
                            $this->_addPartToArray($part2, $partno.".".($count2+1), $part_array);
                        }
                    }
                    // Attached email does not have a seperate mime attachment for text
                    else {
                        $part_array[] = array('part_number' => $partno.'.'.($count+1), 'part_object' => $obj);
                    }
                }
            }
            // Not sure if this is possible
            else {
                $part_array[] = array('part_number' => $prefix.'.1', 'part_object' => $obj);
            }
        }
        // If there are more sub-parts, expand them out.
        else {
            if (sizeof($obj->parts) > 0) {
                foreach ($obj->parts as $count => $p) {
                    $this->_addPartToArray($p, $partno.".".($count+1), $part_array);
                }
            }
        }
    }
}
