<?php
class CliHelper extends PHPFrame_ViewHelper
{
    public function formatH2($str)
    {
        $title     = (string) $str;
        $underline = "";
        for ($i=0; $i<strlen($title); $i++) {
            $underline .= "-";
        }
        
        return $title."\n".$underline;
    }
}
