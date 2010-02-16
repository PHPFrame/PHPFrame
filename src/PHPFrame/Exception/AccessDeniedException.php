<?php
/**
 * PHPFrame/Exception/AccessDeniedException.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Exception
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Access Denied Exception Class
 * 
 * @category PHPFrame
 * @package  Exception
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_AccessDeniedException extends RuntimeException
{
    /**
     * Constructor.
     * 
     * @param string $msg [Optional] Error message.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct($msg="")
    {
        parent::__construct($msg, 401);
    }
}
