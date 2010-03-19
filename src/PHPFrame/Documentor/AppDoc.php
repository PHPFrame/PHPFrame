<?php
/**
 * PHPFrame/Documentor/AppDoc.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Documentor
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 * @ignore
 */

/**
 * Application Documentor Class
 *
 * @category PHPFrame
 * @package  Documentor
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_AppDoc implements IteratorAggregate
{
    private $_controllers = array();

    /**
     * Constructor.
     *
     * @param string $install_dir Absolute path to installation directory of
     *                            application we want to build the docs for.
     *
     * @return void
     * @since  1.0
     */
    public function __construct($install_dir)
    {
        $controllers_dir = $install_dir.DS."src".DS."controllers";
        $dir_iterator    = new RecursiveDirectoryIterator($controllers_dir);
        $it_iterator     = new RecursiveIteratorIterator($dir_iterator);

        foreach ($it_iterator as $file) {
            if ((end(explode(".", $file))) == "php") {
                $controller_name = substr(
                    $file->getFileName(),
                    0,
                    strpos($file->getFileName(), ".")
                );

                $controller_doc = new PHPFrame_ControllerDoc(
                    ucfirst($controller_name)."Controller"
                );

                $this->_controllers[$controller_name] = $controller_doc;
            }
        }
    }

    /**
     * Convert object to string.
     *
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str = "Controllers:\n\n";

        foreach ($this->_controllers as $key=>$controller) {
            $str .= $key."\n";
            for ($i=0; $i<strlen($key); $i++) {
                $str .= "-";
            }

            $str .= "\n".$controller."\n\n";
        }

        return $str;
    }

    /**
     * Implementation of the IteratorAggregate interface.
     *
     * @return ArrayIterator
     * @since  1.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_controllers);
    }
}
