<?php
/**
 * PHPFrame/SCM/SVN.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   SCM
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * SVN Class
 * 
 * @category PHPFrame
 * @package  SCM
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_SCM_SVN implements PHPFrame_SCM
{
    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct() {}
    
    public function checkout($url, $path, $username=null, $password=null)
    {
        $cmd = "svn checkout ";
        
        if (!is_null($username)) {
            $cmd .= "--username ".$username." ";
        }
        
        $cmd .= $url." ".$path;
        
        $exec = new PHPFrame_Utils_Exec($cmd);
        var_dump($exec);
    }
    
    public function update($path)
    {
        $cmd = "cd ".$path." && svn update";
        
        $exec = new PHPFrame_Utils_Exec($cmd);
        var_dump($exec);
    }
    
    public function switchURL($url, $path)
    {
        
    }
    
    public function export($url, $path)
    {
        
    }
    
    public function commit()
    {
        
    }
}
