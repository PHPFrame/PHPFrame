<?php
/**
 * PHPFrame/Mail/IMAP.php
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
 * Throw exception if IMAP extension is not loaded.
 */
if (!function_exists("imap_open")) {
    $msg = "PHP's IMAP extension not loaded!!";
    throw new RuntimeException($msg);
}

/**
 * The IMAP Class is used to connect to IMAP servers, read email and manage
 * mailboxes.
 *
 * @category PHPFrame
 * @package  Mail
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_IMAP
{
    /**
     * IMAP stream.
     *
     * @var resource
     */
    private $_stream;
    /**
     * IMAP host name or IP address.
     *
     * @var string
     */
    private $_host;
    /**
     * IMAP server port.
     *
     * @var int
     */
    private $_port;
    /**
     * IMAP username
     *
     * @var string
     */
    private $_user;
    /**
     * IMAP password.
     *
     * @var string
     */
    private $_password;
    /**
     * Mail box name.
     *
     * @var string
     */
    private $_mbox;
    /**
     * Mail box info.
     *
     * @var array
     */
    private $_mbox_info;

    /**
     * Constructor.
     *
     * @param string $host     Host name or IP address of IMAP host.
     * @param string $user     IMAP username.
     * @param string $password Password for IMAP account.
     * @param string $mailbox  [Optional] Default value is 'INBOX'.
     * @param int    $port     [Optional] Default value is '143'.
     *
     * @return void
     * @throws RuntimeException On connection failure.
     * @since  1.0
     */
    public function __construct(
        $host,
        $user,
        $password,
        $mailbox="INBOX",
        $port=143
    ) {
        $this->_host     = (string) $host;
        $this->_user     = (string) $user;
        $this->_password = (string) $password;
        $this->_mbox     = (string) $mailbox;
        $this->_port     = (int) $port;

        // Open mailbox stream
        $this->_connect();
    }

    /**
     * Magic method invoked when an instance is serialized.
     *
     * @return array
     * @since  1.0
     */
    public function __sleep()
    {
        $this->close();

        return array_keys(get_object_vars($this));
    }

    /**
     * Magic method invoked when a new instance is unserialized.
     *
     * @return void
     * @since  1.0
     */
    public function __wakeup()
    {
        $this->_connect();
    }

    /**
     * Connect to IMAP server.
     *
     * @return void
     * @throws RuntimeException
     * @since  1.0
     */
    private function _connect()
    {
        // Build mailbox name
        $mbox  = '{'.$this->_host.':'.$this->_port.'/novalidate-cert}';
        $mbox .= $this->_mbox;

        $this->_stream = @imap_open($mbox, $this->_user, $this->_password);

        if ($this->_stream === false) {
            throw new RuntimeException(imap_last_error());
        }
    }

    /**
     * Reconnect to server if connection is lost.
     *
     * @param bool $force [Optional] Default value is FALSE, in this case the
     *                    connection will only be re-established is needed. To
     *                    force a new connection set to TRUE.
     *
     * @return void
     * @throws RuntimeException
     * @since  1.0
     */
    public function reconnect($force=false)
    {
        $force = (bool) $force;

        if ($force) {
            $this->close();
        }

        if (is_null($this->_stream) || !imap_ping($this->_stream)) {
            $this->_connect();
        }
    }

    /**
     * Get mailbox info.
     *
     * @return array Returns an array with the folling keys:
     *               - date (current system time formatted according to RFC2822)
     *               - driver (protocol used: pop3, imap or nntp)
     *               - mailbox (the mailbox name)
     *               - nmsgs (number of messages in the mailbox)
     *               - recent (number of recent messages in the mailbox)
     * @since  1.0
     */
    public function getMailboxInfo()
    {
        if (is_null($this->_mbox_info)) {
            $mbox_check = imap_check($this->_stream);

            if ($mbox_check === false) {
                throw new RuntimeException(imap_last_error());
            }

            $this->_mbox_info["date"]    = $mbox_check->Date;
            $this->_mbox_info["driver"]  = $mbox_check->Driver;
            $this->_mbox_info["mailbox"] = $mbox_check->Mailbox;
            $this->_mbox_info["nmsgs"]   = $mbox_check->Nmsgs;
            $this->_mbox_info["recent"]  = $mbox_check->Recent;
        }

        return $this->_mbox_info;
    }

    /**
     * Get messages in the current mailbox.
     *
     * @return array An array of message objects.
     * @since  1.0
     */
    public function getMessages()
    {
        // This will only try to reconnect if needed.
        $this->reconnect();

        $mbox_info = $this->getMailboxInfo();

        // Get overview for messages in mailbox
        $messages = imap_fetch_overview(
            $this->_stream,
            "1:".$mbox_info["nmsgs"],
            0
        );

        foreach ($messages as $overview) {
            $body = imap_fetchstructure($this->_stream, $overview->msgno);

            $parts = $this->_createPartArray($body);
            //var_dump($parts);
            for ($i=0; $i<count($parts); $i++) {
                if ($parts[$i]['part_object']->subtype == 'PLAIN'
                    || $parts[$i]['part_object']->subtype == 'HTML'
                ) {
                    $key = strtoupper($parts[$i]['part_object']->subtype);

                    // We declare an empty body if not set yet
                    if (!isset($overview->body[$key])) {
                        $overview->body = array($key=>"");
                    }

                    $overview->body[$key] .= imap_fetchbody(
                        $this->_stream,
                        $overview->msgno,
                        $parts[$i]['part_number']
                    );

                } else {
                    $part_object = $parts[$i]['part_object'];

                    // Store attachment info in body
                    $overview->body['ATTACHMENTS'][] = array(
                        "filename"    => $part_object->dparameters[0]->value,
                        "disposition" => $part_object->disposition,
                        "bytes"       => $part_object->bytes,
                        "subtype"     => $part_object->subtype
                    );
                }
            }
        }

        return $messages;
    }

    /**
     * This function deletes a message from the current mailbox.
     *
     * @param int $uid Can contain a list of ids separated by commas.
     *
     * @return void
     * @since  1.0
     */
    function deleteMessage($uid)
    {
        if (!empty($uid)) {
            imap_delete($this->_stream, $uid, FT_UID);
        }
    }

    /**
     * Expunge mailbox.
     *
     * @return void
     * @since  1.0
     */
    function expunge()
    {
        @imap_expunge($this->_stream);
    }

    /**
     * Close the current IMAP connection.
     *
     * @return void
     * @since  1.0
     */
    public function close()
    {
        // Close IMAP stream
        @imap_close($this->_stream);
        $this->_stream = null;
    }

    /**
     * Create array with message parts.
     *
     * @param stdClass $structure An object returned from imap_fetchstructure().
     * @param string   $prefix    [Optional]
     *
     * @return array
     * @since  1.0
     */
    private function _createPartArray($structure, $prefix="")
    {
        if (isset($structure->parts) && count($structure->parts) > 0) {
            foreach ($structure->parts as $key=>$value) {
                $this->_addPartToArray($value, $prefix.($key+1), $parts_array);
            }
        } else {
            // Email does not have a seperate mime attachment for text
            $parts_array[] = array(
                "part_number" => $prefix."1",
                "part_object" => $structure
            );
        }

        return $parts_array;
    }

    /**
     * Add part object to array containing message parts.
     *
     * @param stdClass $obj          The part object.
     * @param string   $partno       The part number.
     * @param array    &$parts_array Parts array passed by reference.
     *
     * @return void
     * @since  1.0
     */
    private function _addPartToArray($obj, $partno, &$parts_array)
    {
        $parts_array[] = array('part_number'=>$partno, 'part_object'=>$obj);

        // Check to see if the part is an attached email message, as in RFC-822
        if ($obj->type == 2) {
            // Check to see if the email has parts
            if (count($obj->parts) > 0) {
                // Iterate here again to compensate for the broken way that
                // imap_fetchbody() handles attachments
                foreach ($obj->parts as $count => $part) {
                    if (count($part->parts) > 0) {
                        foreach ($part->parts as $count2 => $part2) {
                            $this->_addPartToArray(
                                $part2,
                                $partno.".".($count2+1),
                                $parts_array
                            );
                        }
                    } else {
                        // Attached email does not have a seperate mime
                        // attachment for text
                        $parts_array[] = array(
                            'part_number' => $partno.'.'.($count+1),
                            'part_object' => $obj
                        );
                    }
                }
            } else {
                // Not sure if this is possible
                $parts_array[] = array(
                    'part_number' => $prefix.'.1',
                    'part_object' => $obj
                );
            }
        } else {
            // If there are more sub-parts, expand them out.
            if (isset($obj->parts) && count($obj->parts) > 0) {
                foreach ($obj->parts as $count => $p) {
                    $this->_addPartToArray(
                        $p,
                        $partno.".".($count+1),
                        $parts_array
                    );
                }
            }
        }
    }
}
