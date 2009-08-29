<?php
/**
 * PHPFrame/Utils/FeedReader.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Utils
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Feed Reader Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Utils
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Utils_FeedReader
{
    private $_url;
    private $_xml;
    private $_dom;
    private $_updated;
    private $_id;
    private $_title;
    private $_link_self;
    private $_link_alternate;
    private $_entries=array();
    
    public function __construct($url)
    {
        $this->_url = trim((string) $url);
        
        $this->_fetchFeed();
        $this->_parseXML();
    }
    
    public function getURL()
    {
        return $this->_url;
    }
    
    public function getXML()
    {
        return $this->_xml;
    }
    
    public function getDOM()
    {
        return $this->_dom;
    }
    
    public function getUpdated()
    {
        return $this->_updated;
    }
    
    public function getId()
    {
        return $this->_id;
    }
    
    public function getTitle()
    {
        return $this->_title;
    }
    
    public function getLinkSelf()
    {
        return $this->_link_self;
    }
    
    public function getLinkAlternate()
    {
        return $this->_link_alternate;
    }
    
    public function getEntries()
    {
        return $this->_entries;
    }
    
    private function _fetchFeed()
    {
        if (!class_exists("HTTP_Request2")) {
            include "HTTP/Request2.php";
        }

        $http_req      = new HTTP_Request2($this->_url);
        $http_response = $http_req->send();
        $this->_xml    = $http_response->getBody();
    }
    
    private function _parseXML()
    {
        $this->_dom = new DOMDocument;
        $this->_dom->loadXML($this->_xml);
        
        $properties = array("updated", "id", "title");
        
        foreach ($properties as $property) {
            $node = $this->_dom->getElementsByTagName($property);
            $prop_name = "_".$property;
            $this->$prop_name = $node->item(0)->nodeValue;
        }
        
        $link_nodes = $this->_dom->getElementsByTagName("link");
        
        $this->_link_self = $link_nodes->item(0)
                                       ->attributes
                                       ->getNamedItem("href")
                                       ->value;
                                       
        $this->_link_alternate = $link_nodes->item(1)
                                            ->attributes
                                            ->getNamedItem("href")
                                            ->value;
        
        $entries_nodes = $this->_dom->getElementsByTagName("entry");
        
        foreach ($entries_nodes as $entries_node) {
            $array = array();
            
            $array["updated"] = $entries_node->getElementsByTagName("updated")->item(0)->nodeValue;
            $array["id"]      = $entries_node->getElementsByTagName("id")->item(0)->nodeValue;
            
            $array["link"]    = $entries_node->getElementsByTagName("link")
                                              ->item(0)
                                              ->attributes
                                              ->getNamedItem("href")
                                              ->value;
            
            $array["title"]   = $entries_node->getElementsByTagName("title")->item(0)->nodeValue;
            
            $array["author"]  = $entries_node->getElementsByTagName("author")
                                             ->item(0)
                                             ->nodeValue;
            
            $array["author"] = trim($array["author"]);
            
            $array["content"] = $entries_node->getElementsByTagName("content")->item(0)->nodeValue;
            
            $this->_entries[] = $array;
        }
    }
}
