<?php
/**
 * PHPFrame/Utils/InputFilter.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Utils
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * InputFilter class.
 *
 * @category PHPFrame
 * @package  Utils
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_InputFilter
{
    /**
     * Array with tags to blacklist.
     *
     * @var array
     */
    private $_tag_blacklist  = array(
        'applet', 'body', 'bgsound', 'base', 'basefont', 'embed', 'frame',
        'frameset', 'head', 'html', 'id', 'iframe', 'ilayer', 'layer', 'link',
        'meta', 'name', 'object', 'script', 'style', 'title', 'xml'
    );
    /**
     * Array with attributes to blacklist.
     *
     * @var array
     */
    private $_attr_blacklist = array(
        'action', 'background', 'codebase', 'dynsrc', 'lowsrc'
    );

    // should also strip ALL event handlers

    /**
     * Constructor.
     *
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        //...
    }

    /**
     * Process/filter a value.
     *
     * @param string|array $mixed The value to filter.
     *
     * @return string|array
     * @since  1.0
     */
    public function process($mixed)
    {
        if (is_array($mixed)) {
            return $this->_processArray($mixed);
        }

        return $this->_processString($mixed);
    }

    /**
     * Process input array.
     *
     * @param array $array The array containing the data to filter.
     *
     * @return array
     * @since  1.0
     */
    private function _processArray(array $array)
    {
        $filtered = array();
        foreach ($array as $key=>$value) {
            if (is_array($value)) {
                $filtered[$key] = $this->_processArray($value);
            } elseif (!is_object($value) && !is_resource($value)) {
                $filtered[$key] = $this->_processString($value);
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Process input string.
     *
     * @param string $str The str to filter.
     *
     * @return string
     * @since  1.0
     */
    private function _processString($str)
    {
        $str = urldecode($str);

        $patterns = array();
        $replacements = array();

        foreach ($this->_tag_blacklist as $tag) {
            $patterns[] = "/<".$tag.".*(\/>|<\/".$tag.">)/is";
            $replacements[] = "";
        }

        foreach ($this->_attr_blacklist as $attr) {
            $patterns[] = "/\s?$attr=['\"]?[^\s'\">]+['\"]?/is";
            $replacements[] = "";
        }

        $patterns[] = "/<(\?|%)(php)?.*(\?|%)>/is";
        $replacements[] = "";

        return trim(preg_replace($patterns, $replacements, $str));
    }
}
