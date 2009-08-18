<?php
/**
 * PHPFrame/Addons/Theme.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Addons
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Addons Theme Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Addons
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Addons_Theme extends PHPFrame_Mapper_DomainObject
{
    protected $name="";
    protected $author="";
    protected $enabled=false;
    protected $version="";
     
    /**
     * Constructor
     * 
     * @param array $options
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        parent::__construct($options);
    }
    
    protected function _doToArray($array)
    {
        return $array;
    }
}
