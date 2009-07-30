<?php
/**
 * PHPFrame/Exception/XMLRPC.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Exception
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Filesystem.php 37 2009-06-11 21:00:01Z luis.montero@e-noise.com $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * XMLRPC Exception Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Exception
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Exception_XMLRPC extends PHPFrame_Exception
{
	
	private $_faultCode;
	
	const INVALID_COMPONENT = 1;
	const INVALID_ACTION = 2;
	const INVALID_NUMBER_PARAMETERS = 3;
	const INVALID_PARAMETER_TYPE = 4;
	const INVALID_API_KEY_OR_USER = 5;
	const INVALID_PERMISSIONS = 6;
	
	/**
     * Constructor
     * 
     * @param string       $message   The error message.
     * @param int          $code      The error code.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(
        $message=null,
        $xmlrpcFaultCode,
        $code=PHPFrame_Exception::ERROR
    ) {
       parent::__construct($message, $code);
       $_faultCode = $xmlrpcFaultCode;
    }
    
    /**
     * Gets this exception formatted in xml as an XML-RPC fault message.
     * 
     * @return string the xml-rpc formatted fault message
     */
    public function getXMLRPCFault()
    {
    	$fault = '<?xml version="1.0" encoding="UTF-8"?>
    			<methodResponse>
				   <fault>
				      <value>
				         <struct>
				            <member>
				               <name>faultCode</name>
				               <value><int>'.$this->_faultCode.'</int></value>
				               </member>
				            <member>
				               <name>faultString</name>
				               <value><string>'.$this->getMessage().'</string></value>
				               </member>
				            </struct>
				         </value>
				      </fault>
				   </methodResponse>';
    	
    	return $fault;
    }
}
