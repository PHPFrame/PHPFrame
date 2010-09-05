<?php
/**
 * PHPFrame/Client/DefaultClient.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Client
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Client used by default (PC HTTP browsers or anything for which no helper
 * exists)
 *
 * @category PHPFrame
 * @package  Client
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_DefaultClient extends PHPFrame_Client
{
    /**
     * Check if this is the correct helper for the client being used
     *
     * @static
     * @return PHPFrame_Client|boolean Instance of this class if correct
     *                                 helper for client or false otherwise.
     * @since  1.0
     */
    public static function detect()
    {
        //this is our last hope to find a helper, just return instance
        return new self;
    }

    /**
     * Populate the Request object.
     *
     * @param PHPFrame_Request $request Reference to the request object.
     *
     * @return void
     * @since  1.0
     */
    public function populateRequest(PHPFrame_Request $request)
    {
        // Get an instance of PHP Input filter
        $inputfilter = new PHPFrame_InputFilter();

        // Process incoming request arrays and store filtered data in class
        $filtered_request = $inputfilter->process($_REQUEST);

        // Populate request params
        foreach ($filtered_request as $key=>$value) {
            if ($key == "controller") {
                $request->controllerName($value);
            } elseif ($key == "action") {
                $request->action($value);
            } elseif ($key == "ajax") {
                $request->ajax($value);
            } else {
                $request->param($key, $value);
            }
        }

        if (function_exists('apache_request_headers')) {
            $apache_headers = apache_request_headers();
            foreach ($apache_headers as $key=>$value) {
                $request->header($key, $value);
            }
        }

        foreach ($_SERVER as $key=>$value) {
            if (substr($key, 0, 5) == "HTTP_") {
                $key = str_replace('_', ' ', substr($key, 5));
                $key = str_replace(' ', '-', ucwords(strtolower($key)));
                $request->header($key, $value);
            } elseif ($key == "REQUEST_METHOD") {
                $request->method($value);
            } elseif ($key == "REMOTE_ADDR") {
                $request->remoteAddr($value);
            } elseif ($key == "REQUEST_URI") {
                $request->requestURI($value);
            } elseif ($key == "SCRIPT_NAME") {
                $request->scriptName($value);
            } elseif ($key == "QUERY_STRING") {
                $request->queryString($value);
            } elseif ($key == "REQUEST_TIME") {
                $request->requestTime($value);
            }
        }

        foreach ($_FILES as $key=>$value) {
            if (is_array($value) && !empty($value["name"])) {
                $request->file($key, $value);
            }
        }

        return;
    }

    /**
     * Prepare response
     *
     * This method is invoked by the front controller before invoking the
     * requested action in the action controller. It gives the client an
     * opportunity to do something before the component is executed.
     *
     * @param PHPFrame_Response $response   The response object to prepare.
     * @param string            $views_path Absolute path to vies dir.
     *
     * @return void
     * @since  1.0
     */
    public function prepareResponse(PHPFrame_Response $response, $views_path)
    {
        // Set document as response content
        $response->document(new PHPFrame_HTMLDocument());

        // Set response renderer
        $response->renderer(new PHPFrame_HTMLRenderer($views_path));
    }

    /**
     * Handle controller redirection
     *
     * @param string $url The URL we want to redirect to.
     *
     * @return void
     * @since  1.0
     */
    public function redirect($url)
    {
        $url = trim((string) $url);

        if (!$url) {
            return;
        }

        if (!headers_sent()) {
            header("Location: ".$url);
        } else {
            echo '<meta http-equiv="refresh" content="1; URL='.$url.'">';
        }

        exit(0);
    }
}
