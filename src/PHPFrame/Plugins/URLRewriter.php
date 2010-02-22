<?php
/**
 * PHPFrame/Utils/URLRewriter.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Plugins
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * URLRewriter Class
 * 
 * @category PHPFrame
 * @package  Plugins
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_URLRewriter extends PHPFrame_Plugin
{
    /**
     * Rewrite the request on route startup.
     * 
     * @return void
     * @since  1.0
     */
    public function routeStartup() 
    {
        $request_uri = $this->app()->request()->requestURI();
        $script_name = $this->app()->request()->scriptName();
        
        // If there is no request uri (ie: we are on the command line) we do 
        // not rewrite
        if (empty($request_uri)) {
            return;
        }
        
        // Get path to script
        $path = substr($script_name, 0, (strrpos($script_name, '/')+1));
        
        // If script name doesnt appear in the request URI we need to rewrite
        if (strpos($request_uri, $script_name) === false
            && $request_uri != $path
            && $request_uri != $path."index.php"
        ) {
            // Remove path from request uri. 
            // This gives us the component and action expressed as directories
            if ($path != "/") {
                $params = str_replace($path, "", $request_uri);
            } else {
                // If app is in web root we simply remove preceding slash
                $params = substr($request_uri, 1);
            }
            
            // Get component and action name using regex
            preg_match('/^([a-zA-Z]+)\/([a-zA-Z_]+)?/', $params, $matches);
            
            if (isset($matches[1])) {
                $this->app()->request()->controllerName($matches[1]);
                
                // Prepend component to query string
                $rewritten_query_string = "controller=".$matches[1];
                
                if (isset($matches[2])) {
                    $this->app()->request()->action($matches[2]);
                    
                    // Prepend component and action to query string
                    $rewritten_query_string .= "&action=".$matches[2];
                
                }
                
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $rewritten_query_string .= "&".$_SERVER['QUERY_STRING'];
                }
                
                $_SERVER['QUERY_STRING'] = $rewritten_query_string;
                
                // Update request uri
                $_SERVER['REQUEST_URI']  = $path."index.php?";
                $_SERVER['REQUEST_URI'] .= $_SERVER['QUERY_STRING'];
                
            }
        }
    }
    
    /**
     * Rewrite URLs after controllers have run in dispatch loop and theme has 
     * been applied.
     * 
     * @return string
     * @since  1.0
     */
    public function postApplyTheme()
    {
        // Get response body
        $body     = $this->app()->response()->document()->body();
        $base_url = $this->app()->config()->get("base_url");
        
        // Build sub patterns
        $controller = 'controller=([a-zA-Z]+)';
        $action     = 'action=([a-zA-Z_]+)';
        $amp        = '(&amp;|&)';
        
        // Build patterns and replacements
        $patterns[]     = '/"index.php\?'.$controller.$amp.$action.$amp.'/';
        $replacements[] = '"'.$base_url.'${1}/${3}?';
        
        $patterns[]     = '/"index.php\?'.$controller.$amp.$action.'"/';
        $replacements[] = '"'.$base_url.'${1}/${3}"';
        
        $patterns[]     = '/"index.php\?'.$controller.$amp.'/';
        $replacements[] = '"'.$base_url.'${1}?';
        
        $patterns[]     = '/"index.php\?'.$controller.'"/';
        $replacements[] = '"'.$base_url.'${1}"';
        
        // Replace the patterns in response body
        $body = preg_replace($patterns, $replacements, $body);
        
        // Set the processed body back in the response
        $this->app()->response()->document()->body($body);
    }
    
    /**
     * Rewrite a given URL.
     * 
     * @param string $url   The URL to rewrite.
     * @param bool   $xhtml [Optional] Default value is TRUE.
     * 
     * @return string The rewritten URL
     * @since  1.0
     */
    public static function rewriteURL($url, $xhtml=true)
    {
        $uri = new PHPFrame_URI(self::$_app->config()->get("base_url"));
        
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
        if (count($query_array) == 0) {
            return $url;
        }
        
        $rewritten_url = "";
        
        if (isset($query_array['controller']) 
            && !empty($query_array['controller'])
        ) {
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
                if ($i>0) {
                    $rewritten_url .= $xhtml ? "&amp;" : "&"; 
                }
                $rewritten_url .= $key."=".urlencode($value);
                $i++;
            }
        }
        
        return $uri->getBase().$rewritten_url;
    }
}
