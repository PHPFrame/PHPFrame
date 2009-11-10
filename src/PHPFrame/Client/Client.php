<?php
/**
 * PHPFrame/Client/Client.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Client
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @ignore
 */

/**
 * Abstract Client Class
 * 
 * Implementing Client classes represent the different "client" applications 
 * that will interact with our app.
 * 
 * PHPFrame 1.0 includes 4 implementations of this interface:
 * 
 * - CLI (Command Line Interface)
 * - Default (Normal web browsers)
 * - Mobile (Mobile phones)
 * - XMLRPC (XMLRPC API consumers)
 * 
 * @category PHPFrame
 * @package  Client
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @ignore
 */
abstract class PHPFrame_Client
{
    /**
     * Check if this is the correct helper for the client being used and 
     * returns instance if so.
     * 
     * @static
     * @access public
     * @return PHPFrame_Client|boolean
     * @since  1.0
     */
    abstract public static function detect();
    
    /**    
     * Get client name
     * 
     * @access public
     * @return string Name to identify helper type
     * @since  1.0
     */
    abstract public function getName();
    
    /**    
     * Populate a Unified Request Array to return
     * 
     * @access public
     * @return array  Unified Request Array
     * @since  1.0
     */
    abstract public function populateRequest(PHPFrame_Request $request);
    
    /**
     * Prepare response
     * 
     * This method is invoked by the front controller before invoking the 
     * requested action in the action controller. It gives the client an 
     * opportunity to do something before the component is executed.
     * 
     * The implementing Client classes will need to make sure the set the 
     * response content to the right document type. See PHPFrame_DefaultClient 
     * for an example. 
     * 
     * @param PHPFrame_Response $response The response object to prepare.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    abstract public function prepareResponse(PHPFrame_Response $response);
    
    /**
     * Handle controller redirection
     * 
     * @param string $url
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function redirect($url)
    {
        $url = trim((string) $url);
        
        if ($url) {
            $url = PHPFrame_URLRewriter::rewriteURL($url);
            header("Location: ".$url);
            exit;
        }
    }
}
