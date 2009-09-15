<?php
/**
 * PHPFrame/Utils/URLRewriter.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Plugins
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * URLRewriter Class
 * 
 * @category PHPFrame
 * @package  Plugins
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_URLRewriter extends PHPFrame_Plugin
{
    /**
     * Rewrite the request
     * 
     * @static
     * @access public
     * @return void
     * @since  1.0
     */
    public function routeStartup() 
    {
        // If there is no request uri (ie: we are on the command line) we do not rewrite
        if (!isset($_SERVER['REQUEST_URI'])) {
            return;
        }
        
        // Get path to script
        $path = substr($_SERVER['SCRIPT_NAME'], 0, (strrpos($_SERVER['SCRIPT_NAME'], '/')+1));
        
        // If the script name doesnt appear in the request URI we need to rewrite
        if (strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) === false
            && $_SERVER['REQUEST_URI'] != $path
            && $_SERVER['REQUEST_URI'] != $path."index.php") {
            // Remove path from request uri. 
            // This gives us the component and action expressed as directories
            if ($path != "/") {
                $params = str_replace($path, "", $_SERVER['REQUEST_URI']);
            }
            else {
                // If app is in web root we simply remove preceding slash
                $params = substr($_SERVER['REQUEST_URI'], 1);
            }
            
            //preg_match('/^([a-zA-Z]+)\/?([a-zA-Z_]+)?\/?.*$/', $params, $matches);
            
            // Get component name using regex
            preg_match('/^([a-zA-Z]+)/', $params, $controller_matches);
            
            // Get action name using regex
            preg_match('/^[a-zA-Z]+\/([a-zA-Z_]+)/', $params, $action_matches);
            
            if (isset($controller_matches[1]) && !empty($controller_matches[1])) {
                $controller = $controller_matches[1];
                if (isset($action_matches[1])) {
                    $action = $action_matches[1];
                }

                // Prepend component and action to query string
                $rewritten_query_string = "controller=".$controller;
                if (!empty($action)) $rewritten_query_string .= "&action=".$action;
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $rewritten_query_string .= "&".$_SERVER['QUERY_STRING'];
                }
                $_SERVER['QUERY_STRING'] = $rewritten_query_string;
                
                // Update request uri
                $_SERVER['REQUEST_URI'] = $path."index.php?".$_SERVER['QUERY_STRING'];
                
                // Set vars in _REQUEST array
                if (!empty($controller)) {
                    $_REQUEST['controller'] = $controller;
                    $_GET['controller'] = $controller;
                }
                if (!empty($action)) {
                    $_REQUEST['action'] = $action;
                    $_GET['action'] = $action;    
                }
            }
        }
    }
    
    /**
     * Rewrite URL
     * 
     * @param string $url   The URL to rewrite
     * @param bool   $xhtml A boolean to indicate whether we want to use an XHTML
     *                      compliant URL. Default value is TRUE.
     * 
     * @static
     * @access public
     * @return string
     * @since  1.0
     */
    public static function rewriteURL($url, $xhtml=true) 
    {
        $uri = new PHPFrame_URI();
        
        if (!preg_match('/^http/i', $url)) {
            $url = $uri->getBase().$url;
        }
        
        // Parse URL string
        $url_array = parse_url($url);
        $query_array = array();
        if (isset($url_array['query'])) {
            parse_str($url_array['query'], $query_array);
        }
        
        
        // If there are no query parameters we don't need to rewrite anything
        if (count($query_array) == 0) return $url;
        
        $rewritten_url = "";
        
        if (isset($query_array['controller']) && !empty($query_array['controller'])) {
            $rewritten_url .= $query_array['controller'];
            unset($query_array['controller']);
        }
        
        if (isset($query_array['action']) && !empty($query_array['action'])) {
            $rewritten_url .= "/".$query_array['action'];
            unset($query_array['action']);
        }
        
        if (is_array($query_array) && count($query_array) > 0) {
            $rewritten_url .= "?";
            $i=0;
            foreach ($query_array as $key=>$value) {
                if ($i>0) $rewritten_url .= $xhtml ? "&amp;" : "&"; 
                $rewritten_url .= $key."=".urlencode($value);
                $i++;
            }
        }
        
        return $uri->getBase().$rewritten_url;
    }
}
