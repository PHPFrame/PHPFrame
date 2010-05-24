<?php
/**
 * PHPFrame/Client/CLIClient.php
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
 * Client for Command Line Interface
 *
 * @category PHPFrame
 * @package  Client
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_CLIClient extends PHPFrame_Client
{
    /**
     * Check if this is the correct helper for the client being used
     *
     * @static
     * @return PHPFrame_Client|boolean Instance of this class if correct helper
     *                                 for client or FALSE otherwise.
     * @since  1.0
     */
    public static function detect()
    {
        global $argv;

        if (is_array($argv)) {
            return new self;
        }

        return false;
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
        // Automatically log in as system user
        $user = new PHPFrame_User();
        $user->id(1);
        $user->groupId(1);
        $user->email('cli@localhost.local');

        // Store user in session
        $session = PHPFrame::getSession();
        $session->setUser($user);

        // Automatically set session token in request to allow forms
        $request->param($session->getToken(), 1);

        // create the CLI parser
        $parser = new Console_CommandLine();

        // add an option to delete original files after zipping
        $parser->addOption(
            'quiet',
            array(
                'short_name'  => '-q',
                'long_name'   => '--quiet',
                'action'      => 'StoreTrue',
                'description' => 'Operate in quiet mode (no output)'
            )
        );

        // add the files argument, the user can specify one or several files
        $parser->addOption(
            'infile',
            array(
                'short_name'  => '-i',
                'long_name'   => '--infile',
                'description' => 'List of input files separated by commas',
                'optional'    => true
            )
        );

        // add the out file name argument
        $parser->addOption(
            'outfile',
            array(
                'short_name'  => '-o',
                'long_name'   => '--outfile',
                'description' => 'File to save the output',
                'optional'    => true
            )
        );

        $parser->addArgument(
            'controller',
            array(
                'description' => 'The controller to run',
                'optional'    => true
            )
        );

        $parser->addArgument(
            'action',
            array(
                'description' => 'The action to run',
                'optional'    => true
            )
        );

        $parser->addArgument(
            'params',
            array(
                'multiple'=>true,
                'description'=>'List of request parameters separated by spaces',
                'optional'=>true
            )
        );

        try {
            global $argv, $argc;

            // Parse input options
            $result = $parser->parse($argc, $argv);

            $request->controllerName($result->args["controller"]);
            $request->action($result->args["action"]);
            $request->scriptName($argv[0]);
            $request->requestTime(time());
            $request->quiet($result->options["quiet"]);
            $request->method("CLI");
            $request->outfile($result->options["outfile"]);

            if ($result->options["infile"]) {
                $infile = new SplFileObject($result->options["infile"]);

                $request->attachFile(
                    "infile",
                    array(
                        "tmp_name" => $infile->getPath(),
                        "name"     => $infile->getFilename(),
                        "size"     => $infile->getSize(),
                        "type"     => $infile->getType(),
                        "error"    => null
                    )
                );
            }

            foreach ($result->args["params"] as $param) {
                parse_str($param, $param_pair);
                foreach ($param_pair as $param_key=>$param_value) {
                    $request->param($param_key, $param_value);
                }
            }

        } catch (Exception $e) {
            $parser->displayError($e->getMessage());
            exit;
        }
    }

    /**
     * Prepare response
     *
     * This method is invoked by the front controller before invoking the requested
     * action in the action controller. It gives the client an opportunity to do
     * something before the component is executed.
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
        $response->document(new PHPFrame_PlainDocument());

        // Set response renderer
        $response->renderer(new PHPFrame_PlainRenderer());
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
        // CLI can't redirect
    }
}
