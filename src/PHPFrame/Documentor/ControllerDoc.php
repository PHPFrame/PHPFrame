<?php
/**
 * PHPFrame/Documentor/ControllerDoc.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Documentor
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Controller Documentor Class
 * 
 * @category PHPFrame
 * @package  Documentor
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_ControllerDoc extends PHPFrame_ClassDoc
{
    public function __toString()
    {
        $str = "";
        
        $actions = $this->getOwnMethods();
        if (count($actions) > 0) {
            $str .= "Actions:";
            foreach ($actions as $action) {
                if ($action->getName() == "__construct") {
                    continue;
                }
                
                $str .= "\n".$action->getName();
                $str .= "(";
                $count = 0;
                foreach ($action->getParams() as $param) {
                    if ($count > 0) {
                        $str .= ", ";
                    }
                    if ($param->getType()) {
                        $str .= $param->getType()." ";
                    }
                    $str .= "$".$param->getName();
                    
                    $count++;
                }
                $str .= ")";
            }
        }
        
        return $str;
    }
}
