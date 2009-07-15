<?php
interface PHPFrame_SCM
{
    public function checkout($url, $path, $username=null, $password=null);
    
    public function update($path);
    
    public function switchURL($url, $path);
    
    public function export($url, $path);
    
    public function commit();
}