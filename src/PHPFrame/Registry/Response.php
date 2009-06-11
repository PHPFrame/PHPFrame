<?php
/**
 * PHPFrame/Registry/Response.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Registry
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
 * @subpackage Registry
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Registry_Response
{
    private $_header=null;
    private $_body=null;
    
    function setHeader($str) 
    {
        $this->_header = $str;
    }
    
    function setBody($str) 
    {
        $this->_body = $str;
    }
    
    function send() 
    {
        echo $this->_body;
        
        if (config::DEBUG) {
            echo '<pre>'.PHPFrame_Debug_Profiler::getReport().'</pre>';
        }
    }
}
