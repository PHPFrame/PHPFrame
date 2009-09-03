<?php
/**
 * PHPFrame/Client/IClient.php
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
 * Client Interface
 * 
 * Implementing Client classes represent the different "client" applications that
 * will interact with our app.
 * 
 * PHPFrame 1.0 includes 4 implementations of this interface:
 * 
 * - CLI
 * - Default
 * - Mobile
 * - XMLRPC
 * 
 * @category PHPFrame
 * @package  Client
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @ignore
 */
interface PHPFrame_Client_IClient
{
    /**
     * Check if this is the correct helper for the client being used and returns instance if so
     * 
     * @static
     * @access public
     * @return PHPFrame_Client_IClient|boolean
     * @since  1.0
     */
    public static function detect();
    
    /**    
     * Get client name
     * 
     * @access public
     * @return string Name to identify helper type
     * @since  1.0
     */
    public function getName();
    
    /**    
     * Populate a Unified Request Array to return
     * 
     * @access public
     * @return array  Unified Request Array
     * @since  1.0
     */
    public function populateRequest();
    
    /**
     * Prepare response
     * 
     * This method is invoked by the front controller before invoking the requested
     * action in the action controller. It gives the client an opportunity to do 
     * something before the component is executed.
     * 
     * The implementing Client classes will need to make sure the set the response 
     * content to the right document type. See PHPFrame_Client_Default for an example. 
     * 
     * @param PHPFrame_Application_Response $response The response object to prepare.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function prepareResponse(PHPFrame_Application_Response $response);
    
    public function redirect($url);
}
