<?php
/**
 * PHPFrame/MVC/ViewHelper.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   MVC
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * View helper...
 * 
 * @category PHPFrame
 * @package  MVC
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @see      PHPFrame_ActionController
 * @since    1.0
 */
abstract class PHPFrame_ViewHelper
{
    /**
     * Reference to the application object.
     * 
     * @var PHPFrame_Application
     */
    private $_app = null;
    
    /**
     * Constructor
     * 
     * @param string $app Reference to the application object.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        $this->_app = $app;
    }
    
    public function app()
    {
    	return $this->_app;
    }
}
