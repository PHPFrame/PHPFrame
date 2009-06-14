<?php
/**
 * PHPFrame/Application/Response.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Response Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 * @abstract
 */
abstract class PHPFrame_Application_Response
{
    /**
     * Instance of itself
     * 
     * @var PHPFrame_Application_Response
     */
    private static $_instance=null;
    /**
     * HTTP Response code
     * 
     * @var int
     */
    private $_code=null;
    /**
     * An array containing the raw headers
     * 
     * @var array
     */
    private $_header=array();
    /**
     * The content
     * 
     * @var mixed
     */
    private $_content=null;
    
    /**
     * Constructor
     * 
     * @return void
     */
    private function __construct()
    {
        // ...
    }
    
    /**
     * Get instance
     * 
     * @return PHPFrame_Application_Response
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self;
        }
        
        return self::$_instance;
    }
    
    /**
     * Set header line
     * 
     * @param string $key   The header key
     * @param string $value The header value
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setHeader($key, $value) 
    {
        $this->_header[$key] = $value;
    }
    
    /**
     * Set response content.
     * 
     * @param PHPFrame_Document $document
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setContent(PHPFrame_Document $document) 
    {
        $this->_content = $document;
    }
    
    /**
     * Get response content
     * 
     * @return PHPFrame_Document
     */
    public function getContent()
    {
        return $this->_content;
    }
    
    public function setTitle($str)
    {
        $this->_content->setTitle($str);
    }
    
    /**
     * Send response back to client
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function send() 
    {
        foreach ($this->_header as $key=>$value) {
            header($key.": ".$value);
        }
		
        echo $this->_content;
        
        if (config::DEBUG) {
            echo '<pre>'.PHPFrame_Debug_Profiler::getReport().'</pre>';
        }
    }
}
