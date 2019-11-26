#myallocator-pms-php

MyAllocator PMS PHP SDK (JSON & XML). Property management systems (PMS) can use this SDK to quickly and reliably integrate with the MyAllocator API to enable distribution for their customers.

Note, this is not the BuildToUs PHP SDK for OTA's. The BuildToUs PHP SDK can be found at https://github.com/MyAllocator/myallocator-ota-php

MyAllocator API Version: 201408

MyAllocator PHP SDK Documentation [http://myallocator.github.io/myallocator-pms-php-docs/]

MyAllocator API Documentation [http://myallocator.github.io/apidocs/]

MyAllocator API Integration Guide [https://docs.google.com/document/d/1_OuI0Z6rTkkuA9xxlJUvhXlazJ9w_iqsp1QzIj4gb2U/edit?usp=sharing]

MyAllocator [https://www.myallocator.com/]

MyAllocator Development Support [devhelp@myallocator.com]

## Requirements

PHP 5.3.2 and later.

## Documentation

Please see http://myallocator.github.io/myallocator-pms-php-docs/ for the complete and up-to-date SDK documentation.

## Composer

You can install via composer. Add the following to your project's `composer.json`.

    {
        "require": {
            "myallocator/myallocator-php-sdk": "1.*"
        }
    }

Then install via:

    composer.phar install

To use the bindings, either use Composer's autoload [https://getcomposer.org/doc/00-intro.md#autoloading]:

    require_once('vendor/autoload.php');

Or manually:

    require_once('/path/to/vendor/MyAllocator/myallocator-php-sdk/src/MyAllocator.php');

## Manual Installation

Grab the latest version of the SDK:

    git clone https://github.com/MyAllocator/myallocator-pms-php.git

To use the bindings, add the following to a PHP script:

    require_once('/path/to/myallocator-php-sdk/src/MyAllocator.php');

## Getting Started

#### Installation and usage example with composer installation:

    root@nate:/var/www# mkdir project
    root@nate:/var/www# cd project/
    root@nate:/var/www/project# echo '{"require": {"myallocator/myallocator-php-sdk": "1.*"}}' > composer.json
    root@nate:/var/www/project# composer install
    Loading composer repositories with package information
    Installing dependencies (including require-dev)
      - Installing myallocator/myallocator-php-sdk (1.2.1)
        Downloading: 100%         
    Writing lock file
    Generating autoload files
    root@nate:/var/www/project# cp vendor/myallocator/myallocator-php-sdk/src/example_autoload.php .
    root@nate:/var/www/project# v example_autoload.php 

Edit require_once autoload in example_autoload.php in line 33 to:

    require_once(dirname(__FILE__) . '/vendor/autoload.php');

Run HelloWorld ping to MyAllocator:

    root@nate:/var/www/project# php example_autoload.php
    {"Auth" : "true", hello" : "world", "_method" : "HelloWorld", "_version" : "201408"}

#### Installation and usage example with manual installation:

    root@nate:/var/www# mkdir -p project/lib
    root@nate:/var/www# cd project/lib/
    root@nate:/var/www/project/lib# git clone https://github.com/MyAllocator/myallocator-pms-php.git
    Cloning into 'myallocator-pms-php'...
    remote: Counting objects: 1133, done.
    remote: Compressing objects: 100% (6/6), done.
    remote: Total 1133 (delta 0), reused 0 (delta 0), pack-reused 1127
    Receiving objects: 100% (1133/1133), 186.80 KiB | 0 bytes/s, done.
    Resolving deltas: 100% (874/874), done.
    Checking connectivity... done.
    root@nate:/var/www/project/lib# cd ..
    root@nate:/var/www/project# cp lib/myallocator-pms-php/src/example_autoload.php .
    root@nate:/var/www/project# v example_autoload.php 

Edit require_once autoload in example_autoload.php in line 33 to:

    require_once(dirname(__FILE__) . '/lib/myallocator-pms-php/src/MyAllocator.php');

Run HelloWorld ping to MyAllocator:

    root@nate:/var/www/project# php example_autoload.php
    {"Auth" : "true", hello" : "world", "_method" : "HelloWorld", "_version" : "201408"}

#### Simple usage example:

Can be found at myallocator-pms-php/src/example_autoload.php. You may need to modify the autoload require path as shown above.

The setConfig is not required once `src/MyAllocator/Config/Config.php` has been configured.

## Configuration

The default configuration file can be found at at `src/MyAllocator/Config/Config.php`. The following is configurable:

#### paramValidationEnabled

The SDK supports parameter validation for array and json data formats, which can be configured via the `paramValidationEnabled` configuration in `src/MyAllocator/Config/Config.php`. If you prefer to send a raw request for performance, or other reasons, set this configuration to false. If parameter validation is enabled:

1.  Required and optional Api keys are defined via $keys array in each Api class.
2.  Top level required and optional keys are validated prior to sending a request to MyAllocator.
3.  An ApiException is thrown if a required key is not present.
4.  Top level keys not defined in $keys are stripped from parameters.
5.  Minimum optional parameters are enforced.

#### dataFormat

The SDK supports three data in/out formats (array, json, xml), which can be configured via the `dataFormat` configuration in `src/MyAllocator/Config/Config.php`. The following table illustrates the formats used for the request flow based on dataFormat.

    you->SDK(dataFormat)    SDK->MA     MA->SDK     SDK->you
    --------------------    -------     -------     --------
    array                   json        json        array
    json                    json        json        json
    xml                     xml         xml         xml

`array` and `json` data formats are preferred vs. `xml`.

Note, parameter validation only supports array and json data formats. For json data validation, the data must be decoded and re-encoded after validation. For xml data, the raw request is sent to MyAllocator and raw response returned to you. Disable `paramValidationEnabled` in Config.php to skip parameter validation.

#### dataResponse

Define what data you prefer to be included in Api responses. The response 'body', 'code', and 'headers' keys are not configurable and will always be included in a response. Each piece of data may be useful if you intend to store request and response data locally. The following keys in the dataResponse array below will cause the related data to be returned in all responses:

    1. timeRequest - The time immediately before the request is sent
        to MyAllocator (from Requestor). timeRequest is returned
        as a DateTime object.
    2. timeResponse - The time immediately after the response is
        received from MyAllocator (from Requestor). timeResponse is
        returned as a DateTime object.
    3. request - The exact request data sent from MyAllocator including
        authentication and provided parameters. The request is returned
        in the configured dataFormat format. Note, for xml, the request
        is stored in the result prior to url encoding.

#### debugsEnabled

Set `debugsEnabled` to true in `src/MyAllocator/Config/Config.php` to display request and response data in the SDK interface and API transfer data formats for an API request.

## API Response Format

A successful request call will return an array with the following response structure. By default, all key/values are returned. If you prefer to not receive request data or response['time'] in an Api response, you may configure the dataResponse array in `src/MyAllocator/Config/Config.php` to remove the data.

    return array(
        'request' => array(
            'time' => {DateTime Object},
            'body' => {Request body in dataFormat}
        ),
        'response' => array(
            'time' => {DateTime Object},
            'code' => {int},
            'headers' => {string},
            'body' => {Response body in dataFormat}      
        )
    );

`request['time']` *(optional)* is a DateTime object representing the time immediately before sending the request to MyAllocator.

`request['body']` *(optional)* is the request body sent to MyAllocator in your configured dataFormat.

`response['time']` *(optional)* is a DateTime object representing the time immediately after receiving the response from MyAllocator.

`response['code']` is the HTTP response code.

`response['headers']` are the HTTP response headers.

`response['body']` is the response body.

Requests may also return any of the exceptions defined in `src/MyAllocator/Exception/`. Be sure to wrap your API calls in try blocks. You may use the `getHttpStatus`, `getHttpBody`, and `getJsonBody` methods defined in `/Exception/MaException.php` within an exception block for information. Additionally, the `getState` method may be called for an exception to retrieve the state information for the request up to the point of failure in the same format as the response structure above (request/response). For example, if an HTTP connection timeout exception occurs, you may access the request time/body and response code/headers via `getState`.

## Tests

You can run phpunit tests from the top directory:

    Run common infra, JSON API, and XML API test cases. This excludes some of the advanced API's. Refer to `phpunit.xml`.
    vendor/bin/phpunit --debug

    Run JSON API test cases.
    vendor/bin/phpunit --debug tests/json

    Run XML API test cases.
    vendor/bin/phpunit --debug tests/xml

    Run common infra test cases.
    vendor/bin/phpunit --debug tests/common

Note, there is a different set of tests for json and XML.

The json tests use the `array` dataFormat to interface with the SDK. Refer to `src/MyAllocator/Config/Config.php`.

#### Setup Local Environment Variables

Most of the test cases use local environment variables and will be skipped if not provided. Export the following local environment variables from your data to use with the related test cases:

    myallocator-pms-php$ cat test/ENVIRONMENT_CREDENTIALS 
    #!/bin/bash
    export ma_vendorId=xxxxx
    export ma_vendorPassword=xxxxx
    export ma_userId=xxxxx
    export ma_userPassword=xxxxx
    export ma_userToken=xxxxx
    export ma_propertyId=xxxxx
    export ma_PMSUserId=xxxxx
    myallocator-pms-php$ source test/ENVIRONMENT_CREDENTIALS
