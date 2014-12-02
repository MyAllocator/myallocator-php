<?php
/**
 * Copyright (C) 2014 MyAllocator
 *
 * A copy of the LICENSE can be found in the LICENSE file within
 * the root directory of this library.  
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

namespace MyAllocator\phpsdk\Api;
use MyAllocator\phpsdk\Object\Auth as Auth;
use MyAllocator\phpsdk\Util\Requestor;
use MyAllocator\phpsdk\Util\Common;
use MyAllocator\phpsdk\Exception\ApiException;
use MyAllocator\phpsdk\Exception\ApiAuthenticationException;

class Api
{
    /**
     * @var boolean Whether or not the API is currently enabled/supported.
     */
    protected $enabled = true;

    /**
     * @var string The api to call.
     */
    protected $id = null;

    /**
     * @var \MyAllocator\Object\Auth Authentication object for requester.
     */
    private $auth = null;

    /**
     * @var array API request parameters to be included in API request.
     */
    private $params = null;

    /**
     * @var mixed The response from the last request.
     */
    private $lastApiResponse = null;

    /**
     * @var string A default element name to be used by XML requests.
     */
    protected $defaultElementNameXML = 'item';

    /**
     * @var array Array of required and optional authentication and argument 
     *      keys (string) for API method.
     */
    protected $keys = array(
        'auth' => array(
            'req' => array(),
            'opt' => array()
        ),
        'args' => array(
            'req' => array(),
            'opt' => array()
        )
    );

    /**
     * @var array MyAllocator API configuration.
     */
    private $config = null;

    public function __construct($cfg = null)
    {
        // Load auth information if provided
        if (isset($cfg) && isset($cfg['auth'])) {
            if (is_array($cfg['auth'])) {
                $auth = new Auth();
                $auth_refl = new \ReflectionClass($auth);
                $props = $auth_refl->getProperties(\ReflectionProperty::IS_PUBLIC);

                foreach ($props as $prop) {
                    $name = $prop->getName();
                    if (isset($cfg['auth'][$name])) {
                        $auth->$name = $cfg['auth'][$name];
                    }
                }

                $this->auth = $auth;
            } else if (is_object($cfg['auth']) && is_a($cfg['auth'], 'MyAllocator\phpsdk\Object\Auth')) {
                $this->auth = $cfg['auth'];
            }
        }

        // Load API Configuration (Throws exception if cannot find config file)
        $this->config = require(dirname(__FILE__) . '/../Config/Config.php');
    }

    /**
     * Set the parameters to be used in the API request. Parameters may
     * also be set at the time of API call via callApiWithParams().
     *
     * @param array $params API request parameters.
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Call the API using previously set parameters (if any).
     */
    public function callApi()
    {
        return $this->processRequest($this->params);
    }

    /**
     * Call the API using provided parameters (if any).
     *
     * @param array $params API request parameters.
     */
    public function callApiWithParams($params = null)
    {
        return $this->processRequest($params);
    }

    /**
     * Get the authentication object for the API.
     *
     * @return MyAllocator\phpsdk\Object\Auth API Authentication object.
     */
    public function getAuth($errorOnNull = false)
    {
        if ($errorOnNull && !$this->auth) {
            $msg = 'No Auth object provided.  (HINT: Set your Auth data using '
                 . '"$API->setAuth(Auth $auth)" or $API\' constructor.  '
                 . 'See https://TODO for details.';
            throw new ApiException($msg);
        }

        return $this->auth;
    }

    /**
     * Set an API configuration key (overwrites keys in Config.php).
     *
     * @param key $key The configuration key. 
     * @param value $value The configuration key value. 
     */
    public function setConfig($key, $value)
    {
        if (!$key || !$value) {
            return false;
        }
        $this->config[$key] = $value;
        return true;
    }

    /**
     * Set the authentication object for the API.
     *
     * @param MyAllocator\phpsdk\Object\Auth API Authentication object.
     */
    public function setAuth(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Determine if the API is enabled.
     *
     * @return booleam True if the API is enabled.
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Get the last API response as array($rbody, $rcode).
     *
     * @return array
     */
    public function getLastApiResponse()
    {
        return $this->lastApiResponse;
    }

    /**
     * Validate and process/send the API request.
     *
     * @param array $params API request parameters.
     * @return array API response.
     */
    private function processRequest($params = null)
    {
        // Ensure this api is currently enabled/supported
        $this->assertEnabled();

        // Validate and sanitize parameters
        $params = $this->validateApiParameters($this->keys, $params);

        // Perform request
        $requestor = new Requestor($this->config);
        // If xml, set default tag element name
        if ($this->config['apiDataFormat'] == 'xml') {
            $requestor->defaultElementNameXML = $this->defaultElementNameXML; 
        }
        $url = $this->id;
        $response = $requestor->request('post', $url, $params);

        // Return result
        $this->lastApiResponse = $response;
        return $response;
    }

    /**
     * Assert the API is enabled.
     *
     * @throws MyAllocator\phpsdk\Exception\ApiException
     */
    private function assertEnabled()
    {
        if (!$this->enabled) {
            $msg = 'This API is not currently enabled/supported.';
            throw new ApiException($msg);
        }
    }

    /**
     * Validate authentication and argument parameters for an API.
     *
     * @param array $keys Array of required and optional keys.
     * @param array $params Array of API parameters.
     * @return array Sanitized and validated parameters.
     * @throws MyAllocator\phpsdk\Exception\ApiException
     * @throws MyAllocator\phpsdk\Exception\ApiAuthenticationException
     */
    private function validateApiParameters($keys = null, $params = null)
    {
        // Assert API has defined an id/endpoint
        $this->assertApiId();

        // Assert API keys array structure is valid
        $this->assertKeysArrayValid($keys);

        // Assert keys array has minimum required optional parameters
        $this->assertKeysHasMinOptParams($keys, $params);

        // Assert and set authentication parameters from Auth object
        $params = $this->setAuthenticationParameters($keys, $params, 'req');
        $params = $this->setAuthenticationParameters($keys, $params, 'opt');

        // Assert required argument parameters exist (non-authentication)
        $this->assertReqParameters($keys, $params);

        // Remove extra parameters not defined in keys array
        $this->removeUnknownParameters($keys, $params);

        return $params;
    }

    /*
     * Assert the API id is set by the API class.
     */
    private function assertApiId()
    {
        // Assert minimum number of optional args exist if requirement exists
        if (!$this->id) {
            $msg = 'The API id has not be set in the API class.';
            throw new ApiException($msg);
        }
    }

    /*
     * Assert required API keys exist and are valid.
     */
    private function assertKeysArrayValid($keys = null)
    {
        if ((!$keys) ||
            (!is_array($keys)) ||
            (!isset($keys['auth'])) || 
            (!is_array($keys['auth'])) ||
            (!isset($keys['auth']['req'])) || 
            (!is_array($keys['auth']['req'])) ||
            (!isset($keys['auth']['opt'])) || 
            (!is_array($keys['auth']['opt'])) ||
            (!isset($keys['args'])) || 
            (!is_array($keys['args'])) ||
            (!isset($keys['args']['req'])) || 
            (!is_array($keys['auth']['req'])) ||
            (!isset($keys['args']['opt'])) || 
            (!is_array($keys['auth']['opt']))
        ) {
            $msg = 'Invalid API keys provided. (HINT: Each '
                 . 'API class must define a $keys array with '
                 . 'specific key requirements. (HINT: View an /Api/[file] '
                 . 'for an example.)';
            throw new ApiException($msg);
        }
    }

    /*
     * Assert parameters include minimum number of optional
     * parameters as configured/defined by the API.
     */
    private function assertKeysHasMinOptParams($keys, $params)
    {
        // Assert minimum number of optional args exist if requirement exists
        if ((isset($keys['args']['opt_min'])) && 
            (!$params || count($params) < $keys['args']['opt_min'])
        ) {
            $msg = 'API requires at least '.$keys['args']['opt_min'].' optional '
                 . 'parameter(s). (HINT: Reference the $keys '
                 . 'property at the top of the API class file for '
                 . 'required and optional parameters.)';
            throw new ApiException($msg);
        }
    }

    /*
     * Validate and set required authentication parameters from Auth object.
     */
    private function setAuthenticationParameters(
        $keys = null,
        $params = null,
        $type = 'req'
    ) {
        if (!empty($keys['auth'][$type])) {
            if ($this->auth == null) {
                $msg = 'No Auth object provided.  (HINT: Set your Auth data using '
                     . '"$API->setAuth(Auth $auth)" or $API\' constructor.  '
                     . 'See https://TODO for details.';
                throw new ApiAuthenticationException($msg);
            }

            // Set authentication parameters
            $auth_group = false;
            foreach ($keys['auth'][$type] as $k) {
                if (is_array($k) && !empty($k)) {
                    /*
                     * Different auth key groups may be required.
                     * In these situations, must assert that each
                     * key within an auth key group exists. Exits
                     * once the first auth key group is validated.
                     */

                    if ($auth_group && $auth_group_validated) {
                        /*
                        * At this point an authentication group has been satisfied
                        * and we don't need to process additional groups.
                        */
                        continue;
                    }

                    $auth_group = true;
                    $auth_group_validated = true;
                    foreach ($k as $g) {
                        if (!isset($params[$g])) {
                            $v = $this->auth->getAuthKeyVar($g);
                            if (!$v) {
                                $auth_group_validated = false;
                                break;
                            }
                            $params[$g] = $v;
                        }
                    }
                } else {
                    if (!isset($params[$k])) {
                        $v = $this->auth->getAuthKeyVar($k);
                        if (!$v) {
                            if ($type == 'req') {
                                $msg = 'Authentication key `'.$k.'` is required. '
                                     . 'HINT: Set your Auth data using "$API->'
                                     . 'setAuth(Auth $auth)" or $API\' constructor. '
                                     . 'See https://TODO for details.';
                                throw new ApiAuthenticationException($msg);
                            } else {
                                // optional
                                continue;
                            }
                        }
                        $params[$k] = $v;
                    }
                }
            }

            // If keys configured with authentication groups, verify one was validated
            if ($auth_group && !$auth_group_validated) {
                $msg = 'A required authentication key group was not satisfied. '
                     . '(HINT: Reference the $keys '
                     . 'property at the top of the API class file for '
                     . 'required and optional parameters.)';
                throw new ApiAuthenticationException($msg);
            }
        }

        return $params;
    }

    /*
     * Validate required parameters for Api.
     */
    private function assertReqParameters($keys = null, $params = null, $type = 'req')
    {
        if (!empty($keys['args']['req'])) {
            if (!$params) {
                $msg = 'No parameters provided. (HINT: Reference the $keys '
                     . 'property at the top of the API class file for '
                     . 'required and optional parameters.)';
                throw new ApiException($msg);
            }

            foreach ($keys['args']['req'] as $k) {
                if (!isset($params[$k])) {
                    $msg = 'Required parameter `'.$k.'` not provided. '
                         . '(HINT: Reference the $keys '
                         . 'property at the top of the API class file for '
                         . 'required and optional parameters.)';
                    throw new ApiException($msg);
                }
            }
        }
    }

    /*
     * Strip parameters not defined in API keys array.
     */
    private function removeUnknownParameters($keys = null, $params = null)
    {
        $valid_keys = array_merge(
            $keys['auth']['req'],
            $keys['auth']['opt'],
            $keys['args']['req'],
            $keys['args']['opt']
        );

        foreach ($params as $k => $v) {
            if (!in_array($k, $valid_keys)) {
                unset($params[$k]);
            }
        }

        return $params;
    }
}
