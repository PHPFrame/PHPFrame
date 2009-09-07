<?php
/**
 * PHPFrame/FS/FileObj.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   FS
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id: Logger.php 320 2009-07-28 17:28:32Z luis.montero@e-noise.com $
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * File Object Class
 * 
 * @category PHPFrame
 * @package  FS
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      SplFileObject
 * @since    1.0
 */
class PHPFrame_FileObject extends SplFileObject
{
    public function __construct(
        $file_name, 
        $open_mode="r", 
        $use_include_path=false, 
        $context=null
    ) {
        // Do not pass null context to parent as it would fail because context is
        // of type resource
        if (is_null($context)) {
            parent::__construct($file_name, $open_mode, $use_include_path);
        } else {
            parent::__construct($file_name, $open_mode, $use_include_path, $context);
        }
    }
    
    public function getFileInfo($class_name="PHPFrame_FileInfo")
    {
        return new $class_name($this->getRealPath());
    }
}
