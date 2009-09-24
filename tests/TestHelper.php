<?php
class TestHelper
{
    public static function initFramework()
    {
        $src_dir = str_replace(DIRECTORY_SEPARATOR."tests", "", dirname(__FILE__));
        $src_dir .= DIRECTORY_SEPARATOR."src";
        require_once $src_dir.DIRECTORY_SEPARATOR."PHPFrame.php";
        
        // Set constant with app specific path to tmp directory
        $tests_path = dirname(__FILE__);
        define("PHPFRAME_INSTALL_DIR", $tests_path);
        define("PHPFRAME_CONFIG_DIR", $tests_path.DIRECTORY_SEPARATOR."etc");
        define("PHPFRAME_TMP_DIR", $tests_path.DIRECTORY_SEPARATOR."tmp");
        define("PHPFRAME_VAR_DIR", $tests_path.DIRECTORY_SEPARATOR."var");
        
        // Initialise PHPFrame environment
        // This needs to be done before anything else to avoid session errors
        // due to the fact that PHPUnit sends output before the tests are run
        PHPFrame::Env();
        PHPFrame::Response();
    }
}
