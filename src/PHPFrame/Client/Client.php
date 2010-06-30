<?php
/**
 * PHPFrame/Client/Client.php
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
 * Abstract Client Class
 *
 * Implementing Client classes represent the different "client" applications
 * that will interact with our app.
 *
 * PHPFrame 1.0 includes 4 implementations of this interface:
 *
 * - CLI (Command Line Interface)
 * - Default (Normal web browsers)
 * - Mobile (Mobile phones)
 * - XMLRPC (XMLRPC API consumers)
 *
 * @category PHPFrame
 * @package  Client
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
abstract class PHPFrame_Client
{
    /**
     * Check if this is the correct helper for the client being used and
     * returns instance if so.
     *
     * @static
     * @return PHPFrame_Client|boolean
     * @since  1.0
     */
    abstract public static function detect();

    /**
     * Populate the Request object.
     *
     * @param PHPFrame_Request $request Reference to the request object.
     *
     * @return void
     * @since  1.0
     */
    abstract public function populateRequest(PHPFrame_Request $request);

    /**
     * Prepare response
     *
     * This method is invoked by the front controller before invoking the
     * requested action in the action controller. It gives the client an
     * opportunity to do something before the component is executed.
     *
     * The implementing Client classes will need to make sure the set the
     * response content to the right document type. See PHPFrame_DefaultClient
     * for an example.
     *
     * @param PHPFrame_Response $response   The response object to prepare.
     * @param string            $views_path Absolute path to vies dir.
     *
     * @return void
     * @since  1.0
     */
    abstract public function prepareResponse(
        PHPFrame_Response $response,
        $views_path
    );

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

        if ($url) {
            header("Location: ".$url);
            exit;
        }
    }
}
