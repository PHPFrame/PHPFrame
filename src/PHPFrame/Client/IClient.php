<?php
/**
 * PHPFrame/Client/IClient.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Client
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Client Interface
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Client
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
interface PHPFrame_Client_IClient
{
    /**
     * Check if this is the correct helper for the client being used and returns instance if so
     * 
     * @static
     * @access    public
     * @return    PHPFrame_Client_IClient|boolean
     * @since    1.0
     */
    public static function detect();
    
    /**    
     * Populate a Unified Request Array to return
     * 
     * @access    public
     * @return    array     Unified Request Array
     */
    public function populateURA();
    
    /**    
     * Get helper name
     * 
     * @access    public
     * @return    string     name to identify helper type
     */
    public function getName();
    
    /**
     * Pre action hook
     * 
     * This method is invoked by the front controller before invoking the requested
     * action in the action controller. It gives the client an opportunity to do 
     * something before the component is executed.
     * 
     * @return    void
     */
    public function preActionHook();
    
    /**
     * Render component view
     * 
     * This method is invoked by the views and renders the ouput data in the format specified
     * by the client.
     * 
     * @param    array    $data    An array containing the data assigned to the view.
     * @return    void
     */
    public function renderView($data);
    
    /**
     * Render overall template
     *
     * @param    string    &$str    A string containing the component output.
     * @return    void
     */
    public function renderTemplate(&$str);
}
