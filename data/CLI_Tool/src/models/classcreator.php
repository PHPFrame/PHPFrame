<?php
/**
 * data/CLITool/src/models/classcreator.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   PHPFrame_CLITool
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * This class is used to create new classes based on provided class templates.
 *
 * @category PHPFrame
 * @package  PHPFrame_CLITool
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class ClassCreator
{
    private $_tmpl_path;

    /**
     * Constructor.
     *
     * @param string $tmpl_path Absolute path to directory containings class
     *                          templates.
     *
     * @return void
     * @since  1.0
     */
    public function __construct($tmpl_path)
    {
        if (!is_dir($tmpl_path)) {
            $msg  = "Template path passed to ".get_class($this)." is not ";
            $msg .= "valid. Directory '".$tmpl_path."' doesn't exist or is ";
            $msg .= "not readable.";
            throw new InvalidArgumentException($msg);
        }

        $this->_tmpl_path = $tmpl_path;
    }

    /**
     * Create class code.
     *
     * @param string $tmpl    The template name.
     * @param array  $replace [Optional] Associative array where keys are
     *                        patterns and values are replacements.
     *
     * @return string
     * @since  1.0
     */
    public function create($tmpl, array $replace=null)
    {
        $tmpl = $this->_tmpl_path.DS.$tmpl.".php";
        if (!is_file($tmpl)) {
            $msg = "Template file not found.";
            throw new RuntimeException($msg);
        }

        if (!is_null($replace)) {
            $array_obj = new PHPFrame_Array($replace);
            if (!$array_obj->isAssoc()) {
                $msg  = "Argument 'replace' passed to ".get_class($this)."::";
                $msg .= __FUNCTION__."() must be an asociative array.";
                throw new InvalidArgumentException($msg);
            }
        }

        $class = file_get_contents($tmpl);

        if (is_array($replace) && count($replace) > 0) {
            $patterns     = array();
            $replacements = array();

            foreach ($replace as $key=>$value) {
                $patterns[]     = "/".$key."/s";
                $replacements[] = $value;
            }

            $class = preg_replace($patterns, $replacements, $class);
        }

        return $class;
    }
}
