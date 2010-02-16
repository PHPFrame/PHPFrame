<?php
/**
 * PHPFrame/HTTP/FeedReader.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   HTTP
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Feed Reader Class
 * 
 * @category PHPFrame
 * @package  HTTP
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_FeedReader
{
    /**
     * The feed URL
     * 
     * @var string
     */
    private $_url;
    /**
     * XML string
     * 
     * @var string
     */
    private $_xml;
    /**
     * DOM object
     * 
     * @var DOMDocument
     */
    private $_dom;
    private $_updated;
    private $_id;
    private $_title;
    private $_link_self;
    private $_link_alternate;
    /**
     * Array containing the feed items
     * 
     * @var array
     */
    private $_entries = array();
    
    /**
     * Constructor
     * 
     * @param string $url       The feed url.
     * @param int    $cache     Cache time in seconds.
     * @param string $cache_dir Full path to the cache directory.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct($url, $cache=0, $cache_dir=null)
    {
        $this->_url   = trim((string) $url);
        $this->_cache = (int) $cache;
        $this->_xml   = $this->_fetchFeed($cache, $cache_dir);
        
        // Parse xml (acquired either from HTTP request or cache)
        $this->_parseXML();
    }
    
    /**
     * Get the feed URL
     * 
     * @return string
     * @since  1.0
     */
    public function getURL()
    {
        return $this->_url;
    }
    
    /**
     * Get XML string
     * 
     * @return string
     * @since  1.0
     */
    public function getXML()
    {
        return $this->_xml;
    }
    
    /**
     * Get DOM object
     * 
     * @return DOMDocument
     * @since  1.0
     */
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
    
    /**
     * Get feed items/entries
     * 
     * @return array
     * @since  1.0
     */
    public function getEntries()
    {
        return $this->_entries;
    }
    
    /**
     * Fetch feed using HTTP request
     * 
     * @param int    $cache     Cache time in seconds.
     * @param string $cache_dir Full path to the cache directory.
     * 
     * @return string The HTTP request body containing the XML string
     * @since  1.0
     */
    private function _fetchFeed($cache=0, $cache_dir=null)
    {
        // Create HTTP request
        $http_req = new PHPFrame_HTTPRequest($this->_url);
        $http_req->setCacheTime($cache);
        if (!is_null($cache_dir)) {
            $http_req->setCacheDir($cache_dir);
        }
        
        // Send HTTP request and capture HTTP response
        $http_response = $http_req->send();
        
        // Return the response body (string)
        return $http_response->getBody();
    }
    
    /**
     * Parse XML string stored in internal property
     * 
     * @return void
     * @since  1.0
     */
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
        
        $attr = $link_nodes->item(0)->attributes;
        $this->_link_self = $attr->getNamedItem("href")->value;
                                       
        $this->_link_alternate = $attr->getNamedItem("href")->value;
        
        $entries_nodes = $this->_dom->getElementsByTagName("entry");
        
        foreach ($entries_nodes as $entries_node) {
            $array = array();
            
            $updated = $entries_node->getElementsByTagName("updated")->item(0);
            $id      = $entries_node->getElementsByTagName("id")->item(0);
            $link    = $entries_node->getElementsByTagName("link")->item(0);
            $title   = $entries_node->getElementsByTagName("title")->item(0);
            $author  = $entries_node->getElementsByTagName("author")->item(0);
            $content = $entries_node->getElementsByTagName("content")->item(0);
            
            $array["updated"] = $updated->nodeValue;
            $array["id"]      = $id->nodeValue;
            $array["link"]    = $link->attributes->getNamedItem("href")->value;
            $array["title"]   = $title->nodeValue;
            $array["author"]  = trim($author->nodeValue);
            $array["content"] = $content->nodeValue;
            
            $this->_entries[] = $array;
        }
    }
}
