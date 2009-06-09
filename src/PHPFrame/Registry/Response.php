<?php
/**
 * @version       SVN: $Id$
 * @package       PHPFrame
 * @subpackage    registry
 * @copyright     2009 E-noise.com Limited
 * @license       http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Response Class
 * 
 * @package        PHPFrame
 * @subpackage     registry
 * @since         1.0
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
