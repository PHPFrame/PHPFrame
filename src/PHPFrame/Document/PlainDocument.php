<?php
/**
 * PHPFrame/Document/PlainDocument.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Document
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * XML Document Class
 * 
 * @category PHPFrame
 * @package  Document
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class PHPFrame_PlainDocument extends PHPFrame_Document
{
    /**
     * Constructor
     * 
     * @access public
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
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str = "";
        
        if ($this->getTitle()) {
            $str .= $this->getTitle()."\n";
            for ($i=0; $i<strlen($this->getTitle()); $i++) {
                $str .= "-";
            }
            $str .= "\n\n";
        }
        
        if (count(PHPFrame::getSession()->getSysevents()) > 0) {
            $str .= (string) PHPFrame::getSession()->getSysevents();
            $str .= "\n";
        }
        
        $str .= $this->getBody()."\n";
        
        return $str;
    }
}
