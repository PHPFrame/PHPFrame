<?php
class PHPFrame_Config_XML extends PHPFrame_Config
{
    protected function __construct($path=null)
    {
        parent::__construct($path);
    }
    
    public function toString()
    {
        $xml = new PHPFrame_Document_XML();
        $config_node = $xml->addNode(null, "config");
        
        foreach ($this->toArray() as $row) {
            $data_node = $xml->addNode($config_node, "data", array());
            
            if (is_array($row)) {
                foreach ($row as $key=>$value) {
                    $xml->addNode($data_node, $key, array(), $value);
                }
            }
        }
        
        return $xml->toString();
    }
    
    protected function _fetchData()
    {
        $xml = @simplexml_load_file($this->_path);
        
        if (
            !$xml instanceof SimpleXMLElement
            || !$xml->data instanceof SimpleXMLElement
        ) {
            $msg = get_class($this).": ";
            $msg .= "Could not load config from xml file.";
            trigger_error($msg);
        }
        
        foreach ($xml->data as $data) {
            if (!$data instanceof SimpleXMLElement) {
                continue;
            }
            
            $array = array();
            foreach ($data as $key=>$value) {
                $array[$key] = (string) $value;
            }
            
            if (
                isset($array["value"]) 
                && $array["value"] == "" 
                && isset($array["def_value"]) 
                && $array["def_value"] != ""
            ) {
                $array["value"] = $array["def_value"];
            }
            
            if (isset($array["name"])) {
                $this->data[$array["name"]] = $array;
            } else {
                $this->data[] = $array;
            }
        }
    }
}
