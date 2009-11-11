<?php
/**
 * PHPFrame/Exception/XMLRPCException.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Exception
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * XMLRPC Exception Class
 * 
 * @category PHPFrame
 * @package  Exception
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_XMLRPCException extends RuntimeException
{
    private $_fault_code;
    
    const INVALID_COMPONENT         = 1;
    const INVALID_ACTION            = 2;
    const INVALID_NUMBER_PARAMETERS = 3;
    const INVALID_PARAMETER_TYPE    = 4;
    const INVALID_API_KEY_OR_USER   = 5;
    const INVALID_PERMISSIONS       = 6;
    
    /**
     * Constructor
     * 
     * @param string $message         The error message.
     * @param int    $xmlrpcFaultCode The XMLRPC fault code.
     * @param int    $code            The error code if any.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($message=null, $xmlrpcFaultCode, $code=null)
    {
       parent::__construct($message, $code);
       
       $this->_fault_code = $xmlrpcFaultCode;
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
                               <value><int>'.$this->_fault_code.'</int></value>
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
