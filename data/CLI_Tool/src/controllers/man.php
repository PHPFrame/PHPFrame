<?php
/**
 * data/CLITool/src/controllers/man.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   PHPFrame_CLITool
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * Manual controller.
 * 
 * @category PHPFrame
 * @package  PHPFrame_CLITool
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class ManController extends PHPFrame_ActionController
{
    public function __construct()
    {
        parent::__construct("index");
    }
    
    public function index()
    {
        $app_doc = new PHPFrame_AppDoc($this->app()->getInstallDir());
        
        $str  = PHPFrame::version()."\n\n";
        $str .= "Usage instructions\n\n------------------\n\n";
        $str .= "To use the command line tool you will need to specify at ";
        $str .= "least a controller,\nand normally also an action and a number";
        $str .= " of parameters. For example, to get\na configuration parameter";
        $str .= " we would use the 'get' action in the 'config'\ncontroller. ";
        $str .= "The get action takes a parameter named 'key'.\n\n";
        $str .= "phpframe config get key=db.enable\n\n";
        $str .= "The above command will show the value of db.enable as defined ";
        $str .= "in the config\nfile.\n\n";
        $str .= (string) $app_doc;
        
        $this->response()->setTitle($this->config()->get("app_name"));
        $this->response()->setBody($str);
    }
}