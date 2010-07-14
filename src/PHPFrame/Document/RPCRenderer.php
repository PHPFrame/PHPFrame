<?php
/**
 * PHPFrame/Document/RPCRenderer.php
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
 * XML-RPC renderer class
 *
 * @category PHPFrame
 * @package  Document
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Renderer
 * @since    1.0
 */
class PHPFrame_RPCRenderer extends PHPFrame_Renderer
{
    /**
     * Reference to the document object his renderer will work with.
     *
     * @var PHPFrame_XMLDocument
     */
    private $_document;

    /**
     * Constructor.
     *
     * @param PHPFrame_XMLDocument $document Reference to the document object
     *                                       this renderer will work with.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_XMLDocument $document)
    {
        $this->_document = $document;
        $document->addNode("methodResponse", $document->dom());
    }

    /**
     * Render a given value.
     *
     * @param mixed $value The value we want to render.
     *
     * @return void
     * @since  1.0
     */
    public function render($value)
    {
        if ($value instanceof PHPFrame_View) {
            foreach ($value->getData() as $data) {
                if ($data instanceof PHPFrame_PersistentObject) {
                    $this->_makeParamPayload($data);
                }
            }
        } elseif ($value instanceof Exception) {
            $this->_makeFaultPayload($value->getCode(), $value->getMessage());

        } elseif ($value instanceof PHPFrame_Sysevents) {
            foreach ($value as $event) {
                if ($event[1] == 1) {
                    $this->_makeFaultPayload($value->statusCode(), $event[0]);
                    return;
                }
            }

            $this->_makeParamPayload($event[0]);

        } else {
            $this->_makeParamPayload($value);
        }
    }

    /**
     * Render view and store in document's body
     *
     * This method is invoked by the views and renders the ouput data in the
     * document specific format.
     *
     * @param PHPFrame_View $view The view object to process.
     *
     * @return void
     * @since  1.0
     */
    public function renderView(PHPFrame_View $view)
    {
        $sysevents = PHPFrame::getSession()->getSysevents();

        if (count($sysevents)) {
            $array           = iterator_to_array($sysevents);
            $last_event_msg  = $array[0][0];
            $last_event_type = $array[0][1];

            if ($last_event_type == PHPFrame_Subject::EVENT_TYPE_ERROR) {
                $this->_makeFaultPayload(5, $last_event_msg);
            } else {
                $view->addData('sysevents', $last_event_msg);
                $this->_makeParamPayload($view->getData());
            }
        } else {
            $this->_makeParamPayload($view->getData());
        }
    }

    /**
     * Make Param Payload
     *
     * This method is used to build an XML-RPC Param Response from an array
     *
     * @param mixed $param_value The parameter value as a scalar value or array.
     *
     * @return string An XML-RPC methodResponse structure with Param node.
     * @since  1.0
     */
    private function _makeParamPayload($param_value)
    {
        $doc = $this->_document;
        $dom = $doc->dom();

        $parent_node = $dom->getElementsByTagName("methodResponse")->item(0);
        if (is_null($parent_node)) {
            $parent_node = $doc->addNode("methodResponse");
        }
        $parent_node = $doc->addNode("params", $parent_node);
        $parent_node = $doc->addNode("param", $parent_node);

        $this->_buildNode($parent_node, "value", $param_value);
    }

    /**
     * Make Fault Payload
     *
     * This method is used to build an XML-RPC Falult Response with given
     * faultCode and description
     *
     * @param int    $fault_code   The fault code.
     * @param string $fault_string The fault description.
     *
     * @return string An XML-RPC methodResponse structure with Fault node.
     * @since  1.0
     */
    private function _makeFaultPayload($fault_code, $fault_string)
    {
        $doc = $this->_document;
        $dom = $doc->dom();

        $parent_node = $dom->getElementsByTagName("methodResponse")->item(0);
        $parent_node = $doc->addNode('fault', $parent_node);
        $parent_node = $doc->addNode('value', $parent_node);
        $fault_array = array('faultCode'=>$fault_code, 'faultString'=>$fault_string);
        $parent_node = $this->_addStruct($parent_node, $fault_array);
    }

    /**
     * Build Node
     *
     * This method builds a DOMNode Tree|Leaf from the node Parent, Name and
     * Value
     *
     * @param DOMNode $parent_node The name to be given to the node.
     * @param string  $node_name   The name to be given to the node.
     * @param mixed   $node_value  The node value (string|int|double|array).
     *
     * @return void
     * @since  1.0
     */
    private function _buildNode($parent_node, $node_name, $node_value)
    {
        $doc = $this->_document;
        if (!is_null($node_value)) {
            $parent_node = $doc->addNode($node_name, $parent_node);
            if ($node_value instanceof PHPFrame_RPCObject) {
                $node_value = $node_value->getRPCFields();
            } elseif ($node_value instanceof Traversable) {
                $node_value = iterator_to_array($node_value);
            }

            if (is_array($node_value)) {
                if ($this->_isAssoc($node_value)) {
                    $this->_addStruct($parent_node, $node_value);
                } else {
                    $this->_addArray($parent_node, $node_value);;
                }
            } else {
                if ($parent_node->nodeName == 'value') {
                    $parent_node = $this->_addType($parent_node, $node_value);
                }
                $doc->addNodeContent($parent_node, $node_value, false);
            }
        } else {
            if ($node_name == 'value') {
                $parent_node = $doc->addNode('value', $parent_node);
                $doc->addNodeContent($parent_node, 'NULL', false);
            }
        }
    }

    /**
     * Is Assoc
     *
     * This method is used to check if an array is an associative array
     *
     * @param array $test_array The array to be tested.
     *
     * @return boolean True if array is associative and not empty.
     * @since  1.0
     */
    private function _isAssoc($test_array)
    {
        $assoc = false;

        if (empty($test_array)) {
            return false;
        }

        $indexes = array_keys($test_array);
        $counter = 0;
        foreach ($indexes as $index) {
            if ($counter !== $index) {
                $assoc = true;
            }
            $counter++;
        }

        unset($index);
        return $assoc;
    }

    /**
     * Make Type
     *
     * This method is used to wrap node values in tags denoting their type
     *
     * @param DOMNode $parent_node The parent node.
     * @param mixed   $node_value  The value to be checked and wrapped if
     *                             applicable.
     *
     * @return DOMNode The original node, or firstChild if type added.
     * @since  1.0
     */
    private function _addType(DOMNode $parent_node, $node_value)
    {
        $doc = $this->_document;

        $type = 'string';

        if (is_bool($node_value)) {
            $type = 'boolean';
        } elseif (is_int($node_value)) {
            $type = 'int';
        } elseif (is_float($node_value)) {
            $type = 'double';
        } elseif (is_double($node_value)) {
            $type = 'double';
        }

        $parent_node = $doc->addNode($type, $parent_node);

        return $parent_node;
    }

    /**
     * Make Struct
     *
     * This method is used to build an XML-RPC <struct> structure from an
     * assoc_array
     *
     * @param DOMNode $parent_node The parent node.
     * @param array   $assoc_array The associative array.
     *
     * @return void
     * @since  1.0
     */
    private function _addStruct($parent_node, $assoc_array)
    {
        $doc = $this->_document;

        $parent_node = $doc->addNode('struct', $parent_node);

        foreach ($assoc_array as $key=>$value) {
            $local_parent = $doc->addNode('member', $parent_node);
            $doc->addNode('name', $local_parent, null, $key);
            $this->_buildNode($local_parent, 'value', $value);
        }
    }

    /**
     * Make Array
     *
     * This method is used to build an XML-RPC <array> structure from an indexed
     * array
     *
     * @param DOMNode $parent_node The parent node.
     * @param array   $index_array The array.
     *
     * @return void
     * @since  1.0
     */
    private function _addArray($parent_node, $index_array)
    {
        $doc = $this->_document;

        $parent_node = $doc->addNode('array', $parent_node);
        $parent_node = $doc->addNode('data', $parent_node);

        foreach ($index_array as $key=>$value) {
            $this->_buildNode($parent_node, 'value', $value);
        }
    }
}
