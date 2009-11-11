<?php
/**
 * PHPFrame/FileSystem/FileObj.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   FileSystem
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * File Object Class
 * 
 * @category PHPFrame
 * @package  FileSystem
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      SplFileObject
 * @since    1.0
 */
class PHPFrame_FileObject extends SplFileObject
{
    /**
     * 
     * @param string   $file_name
     * @param string   $open_mode        [Optional]
     * @param bool     $use_include_path [Optional]
     * @param resource $context          [Optional] A stream context
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(
        $file_name, 
        $open_mode="r", 
        $use_include_path=false, 
        $context=null
    )
    {
        // Do not pass null context to parent as it would fail because context 
        // is of type resource
        if (is_null($context)) {
            parent::__construct($file_name, $open_mode, $use_include_path);
        } else {
            parent::__construct(
                $file_name, 
                $open_mode, 
                $use_include_path, 
                $context
            );
        }
    }
    
    /**
     * Get file contents as string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getFileContents()
    {
        return implode("\n", iterator_to_array($this));
    }
    
    /**
     * Get FileInfo object
     * 
     * @param string $class_name [Optional]
     * 
     * @access public
     * @return SplFileInfo
     * @since  1.0
     */
    public function getFileInfo($class_name="PHPFrame_FileInfo")
    {
        return new $class_name($this->getRealPath());
    }
}
