<?php
/**
 * PHPFrame/Utils/XMLSerialiser.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Utils
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * XMLSerialiser
 * 
 * @category PHPFrame
 * @package  Utils
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class PHPFrame_XMLSerialiser
{
    /**
     * Serialise a value to XML
     * 
     * @param mixed  $value
     * @param string $root_node_name [Optional]
     * 
     * @static
     * @access public
     * @return string
     * @since  1.0
     */
    public static function serialise(array $value, $root_node_name="root")
    {
        $str = self::_doSerialise($value);
        $str = "<".$root_node_name.">".$str."</".$root_node_name.">";
        $xml_beautifier = new XML_Beautifier();
        return $xml_beautifier->formatString($str);
    }
    
    /**
     * Unserialise a string as a PHP value
     * 
     * @param string $str
     * 
     * @static
     * @access public
     * @return string
     * @since  1.0
     */
    public static function unserialise($str)
    {
        return self::_unserialiseNode(new SimpleXMLElement($str));
    }
    
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
        
        return (string) $value;
    }
    
    private static function _serialiseArray(array $array)
    {
        $str = "";
        
        $array_obj = new PHPFrame_Array($array);
        
        if (
            $array_obj->isAssoc() 
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
                    $str = "<".$key.">";
                    $str .= self::_doSerialise($child);
                    $str .= "</".$key.">\n";
                }
            } else {
                $str .= self::_doSerialise($array);
            }
        } elseif ($array_obj->isAssoc()) {
            foreach ($array_obj as $key=>$value) {
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
    
    private static function _unserialiseNode(SimpleXMLElement $node)
    {
        $children = $node->children();
        
        if (count($children) > 0) {
            $value = array();
            foreach ($children as $child) {
                if ($child->getName() == "array") {
                    $value[] = self::_unserialiseNode($child);
                } else {
                    if (array_key_exists($child->getName(), $value)) {
                        if (!is_array($value[$child->getName()])) {
                            $value[$child->getName()] = array($value[$child->getName()]);
                        }
                        $array_obj = new PHPFrame_Array($value[$child->getName()]);
                        if ($array_obj->isAssoc()) {
                            $value[$child->getName()] = array($value[$child->getName()]);
                        }
                        $value[$child->getName()][] = self::_unserialiseNode($child);
                    } else {
                        $value[$child->getName()] = self::_unserialiseNode($child);
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
