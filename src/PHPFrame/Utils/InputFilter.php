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
     * @param string $str The value to filter.
     *
     * @return void
     * @since  1.0
     */
    public function process($str)
    {
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
