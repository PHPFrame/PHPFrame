<?php
/**
 * PHPFrame/Base/String.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Base
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * This class provides objects used to represent strings in an Object Oriented
 * context.
 *
 * @category PHPFrame
 * @package  Base
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_String
{
    /**
     * Private propery used to store the string as a primitive value
     *
     * @var string
     */
    private $_str;

    /**
     * Constructor
     *
     * @param string $str The string the object will represent.
     *
     * @return void
     * @since  1.0
     */
    public function __construct($str)
    {
        $this->_str = trim((string) $str);
    }

    /**
     * Magic method called we try to use a string object as a string
     *
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        return $this->_str;
    }

    /**
     * Get string length
     *
     * @return int
     * @since  1.0
     */
    public function len()
    {
        return strlen($this->_str);
    }

    /**
     * Get string in upper case
     *
     * @return string
     * @since  1.0
     */
    public function upper()
    {
        return strtoupper($this->_str);
    }

    /**
     * Get string in lower case
     *
     * @return string
     * @since  1.0
     */
    public function lower()
    {
        return strtolower($this->_str);
    }

    /**
     * Get string with first character in upper case
     *
     * @return string
     * @since  1.0
     */
    public function upperFirst()
    {
        return ucfirst(strtolower($this->_str));
    }

    /**
     * Get string with first character in every word in upper case
     *
     * @return string
     * @since  1.0
     */
    public function upperWords()
    {
        return ucwords(strtolower($this->_str));
    }

    /**
     * Limit string to a set number of characters
     *
     * @param int  $max_chars         Number of characters we want to limit to.
     * @param bool $add_trailing_dots [Optional] Boolean to indicate whether we
     *                                want to add trailing dots or not. Default
     *                                is TRUE.
     *
     * @return string
     * @since  1.0
     */
    public function limitChars($max_chars, $add_trailing_dots=true)
    {
        $str = $this->_str;

        if (strlen($str) > $max_chars) {
            $str = substr($str, 0, $max_chars);
            if ($add_trailing_dots === true) {
                // Remove another 4 chars to replace with dots
                $str  = substr($str, 0, (strlen($str)-3));
                $str .= "...";
            }
        }

        return $str;
    }

    /**
     * Limit the number of words.
     *
     * @param int  $max_chars         Number of characters we want to limit to
     * @param bool $add_trailing_dots [Optional] Boolean to indicate whether we
     *                                want to add trailing dots or not. Default
     *                                is TRUE.
     *
     * @return string
     * @since  1.0
     */
    public function limitWords($max_chars, $add_trailing_dots=true)
    {
        $str = $this->_str;

        if (strlen($str) > $max_chars) {
            $str = substr($str, 0, $max_chars);
            $str = substr($str, 0, strrpos($str, " "));
            if ($add_trailing_dots === true) {
                $str .= " ...";
            }
        }

        return $str;
    }

    /**
     * This method is used to format the string into the given length.
     * If the string is longer than the specified length it is trimmed to fit.
     * If the string is shorter than the specified length it is padded with spaces
     * on the left side to fit length.
     *
     * @param int  $length            Length we want to format the string to.
     * @param bool $add_trailing_dots [Optional] Boolean to indicate whether we
     *                                want to add trailing dots or not. Default
     *                                is TRUE.
     *
     * @return string
     * @since  1.0
     */
    public function fixLength($length, $add_trailing_dots=true)
    {
        // Cast input params to strict types
        $length            = (int) $length;
        $add_trailing_dots = (bool) $add_trailing_dots;

        $str = $this->_str;

        if (strlen($str) > $length) {
            // Trim to fixed length
            $str = substr($str, 0, $length);
            // Add trailing dots if necessary
            if ($add_trailing_dots) {
                $str  = substr($str, 0, ($length-3));
                $str .= "...";
            }
        } else {
            // Add space padding
            for ($i=0; $i<=($length - strlen($str)); $i++) {
                $str .= " ";
            }
        }

        return $str;
    }
}
