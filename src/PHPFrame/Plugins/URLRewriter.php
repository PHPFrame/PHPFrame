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
        $request     = $this->app()->request();
        $request_uri = $request->requestURI();
        $script_name = $request->scriptName();

        // If there is no request uri (ie: we are on the command line) we do
        // not rewrite
        if (empty($request_uri)) {
            return;
        }

        // Get path to script
        $path = substr($script_name, 0, (strrpos($script_name, '/')+1));

        // If script name doesn't appear in the request URI we need to rewrite
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

            $array = explode("?", $params);
            $command = $array[0];
            if (isset($array[1])) {
                $query_string = $array[1];
            } else {
                $query_string = "";
            }

            $array = explode("/", $command);
            $request->controllerName($array[0]);
            $rewritten_query_string = "controller=".$array[0];
            if (isset($array[1])) {
                $request->action($array[1]);
                $rewritten_query_string .= "&action=".$array[1];
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

    /**
     * Rewrite redirect URL if it has been set after dispatching controller.
     *
     * @return void
     * @since  1.0
     */
    public function postDispatch()
    {
        $response = $this->app()->response();
        $location = $response->header("Location");

        if ($location) {
            $response->header("Location", $this->_rewriteURLs($location));
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
        $body = $this->app()->response()->document()->body();

        // Set the processed body back in the response
        $this->app()->response()->document()->body($this->_rewriteURLs($body));
    }

    /**
     * Rewrite URLs contained in given string.
     *
     * @param string $str String containing text to search for URLs to rewrite.
     *
     * @return string
     * @since  1.0
     */
    private function _rewriteURLs($str)
    {
        $base_url = $this->app()->config()->get("base_url");

        $pattern  = "/(".preg_quote($base_url, "/")."index\.php";
        $pattern .= "|([^\/]{1}|^)index\.php)";
        $pattern .= "(\?controller=([a-z_\-]+)";
        $pattern .= "((&amp;|&)action=([a-z_]+)(&amp;|&)?)?)?/";

        return preg_replace_callback(
            $pattern,
            array($this, "_replaceMatches"),
            $str
        );
    }

    /**
     * This method is passed as callback to preg_replace_callback and it builds
     * the replacement string based on the pattern matches.
     *
     * @param array $matches Array containing the regex matches.
     *
     * @return string
     * @since  1.0
     */
    private function _replaceMatches(array $matches)
    {
        $str = "";

        if (isset($matches[2]) && !empty($matches[2])) {
            $str .= $matches[2];
        }

        $str .= $this->app()->config()->get("base_url");

        if (isset($matches[4])) {
            $str .= $matches[4];
        }

        if (isset($matches[7])) {
            $str .= "/".$matches[7];
        }

        if (isset($matches[8])) {
            $str .= "?";
        }

        return $str;
    }
}
