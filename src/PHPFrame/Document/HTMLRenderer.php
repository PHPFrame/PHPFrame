<?php
/**
 * PHPFrame/Document/HTMLRenderer.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Document
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * HTML Renderer Class
 *
 * @category PHPFrame
 * @package  Document
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Renderer
 * @since    1.0
 */
class PHPFrame_HTMLRenderer extends PHPFrame_Renderer
{
    /**
     * Full path to directory with HTML view files.
     *
     * @var string
     */
    private $_views_path;

    /**
     * Constructor.
     *
     * @param string $views_path Full path to directory with HTML view files.
     *
     * @return void
     * @since  1.0
     */
    public function __construct($views_path)
    {
        $this->_views_path = (string) $views_path;
    }

    /**
     * Render a given value.
     *
     * @param mixed $value The value we want to render.
     *
     * @return void
     * @since  1.0
     * @see    PHPFrame/Document/PHPFrame_Renderer#render($value)
     */
    public function render($value)
    {
        $str = $value;

        if ($value instanceof PHPFrame_View) {
            $str = $this->renderView($value);
        } elseif ($value instanceof Exception) {
            $str = $this->renderException($value);
        }

        return (string) $str;
    }

    /**
     * Render view and store in document's body
     *
     * @param PHPFrame_View $view The view object to process.
     *
     * @return void
     * @since  1.0
     */
    public function renderView(PHPFrame_View $view)
    {
        $tmpl_path = $this->_views_path.DS.$view->getName().".php";

        if (is_file($tmpl_path)) {
            // Start buffering
            ob_start();
            // set view data as local array
            $data = $view->getData();

            if (is_array($data) && count($data) > 0) {
                foreach ($data as $key=>$value) {
                    $$key = $value;
                }
            }

            // Include template file
            include_once $tmpl_path;
            // save buffer in body property
            $str = ob_get_contents();
            // clean output buffer
            ob_end_clean();
        } else {
            $msg = "Layout template file ".$tmpl_path." not found.";
            throw new RuntimeException($msg);
        }

        return $str;
    }

    /**
     * Render a partial view
     *
     * @param string $name The partial name.
     * @param array  $data An associative array with data to be passed to the
     *                     partial view.
     *
     * @return void
     * @since  1.0
     */
    public function renderPartial($name, array $data=null)
    {
        $name = trim((string) $name);
        $path = $this->_views_path.DS."partials".DS.$name;

        if (!is_file($path)) {
            $path .= ".php";
            if (!is_file($path)) {
                return;
            }
        }

        // Start buffering
        ob_start();

        if (is_array($data) && count($data) > 0) {
            foreach ($data as $key=>$value) {
                $$key = $value;
            }
        }

        // Include partial file
        include $path;
        // save buffer in body property
        $partial = ob_get_contents();
        // clean output buffer
        ob_end_clean();

        return $partial;
    }

    /**
     * Render exception objects to HTML
     *
     * @param Exception $e Instance of Exception
     *
     * @return string
     * @since  1.0
     */
    public function renderException(Exception $e)
    {
        $tmpl_path = $this->_views_path.DS."error.php";

        if (is_file($tmpl_path)) {
            $error = $this->exceptionToArray($e);
            $view = new PHPFrame_View(
                "error",
                array("error" => $error["error"])
            );

            return $this->renderView($view);
        }

        $str  = "<pre>\n";
        $str .= "Uncaught ".get_class($e).": ";
        $str .= $e->getMessage()."\n";
        $str .= "File: ".$e->getFile()."\n";
        $str .= "Line: ".$e->getLine()."\n";
        $str .= "Code: ".$e->getCode()."\n";
        $str .= $e->getTraceAsString();
        $str .= "</pre>\n";

        return $str;
    }
}
