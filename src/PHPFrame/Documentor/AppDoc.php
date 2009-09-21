<?php
class PHPFrame_AppDoc
{
    private $_visibility = PHPFrame_ClassDoc::VISIBILITY_PUBLIC;
    
    public function __construct($visibility=PHPFrame_ClassDoc::VISIBILITY_PUBLIC)
    {
        $this->_visibility = $visibility;
    }
}
