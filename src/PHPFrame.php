<?php
/**
 * PHPFrame/PHPFrame.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Main
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * PHPFrame Class
 * 
 * This class provides a number of static methods that serve as a simplified
 * interface or facade to the PHPFrame framework.
 * 
 * It also provides information about the installed PHPFrame version.
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Main
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame
{
    /**
     * The PHPFrame version
     * 
     * @var string
     */
    const VERSION='1.0 Alpha';
    
    /**
     * Get PHPFrame version
     * 
     * @return string
     * @since  1.0
     */
    public static function Version() 
    {
        return self::VERSION;
    }
    
    /**
     * Fire up the app
     * 
     * This method instantiates the front controller and runs it.
     * 
     * @return void
     * @since  1.0
     */
    public static function Fire() 
    {
        $frontcontroller = new PHPFrame_Application_FrontController();
        $frontcontroller->run();
    }
    
    /**
     * Request Registry
     * 
     * @return PHPFrame_Registry_Request
     * @since  1.0
     */
    public static function Request() 
    {
        return PHPFrame_Registry_Request::getInstance();
    }
    
    /**
     * Get response object
     * 
     * @return PHPFrame_Application_Response
     * @since  1.0
     */
    public static function Response()
    {
        return PHPFrame_Application_Response::getInstance();
    }
    
    /**
     * Get session object
     * 
     * @return PHPFrame_Registry_Session
     * @since  1.0
     */
    public static function Session() 
    {
        return PHPFrame_Registry_Session::getInstance();
    }
    
    /**
     * Get application registry
     * 
     * @param sring $path The path to the cache directory where to store the app
     *                    registry. If not passed it uses a directory called
     *                    "cache" within the directory specified in config::FILESYSTEM
     * 
     * @return PHPFrame_Registry_Application
     * @since  1.0
     */
    public static function AppRegistry($path='') 
    {
        if (empty($path)) {
            $path = config::FILESYSTEM.DS."cache";
        }
        
        return PHPFrame_Registry_Application::getInstance($path);
    }
    
    /**
     * Get database object
     * 
     * @param object $dsn     An object of type PHPFrame_Database_DSN used to get DB 
     *                        connection. This parameter is optional. If omitted a 
     *                        new DSN object will be created using the database 
     *                        details provided by the config class. 
     * @param string $db_user If we specify a DSN object we might also need to 
     *                        provide a db user in order to connect to the database 
     *                        server.
     * @param string $db_pass When both a DSN object and a db user have been passed 
     *                        we might also need to provide a password for the db 
     *                        connection.
     * 
     * @return PHPFrame_Database
     * @since  1.0
     */
    public static function DB(
        PHPFrame_Database_DSN $dsn=null,
        $db_user=null,
        $db_pass=null
    ) {
        // If no DSN is passed we use settings from config
        if (is_null($dsn)) {
            $dsn_concrete_class = "PHPFrame_Database_DSN_".config::DB_DRIVER;
            $dsn = new $dsn_concrete_class(config::DB_HOST, config::DB_NAME);
        }
        
        if (is_null($db_user)) {
            $db_user = config::DB_USER;
        }
        
        if (is_null($db_pass)) {
            $db_pass = config::DB_PASS;
        }
        
        return PHPFrame_Database::getInstance($dsn, $db_user, $db_pass);
    }
}
