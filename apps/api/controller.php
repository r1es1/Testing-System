<?php

class Api_Controller extends Controller
{

    /**
     * Property: self
     * Self app name;
     */
    private $self = 'api';

    /**
     * Property: debug
     * API debug state, used to display error description instead of raw codes.
     */
    protected $is_debug = true;

    /**
     * Property: interface_namespace
     * Namespace used to identify interface.
     */
    protected $interface_namespace = "\Api\Interfaces\\";

    /**
     * Property: available_interfaces
     * List of active interfaces.
     */
    protected $available_interfaces = array('users', 'tickets');

    /**
     * Property: available_methods
     * List of active methods according to their interfaces.
     */
    protected $available_methods = array(
        'users' => array('signin', 'signup'),
        'tickets' => array('list', 'take', 'view', 'check')
    );

    /**
     * Property: interfaces_pool
     * Pool of connected interfaces (aka multiple api requests per request).
     */
    protected $interfaces_pool;

    /**
     * Property: method
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     */
    protected $request_method;

    /**
     * Property: allowed_request_methods
     * Allowed HTTP methods.
     */
    protected $allowed_request_methods = array('GET', 'POST', 'PUT', 'DELETE');

    /**
     * Property: errors
     * List of API errors with their special codes.
     */
    protected $errors = array(
        1 => 'Unexpected request method.',
        2 => 'Interface does not describe requested method.',
        3 => '%s parameter is expected, but not found or empty.',
        4 => '%s request method is expected, but %s is received.',
        5 => 'Requested API version is not available.',
        6 => 'Requested API interface is not available.',
        7 => 'Requested API method is not available.',
        8 => 'Authorization token is invalid or expired.'
    );

    /**
     * Property: args
     * Arguments this request was made with.
     */
    protected $args;

    /**
     * Property: versions
     * Available API versions.
     */
    protected $versions = array(1);

    /**
     * Contructor.
     */
    public function __construct()
    {
        define("API_MODE", true);
        \Import::app('users', 'functions');

        $this->request_method = $_SERVER['REQUEST_METHOD'];

        if (!in_array($this->request_method, $this->allowed_request_methods)) {
            $this->error(1);
        }
    }

    /**
     * All API calls handler.
     * @request array Arguments of api request instance.
     */
    public function call($request)
    {
        $this->args = $request;

        // temp fix
        $this->args['version'] = $this->args[1];
        $this->args['interface'] = $this->args[2];
        $this->args['method'] = $this->args[3];

        if (!isset($this->args['params'])) {
            $this->args['params'] = $_GET;
        }

        if (($err = $this->validate_request()) === true) {
            Import::interface($this->self, $this->args['version'], $this->args['interface']);
            $interface_main_class = $this->interface_namespace . ucfirst(strtolower($this->args['interface'])) . '\\Main';
            if (!isset($this->interfaces_pool[$this->args['interface']])) {
                $this->interfaces_pool[$this->args['interface']] = new $interface_main_class;
            }

            if (method_exists($this->interfaces_pool[$this->args['interface']], $this->args['method'])) {
                $call_result = $this->interfaces_pool[$this->args['interface']]->{$this->args['method']}($this->args, $this);

                if (is_array($call_result)) {
                    return $this->response($call_result);
                } else if (is_bool($call_result)) {
                    return $this->response('empty', $call_result);
                } else {
                    return $this->response($call_result);
                }
            } else {
                $this->error(2);
            }
        } else {
            $this->error($err);
        }
    }

    /**
     * Request validator.
     */
    public function validate_request()
    {
        if (!in_array($this->args['version'], $this->versions)) {
            return 5; // return code 5
        }

        if (!in_array($this->args['interface'], $this->available_interfaces)) {
            return 6; // return code 6
        }

        if (!in_array(strtolower($this->args['method']), $this->available_methods[$this->args['interface']])) {
            return 7; // return code 7
        }

        return true;
    }

    /**
     * Parameters expector.
     */
    public function expect_parameters($list, $require_creds = false)
    {
        if($require_creds) {
            $list[] = 'token';
        }

        foreach ($list as $exp) {
            if (!isset($this->args['params'][$exp]) || \FilterMaster::isRegEmpty($this->args['params'][$exp])) {
                $this->error(3, array($exp));
            }
        }

        // check creds
        if($require_creds) {
            $this->check_creds($this->args['params']['token']);
        }
    }

    /**
     * Request method expector.
     */
    public function expect_request_method($type = 'GET')
    {
        if ($type != $this->request_method) {
            $this->error(4, array($type, $this->request_method));
        }
    }

    /**
     * Check if current request is inline.
     */
    protected function is_inline_call()
    {
        return (isset($this->args['inline']) && $this->args['inline'] == true);
    }

    /**
     * Response handler.
     */
    public function response($content, $status = true, $code = null)
    {
        $response = array(
            'status' => $status
        );

        if (is_array($content)) {
            $response['response'] = $content;
        } elseif (!is_null($content)) {
            $response['message'] = $content;
        }

        if (!is_null($code)) {
            $response['response']['code'] = $code;
        }

        // dump($response, true);
        if ($this->is_inline_call()) {
            return $response;
        } else {
            exit(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * Check credentials.
     */
    public function check_creds($token) {
        $cred = \R::findOne("credentials", "`token` = ?", array($token));

        if(!$cred || ($cred->expire != null && \TimeManager::time() > $cred->expire)) {
            // token invalid
            $this->error(8);
        } else {
            // token valid
            \Users\get(\R::findOne('user', '`id` = ?', array($cred['user_id'])));
        }
    }

    /**
     * Error thrower.
     */
    public function error($code, $format = array())
    {
        if (isset($this->errors[$code])) {
            if ($this->is_debug) {
                if (!empty($format)) {
                    $this->response(vsprintf($this->errors[$code], $format), false, $code);
                } else {
                    $this->response(null, false, $code);
                }
            } else {
                $this->response(null, false, $code);
            }
        } else {
            $this->response('API internal error.', false, null);
        }
    }

}