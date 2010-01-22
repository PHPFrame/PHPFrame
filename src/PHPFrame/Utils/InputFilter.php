<?php
class PHPFrame_InputFilter
{
    private $tag_blacklist  = array(
        'applet', 'body', 'bgsound', 'base', 'basefont', 'embed', 'frame', 
        'frameset', 'head', 'html', 'id', 'iframe', 'ilayer', 'layer', 'link', 
        'meta', 'name', 'object', 'script', 'style', 'title', 'xml'
    );
    private $attr_blacklist = array(
        'action', 'background', 'codebase', 'dynsrc', 'lowsrc'
    ); // should also strip ALL event handlers
    
    public function __construct()
    {
        
    }
    
    public function process($var)
    {
        return $var;
    }
}
