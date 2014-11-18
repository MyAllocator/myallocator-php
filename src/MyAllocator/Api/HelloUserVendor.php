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
use MyAllocator\phpsdk\Object\Auth;
use MyAllocator\phpsdk\Util\Requestor;
use MyAllocator\phpsdk\Util\Common;
use MyAllocator\phpsdk\Exception\ApiAuthenticationException;

class HelloUserVendor extends Api
{
    /**
     * @var array Array of required authentication keys (string) for API method.
     */
    protected $auth_keys = array(
        'Auth/VendorId', 
        'Auth/VendorPassword',
        'Auth/UserId', 
        'Auth/UserPassword'
    );

    /**
     * Say Hello! (Requires valid user and vendor credentials)
     *
     * @return string Server's response
     */
    public function sayHello()
    {
        $auth = $this->auth;

        if (!$auth) {
            $msg = 'No Auth object provided.  (HINT: Set your Auth data using '
                 . '"$API->setAuth(Auth $auth)" or $API\' constructor.  '
                 . 'See https://TODO for details.';
            throw new ApiAuthenticationException($msg);
        }

        $requestor = new Requestor($auth);
        $url = Common::get_class_name(get_class());
        $params = array(
            'hello' => 'world'
        );
        list($response, $auth) = $requestor->request('post', $url, $params, $this->auth_keys);
        $this->lastApiResponse = $response;
        return $response;
    }
}