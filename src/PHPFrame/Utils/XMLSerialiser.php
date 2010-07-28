<?php
/**
 * PHPFrame/Utils/XMLSerialiser.php
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
 * XMLSerialiser
 *
 * @category PHPFrame
 * @package  Utils
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_XMLSerialiser
{
    /**
     * Serialise a value to XML
     *
     * @param mixed  $value          The value we want to serialise to XML.
     * @param string $root_node_name [Optional]
     *
     * @return string
     * @since  1.0
     */
    public static function serialise($value, $root_node_name="root")
    {
        // Build serialised string
        $str = self::_doSerialise($value);

        if ($root_node_name) {
            $str = "<".$root_node_name.">".$str."</".$root_node_name.">";
        }

        // Get instance of beautifier to make string look pretty ;-)
        $xml_beautifier = new XML_Beautifier();

        // Return beautified string
        return $xml_beautifier->formatString($str);
    }

    /**
     * Unserialise a string as a PHP value.
     *
     * @param string $str The XML string we want to unserialise.
     *
     * @return string
     * @since  1.0
     */
    public static function unserialise($str)
    {
        return self::_unserialiseNode(new SimpleXMLElement($str));
    }

    /**
     * Serialise value.
     *
     * @param mixed $value The value we want to serialise to XML.
     *
     * @return string
     * @since  1.0
     */
    private function _doSerialise($value)
    {
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        } elseif (is_object($value)) {
            $value = get_object_vars($value);
        }

        if (is_array($value)) {
            return self::_serialiseArray($value);
        }

        if (is_null($value)) {
            return "NULL";
        }

        return htmlentities((string) $value);
    }

    /**
     * Serialise array.
     *
     * @param array $array The array.
     *
     * @return string
     * @since  1.0
     */
    private static function _serialiseArray(array $array)
    {
        $str = "";

        $array_obj = new PHPFrame_Array($array);

        if ($array_obj->isAssoc()
            && count($array_obj) == 1
            && is_array(end($array))
        ) {
            $key   = end(array_keys($array));
            $child = end($array);

            if (is_array($child)) {
                $child_obj = new PHPFrame_Array($child);
                if (!$child_obj->isAssoc()) {
                    foreach ($child as $grand_child) {
                        $str .= self::_doSerialise(array($key=>$grand_child));
                    }
                } else {
                    $key = preg_replace("/\//", "-", $key);
                    $str = "<".$key.">";
                    $str .= self::_doSerialise($child);
                    $str .= "</".$key.">\n";
                }

            } else {
                $str .= self::_doSerialise($array);
            }

        } elseif ($array_obj->isAssoc()) {
            foreach ($array_obj as $key=>$value) {
                $key = preg_replace("/\//", "-", $key);
                $str .= "<".$key.">";
                $str .= self::_doSerialise($value);
                $str .= "</".$key.">\n";
            }

        } else {
            foreach ($array_obj as $value) {
                $str .= "<array>";
                $str .= self::_doSerialise($value);
                $str .= "</array>\n";
            }
        }

        return $str;
    }

    /**
     * Unserialise SimpleXMLElement node.
     *
     * @param SimpleXMLElement $node The node.
     *
     * @return string|null
     * @since  1.0
     */
    private static function _unserialiseNode(SimpleXMLElement $node)
    {
        $children = $node->children();

        if (count($children) > 0) {
            $value = array();
            foreach ($children as $child) {
                $child_name = $child->getName();
                if ($child_name == "array") {
                    $value[] = self::_unserialiseNode($child);
                } else {
                    if (array_key_exists($child_name, $value)) {
                        if (!is_array($value[$child_name])) {
                            $value[$child_name] = array($value[$child_name]);
                        }
                        $array_obj = new PHPFrame_Array($value[$child_name]);
                        if ($array_obj->isAssoc()) {
                            $value[$child_name] = array($value[$child_name]);
                        }
                        $value[$child_name][] = self::_unserialiseNode($child);
                    } else {
                        $value[$child_name] = self::_unserialiseNode($child);
                    }
                }
            }
        } else {
            $value = (string) $node;
        }

        if ($value == "NULL") {
            return null;
        }

        return $value;
    }
}
