<?php
/**
 * PHPFrame/Document/PlainDocument.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Document
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * XML Document Class
 *
 * @category PHPFrame
 * @package  Document
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_PlainDocument extends PHPFrame_Document
{
    /**
     * Constructor.
     *
     * @param string $mime    [Optional] The document's MIME type. The default
     *                        value is 'text/plain'.
     * @param string $charset [Optional] The document's character set. Default
     *                        value is 'UTF-8'.
     *
     * @return void
     * @since  1.0
     */
    public function __construct($mime="text/plain", $charset=null)
    {
        // Call parent's constructor to set mime type
        parent::__construct($mime, $charset);
    }

    /**
     * Convert object to string
     *
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str = "";

        $title = $this->title();
        if (!empty($title)) {
            $str .= $title."\n";
            for ($i=0; $i<strlen($title); $i++) {
                $str .= "=";
            }
            $str .= "\n\n";
        }

        // Prepend sysevents if applicable
        $sysevents = PHPFrame::getSession()->getSysevents();
        if ($sysevents instanceof PHPFrame_Sysevents) {
            if (count($sysevents) > 0) {
                $str .= (string) $sysevents;
                $str .= "\n";
            }

            $sysevents->clear();
        }

        $body = $this->body();
        if (!empty($body)) {
            $str .= $body."\n";
        }

        return $str;
    }
}
