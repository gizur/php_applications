<?php

/**
 * Yii Controller to handle REST queries
 *
 * Works with remote vtiger REST service
 * 
 * PHP version 5
 *
 * @category   Controller
 * @package    GizurCloud
 * @subpackage Controller
 * @author     Anshuk Kumar <anshuk.kumar@essindia.co.in>
 * 
 * @license    Gizur Private Licence
 * @link       http://api.gizur.com/api/index.php
 * 
 * */

/*
 * Including Amazon classes
 */

spl_autoload_unregister(array('YiiBase', 'autoload'));
Yii::import('application.vendors.*');
require_once 'aws-php-sdk/sdk.class.php';
spl_autoload_register(array('YiiBase', 'autoload'));

/**
 * Api Controller Class
 *
 * vTiger REST API: Certain new features has been added in vTiger REST API for 
 * Gizur REST API to work. Two files have been added namely:
 *    /lib/vtiger-5.4.0/include/Webservices/LoginCustomer.php
 *    /lib/vtiger-5.4.0/include/Webservices/RelateTroubleTicketDocument.php
 *
 * These two files provide the following additional webservice:
 * +---------------------------------+------+----------------------------------+
 * | Method                          | Type | Post Log In                      |
 * +---------------------------------+------+----------------------------------+
 * | logincustomer                   | POST | No                               |
 * | relatetroubleticketdocument     | POST | Yes                              |
 * | gettroubleticketdocumentfile    | GET  | Yes                              |
 * | getrelatedtroubleticketdocument | GET  | Yes                              |
 * | changepw                        | POST | Yes                              |
 * | resetpassword                   | POST | No                               |
 * +---------------------------------+------+----------------------------------+
 * 
 *
 * Traces / Logs: are present for each event like sending request and receiving
 * response etc. In addition to this a centralize logging system is also 
 * been implemented using a node.js server (hosted in Heroku cloud). For each 
 * request a unique id is generated and tagged in all logs which are made. This 
 * trace_id is also sent to the client in case of an error. One may use this id 
 * as follows
 *
 * $ cat runtime/application.log | grep [trace_id]
 *
 * config for Trace / Log can be both is present in Config/main.php
 *
 *
 * Response: is usually json, Gizur API acts as a thin wrapper around vTiger
 * REST API. Thus the json response is usual of the form:
 *     {success: true, result: [content]}
 * in case of error the response is as follows:
 *     {
 *         success: false, 
 *         error: 
 *             {
 *                 message: [message], 
 *                 code: [code], 
 *                 trace_id: [trace_id]
 *             }
 *     }
 *
 * Request:
 * 
 * Following is the format of the HTTP request which is to be followed
 * 
 * (GET|POST) /url/to/gizur/rest/api/$model/($id|$fieldname|$action|$category) 
 *      HTTP/1.1
 * Host: giruz.com
 * Http_x_username: $username
 * Http_x_password: $password
 * Http_x_timestamp: $timestamp
 * Http_x_gizurcloud_api_key: $GIZURCLOUD_API_KEY
 * Http_x_signature: $signature
 * Http_x_unique_salt: $unique_string
 * User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:12.0) 
 *      Gecko/20100101 Firefox/12.0
 * Accept: text/json
 * Accept-Language: sv,en-us,en;q=0.5
 * Connection: keep-alive
 * 
 * (parameter1=$parameter1&parameter2=$parameter2|)
 * 
 *
 * Caching: This API is designed to work with and without the cache. Cache 
 * configuration can be set in the Config/main.php.
 * 
 *
 * @category   Controller
 * @package    GizurCloud
 * @subpackage Controller
 * @author     Anshuk Kumar <anshuk.kumar@essindia.co.in>
 * 
 * @license    Gizur Private Licence
 * @link       https://api.gizur.com/api/index.php
 * 
 * */

class ApiController extends Controller
{

    /**
     * Version of API
     */
    const API_VERSION = "0.1";

    /**
     * Session between Gizur REST API and vTiger REST API
     */
    private $_session;

    /**
     * The Error Codes
     */
    private $_errors = Array(
        0 => "ERROR",
        1001 => "MANDATORY_FIELDS_MISSING",
        1002 => "INVALID_FIELD_VALUE",
        1003 => "TIME_NOT_IN_SYNC",
        1004 => "METHOD_NOT_ALLOWED",
        1005 => "MIME_TYPE_NOT_SUPPORTED",
        1006 => "INVALID_SESSIONID",
        2001 => "CLIENT_ID_INVALID",
        2002 => "EMAIL_INVALID",
        2003 => "LOGIN_INVALID",
        2004 => "WRONG_CREDENTIALS",
        2005 => "WRONG_FROM_CLIENT",
        2006 => 'INVALID_EMAIL'
    );

    /**
     * The vTiger REST Web Services Entities
     */
    private $_wsEntities = Array(
        'Documents' => 15,
        'Contacts' => 12
    );

    /**
     * List of valid models
     */
    private $_validModels = Array(
        'User',
        'HelpDesk',
        'Assets',
        'About',
        'DocumentAttachments',
        'DocumentAttachment',
        'Authenticate',
        'Cron',
        'Batches', // Batch Integration
        'Background' // Background Status
    );

    /**
     * Status Codes
     */
    private $_codes = Array(
        200 => 'OK',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
    );

    /**
     * Cache Key Used to store session in Cache
     */
    private $_cacheKey = "";
    
    /**
     * Trace ID
     */
    private $_traceId = "";  
    
    /**
     * vTiger Response
     */
    private $_vtresponse = "";       
    
    /**
     * Amazon Instance ID
     */
    private $_instanceid = ""; 
    
    /**
     * Client ID
     */
    private $_clientid = "";

    /**
     * Contact Info
     */
    private $_contactinfo = "";    
    
    /**
     * Db User
     */
    private $_dbuser = "";    
    
    /**
     * Db Password
     */
    private $_dbpassword = "";
    
    /**
     * Db Server
     */
    private $_dbhost = ""; 
    
    /**
     * Db name
     */
    private $_dbname = "";     
    
    /**
     * vTiger REST URL
     */
    private $_vtresturl = "";     

    /**
     * Filters executable action on permission basis
     * 
     * @return array action filters
     */
    public function filters()
    {
        return array();
    }

    /**
     * Dispatches response to the client
     * 
     * Sends the final response, if the response body is blank this sends an 
     * page with the status code of the error. This also sets the http status
     * code and the MIME type of the response.
     *
     * @param int    $status       Http status code which needs to be sent
     * @param string $body         The payload
     * @param string $content_type Mime type of payload
     * 
     * @return string message body
     */    
    private function _sendResponse($status = 200, $body = '', 
        $contentType = 'text/json'
    ) 
    {
        // set the status
        $statusHeader = 'HTTP/1.1 ' . 
            $status . ' ' . 
            ((isset($this->_codes[$status])) ? $this->_codes[$status] : '');
        header($statusHeader);

        // and the content type
        header('Content-type: ' . $contentType);
        header('Access-Control-Allow-Origin: *');

        // pages with body are easy
        if ($body != '') {
            // send the body
            echo $body;
        } else {
            $message = '';
            switch ($status) {
            case 401:
                $message = 'You must be authorized to view this page.';
                break;
            case 404:
                $message = 'The requested URL ' . $_SERVER['REQUEST_URI']
                            . ' was not found.';
                break;
            case 500:
                $message = 'The server encountered an error ' . 
                    'processing your request.';
                break;
            case 501:
                $message = 'The requested method is not implemented.';
                break;
            }

            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ?
                    $_SERVER['SERVER_SOFTWARE'] . ' Server at ' .
                    $_SERVER['SERVER_NAME'] . ' Port ' .
                    $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            // this should be templated in a real-world solution
            $body = '
            <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" ' . 
                '"http://www.w3.org/TR/html4/strict.dtd">
            <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; ' . 
                'charset=iso-8859-1">
                <title>' . $status . ' ' . ((isset($this->_codes[$status])) ?
                            $codes[$status] : '') . '</title>
            </head>
            <body>
                <h1>' . 
                ((isset($this->_codes[$status])) ? $codes[$status] : '') . 
                '</h1>
                <h2> Trace ID:' . $this->_traceId . '</h2>
                <p>' . $message . '</p>
                <hr />
                <address>' . $signature . '</address>
            </body>
            </html>';

            echo $body;
        }
        
        //Log
        Yii::log(
            "TRACE(" . $this->_traceId . "); FUNCTION(" . __FUNCTION__ . 
            "); DISPATCH RESPONSE: " . $body, CLogger::LEVEL_TRACE
        );
        
        Yii::app()->end();
    }
    
    /**
     * Yii callback executed before executing any action
     *
     * This fuctions is Yii's callback and is executed before executing any 
     * action of this controller class. Thus, making it the best place for
     * authenticating and validating a request.
     *
     * The validation of a request is broken in two parts: resource intensive
     * validation and non-resource intensive validation (generally slow).
     * We keep the non-resource intensive validation first to gain performance
     * and resource intensive validation later. Yet there is an exception to 
     * this as we have to break this validation in two more categories i.e. 
     * GIZURCLOUD_API_KEY validation first and username-password validations
     * later. This is because some Models are only expected to be validated for 
     * GIZURCLOUD_API_KEY and do not require username-password.
     * 
     * Caching: The vTiger session is 
     * stored in the cache to avoid repeated creation of session, if the cache 
     * is not present, a new vTiger REST API session will be created with each
     * request. The GIZURCLOUD_API_KEY and it's secret is also stored in the 
     * cache to preseve repeated calls to Amazon's Dynamo DB (faster than MySql 
     * but slower than Memcache).
     * 
     * @return whether any action should run
     */
    public function beforeAction($action)
    {

        try {
            //Will use this to tag all traces to associate to a request
            $this->_traceId = uniqid();
            
            //Log
            Yii::log(
                " TRACE(" . $this->_traceId . "); " . 
                " FUNCTION(" . __FUNCTION__ . "); " . 
                " RECEIVED REQUEST, STARTING VALIDATION " . 
                json_encode($_SERVER) .
                " GET VAR " . json_encode($_GET), 
                CLogger::LEVEL_TRACE
            );
            
            //First we validate the model
            if (!isset($_GET['model']))
                throw new Exception('Model not present');
            
            if (in_array($_GET['model'], $this->_validModels)===false)
                throw new Exception('Model not supported');            
            
            //First we validate the requests using logic do not consume
            //resources 
            if ($_GET['model'] == 'Batches') {
                return true;
            }
            
            if ($_GET['model'] == 'User' || $_GET['model'] == 'Background') {
                
                if(Yii::app()->request->isPostRequest && !isset($_GET['action']))
                    return true;
                //These models do not require authentication
                if(in_array(
                    $_GET['action'], array('login', 'logout', 'forgotpassword')
                ))
                    return true;
                
                if(empty($_SERVER['HTTP_X_USERNAME']))
                    throw new Exception("Credentials are invalid.", 2004);
                
                if(empty($_SERVER['HTTP_X_PASSWORD']))
                    throw new Exception("Credentials are invalid.", 2004);
                
                $clientID = $_SERVER['HTTP_X_USERNAME'];
                $password = $_SERVER['HTTP_X_PASSWORD'];
                               
                // Instantiate the class
                $dynamodb = new AmazonDynamoDB();
                $dynamodb->set_region(
                    constant(
                        "AmazonDynamoDB::" . 
                        Yii::app()->params->awsDynamoDBRegion
                    )
                );

                // Get an item
                $ddbResponse = $dynamodb->get_item(
                    array(
                        'TableName' => Yii::app()->params->awsDynamoDBTableName,
                        'Key' => $dynamodb->attributes(
                            array(
                                'HashKeyElement' => $clientID,
                            )
                        ),
                        'ConsistentRead' => 'true'
                    )
                );

                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " RECEIVED REQUEST, STARTING VALIDATION " . 
                    json_encode($ddbResponse->body->Item) .
                    " GET VAR " . json_encode($_GET), 
                    CLogger::LEVEL_TRACE
                );

                if(empty($ddbResponse->body->Item))
                    throw new Exception("Credentials are invalid.", 2004);

                $securitySalt = (string)$ddbResponse->body->Item->security_salt->{AmazonDynamoDB::TYPE_STRING};
                $hPassword = (string)$ddbResponse->body->Item->password->{AmazonDynamoDB::TYPE_STRING};

                $hSPassword = (string)hash("sha256", $password . $securitySalt);

                if($hSPassword !== $hPassword)
                    throw new Exception("Credentials are invalid.", 2004);
                    
                return true;
            }
            
            //Check Acceptable language of request
            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
                if (!is_null($_SERVER['HTTP_ACCEPT_LANGUAGE']))
                    if ($_SERVER['HTTP_ACCEPT_LANGUAGE'] != 'null')
                        if (strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'en') === false)
                            throw new Exception('Language not supported');
               
            //Check Acceptable mime-type of request    
            if (isset($_SERVER['HTTP_ACCEPT'])) {
                if (!is_null($_SERVER['HTTP_ACCEPT_LANGUAGE']))
                    if ($_SERVER['HTTP_ACCEPT_LANGUAGE'] != 'null')
                        if (strpos($_SERVER['HTTP_ACCEPT'], 'json')===false) {
                            if (!(
                                strpos($_SERVER['HTTP_ACCEPT'], 'html')!==false 
                                && $_GET['model'] == 'About'
                            ))
                                throw new Exception(
                                    'Mime-Type not supported', 1005
                                ); 
                        }
            }
    
            if (!isset($_SERVER['HTTP_X_CLIENTID'])) {        
                //Check if timestamp is present in the header
                //Part of API Key validation
                if (!isset($_SERVER['HTTP_X_TIMESTAMP']))
                throw new Exception('Timestamp not found in request');

                //Part of API Key validation
                //Check if signature is present in the header
                if (!isset($_SERVER['HTTP_X_TIMESTAMP']))
                if (!isset($_SERVER['HTTP_X_SIGNATURE']))
                throw new Exception('Signature not found');

                //Part of API Key validation
                //Check if Unique Salt is present in request
                if (!isset($_SERVER['HTTP_X_UNIQUE_SALT']))
                throw new Exception('Unique Salt not found');

                //Part of API Key validation
                //check if public key exists
                if (!isset($_SERVER['HTTP_X_GIZURCLOUD_API_KEY']))
                throw new Exception('Public Key Not Found in request');
                
                //Part of API Key validation
                //Check if request is in acceptable timestamp negative error
                $aTE = Yii::app()->params->acceptableTimestampError;
                $rTime = strtotime($_SERVER['HTTP_X_TIMESTAMP']);
                
                if ($_SERVER["REQUEST_TIME"] - $aTE > $rTime)
                throw new Exception('Stale request', 1003);

                //Part of API Key validation
                //Check if request is in acceptable timestamp positive error
                if ($_SERVER["REQUEST_TIME"] + $aTE < $rTime)
                throw new Exception(
                    'Oh, Oh, Oh, request from the FUTURE! ', 1003
                );

                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " VALIDATION (Fetch API Key details from Dynamodb, " .
                    "resource  intensive validation)", 
                    CLogger::LEVEL_TRACE
                );

                //Part of API Key validation
                //Fetch API Key details from Dynamodb, resource  
                //intensive validation
                $httpGAKey = $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'];
                $GIZURCLOUD_SECRET_KEY = Yii::app()->cache->get($httpGAKey);
                
                if ($GIZURCLOUD_SECRET_KEY === false) {
                    // Retreive Key pair from Amazon Dynamodb
                    $dynamodb = new AmazonDynamoDB();
                    $dynamodb->set_region(
                        constant(
                            "AmazonDynamoDB::" . 
                            Yii::app()->params->awsDynamoDBRegion
                        )
                    );

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " VALIDATION (Scan API KEY 1)", 
                        CLogger::LEVEL_TRACE
                    );
                    
                    //Scan for API KEYS
                    $ddbResponse = $dynamodb->scan(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'AttributesToGet' => array(
                                'id', 'apikey_1', 'secretkey_1', 
                                'clientid', 'databasename', 
                                'dbpassword', 'username', 'server', 'contactinfo'
                            ),
                            'ScanFilter' => array(
                                'apikey_1' => array(
                                    'ComparisonOperator' => AmazonDynamoDB::CONDITION_EQUAL,
                                    'AttributeValueList' => array(
                                        array(
                                            AmazonDynamoDB::TYPE_STRING => $_SERVER['HTTP_X_GIZURCLOUD_API_KEY']
                                        )
                                    )
                                )
                            )
                        )
                    );
                    
                    //If API Keys are not found for apikey_1 then look 
                    //in apikey_2
                    //can this be done in a better way?
                    if ($publicKeyNotFound = ($ddbResponse->body->Count == 0)) {
                        
                        //Log
                        Yii::log(
                            " TRACE(" . $this->_traceId . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " VALIDATION (Scan API KEY 2)", 
                            CLogger::LEVEL_TRACE
                        );                    
                        
                        //Scan for API KEYS
                        $ddbResponse = $dynamodb->scan(
                            array(
                                'TableName' => Yii::app()->params->awsDynamoDBTableName,
                                'AttributesToGet' => array(
                                    'id', 'apikey_2', 'secretkey_2', 'clientid',
                                    'databasename', 'dbpassword', 
                                    'username', 'server'
                                ),
                                'ScanFilter' => array(
                                    'apikey_2' => array(
                                        'ComparisonOperator' => AmazonDynamoDB::CONDITION_EQUAL,
                                        'AttributeValueList' => array(
                                            array(
                                                AmazonDynamoDB::TYPE_STRING => $_SERVER['HTTP_X_GIZURCLOUD_API_KEY']
                                            )
                                        )
                                    )
                                )
                            )
                        );
                        
                        //Check if public key is found in apikey_2
                        if (!($publicKeyNotFound = ($ddbResponse->body->Count == 0)))
                        $GIZURCLOUD_SECRET_KEY = (string) $ddbResponse->body->Items->secretkey_2->{AmazonDynamoDB::TYPE_STRING};
                    } else {
                        //Get secret key which belongs to apikey_1
                        $GIZURCLOUD_SECRET_KEY = (string) $ddbResponse->body->Items->secretkey_1->{AmazonDynamoDB::TYPE_STRING};
                    }

                    //If public key is not found throw an exception
                    if ($publicKeyNotFound)
                        throw new Exception('Could not identify public key'); 
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " VALIDATION (Client ID retrived)" . 
                        (string) $ddbResponse->body->Items->clientid->{AmazonDynamoDB::TYPE_STRING}, 
                        CLogger::LEVEL_TRACE
                    );                
                    
                    $this->_clientid = (string) $ddbResponse->body->Items->clientid->{AmazonDynamoDB::TYPE_STRING};

                    $this->_contactinfo = (string) $ddbResponse->body->Items->contactinfo->{AmazonDynamoDB::TYPE_STRING};                    
                    $this->_dbuser = (string) $ddbResponse->body->Items->username->{AmazonDynamoDB::TYPE_STRING};
                    $this->_dbpassword = (string) $ddbResponse->body->Items->dbpassword->{AmazonDynamoDB::TYPE_STRING};
                    $this->_dbhost = (string) $ddbResponse->body->Items->server->{AmazonDynamoDB::TYPE_STRING};
                    $this->_dbname = (string) $ddbResponse->body->Items->databasename->{AmazonDynamoDB::TYPE_STRING};
                        
                    //Store the public key and secret key combination 
                    //in cache to
                    //avoid repeated calls to Dynamo DB
                    Yii::app()->cache->set(
                        $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'], 
                        $GIZURCLOUD_SECRET_KEY
                    );
                    Yii::app()->cache->set(
                        $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] . "_clientid",
                        $this->_clientid
                    );
                    
                    Yii::app()->cache->set(
                        $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] . "_dbpassword", 
                        $this->_dbpassword
                    );
                    Yii::app()->cache->set(
                        $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] . "_dbhost", 
                        $this->_dbhost
                    );
                    Yii::app()->cache->set(
                        $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] . "_dbuser",
                        $this->_dbuser
                    );
                    Yii::app()->cache->set(
                        $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] . "_dbname", 
                        $this->_dbname
                    );
                    Yii::app()->cache->set(
                        $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] . "_contactinfo", 
                        $this->_contactinfo
                    );                    
                } else {
                    $this->_clientid = Yii::app()->cache->get(
                        $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] . "_clientid"
                    );
                    
                    $this->_dbhost = Yii::app()->cache->get(
                        $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] . "_dbhost"
                    );
                    $this->_dbname = Yii::app()->cache->get(
                        $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] . "_dbname"
                    );
                    $this->_dbuser = Yii::app()->cache->get(
                        $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] . "_dbuser"
                    );
                    $this->_dbpassword = Yii::app()->cache->get(
                        $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] . "_dbpassword"
                    );
                    $this->_contactinfo = Yii::app()->cache->get(
                        $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] . "_contactinfo"
                    );                    
                }
                
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " VALIDATION (Generating Signature)", 
                    CLogger::LEVEL_TRACE
                );

                // Build query arguments list
                $params = array(
                    'Verb' => Yii::App()->request->getRequestType(),
                    'Model' => $_GET['model'],
                    'Version' => self::API_VERSION,
                    'Timestamp' => $_SERVER['HTTP_X_TIMESTAMP'],
                    'KeyID' => $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'],
                    'UniqueSalt' => $_SERVER['HTTP_X_UNIQUE_SALT']
                );

                // Sorg arguments
                ksort($params);

                // Generate string for sign
                $stringToSign = "";
                foreach ($params as $k => $v)
                    $stringToSign .= "{$k}{$v}";

                // Generate signature
                $verifySignature = base64_encode(
                    hash_hmac(
                        'SHA256', $stringToSign, $GIZURCLOUD_SECRET_KEY, 1
                    )
                );
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " VALIDATION (Signature Dump) STRING_TO_SIGN: " . 
                    "$stringToSign" .
                    "    GENERATED SIGNATURE: " . $verifySignature .
                    "    SIGNATURE RECEIVED:" . $_SERVER['HTTP_X_SIGNATURE'], 
                    CLogger::LEVEL_TRACE
                );             

                //Verify if the signature is valid
                if ($_SERVER['HTTP_X_SIGNATURE'] != $verifySignature)
                    throw new Exception('Could not verify signature ');

                //Check if the signature has been used before
                //This is a security loop hole to reply attacks in case memcache
                //is not working
                if ($who = Yii::app()->cache->get($_SERVER['HTTP_X_SIGNATURE']) !== false) {
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        "    WHO USED THE SIGNATURE:" . $who, 
                        CLogger::LEVEL_TRACE
                    );             
                    throw new Exception('Used signature');
                }

                //Save the signature for 10 minutes            
                Yii::app()->cache->set(
                    $_SERVER['HTTP_X_SIGNATURE'], 
                    json_encode(
                        array(
                            "trace" => $this->_traceId,
                            "instance" => $this->_instanceid
                        )
                    ), 
                    600
                );
            } else {

                   
                $this->_clientid = $_SERVER['HTTP_X_CLIENTID'];

                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " VALIDATION (IN PRIVATE VAR) " . $this->_clientid, 
                    CLogger::LEVEL_TRACE
                );    
                
                //Check if clientid exists in dynamoDB
                if ( ($this->_dbuser = Yii::app()->cache->get($_SERVER['HTTP_X_CLIENTID'] . "_dbuser")) === false ) {
                    //Retreive Key pair from Amazon Dynamodb
                    $dynamodb = new AmazonDynamoDB();
                    $dynamodb->set_region(constant("AmazonDynamoDB::" . Yii::app()->params->awsDynamoDBRegion));

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " VALIDATION (Scan Client ID)", 
                        CLogger::LEVEL_TRACE
                    );
                    
                    //Scan for ClientID
                    $ddbResponse = $dynamodb->scan(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'AttributesToGet' => array(
                                'id', 'apikey_1', 'secretkey_1', 
                                'clientid', 'databasename', 'dbpassword', 
                                'username', 'server','key_free', 'contactinfo'
                            ),
                            'ScanFilter' => array(
                                'clientid' => array(
                                    'ComparisonOperator' => AmazonDynamoDB::CONDITION_EQUAL,
                                    'AttributeValueList' => array(
                                        array(AmazonDynamoDB::TYPE_STRING => $_SERVER['HTTP_X_CLIENTID'])
                                    )
                                )
                            )
                        )
                    );                                    
     
                    //Check if client id was found
                    if ($ddbResponse->body->Count != 0) {

                        if ((string) $ddbResponse->body->Items->key_free->{AmazonDynamoDB::TYPE_STRING} != 'true') 
                            throw new Exception(
                                'API Key free access is not ' . 
                                'allowed for this account'
                            );

                        $this->_contactinfo = (string) $ddbResponse->body->Items->contactinfo->{AmazonDynamoDB::TYPE_STRING};
                        $this->_dbuser = (string) $ddbResponse->body->Items->username->{AmazonDynamoDB::TYPE_STRING};
                        $this->_dbpassword = (string) $ddbResponse->body->Items->dbpassword->{AmazonDynamoDB::TYPE_STRING};
                        $this->_dbhost = (string) $ddbResponse->body->Items->server->{AmazonDynamoDB::TYPE_STRING};
                        $this->_dbname = (string) $ddbResponse->body->Items->databasename->{AmazonDynamoDB::TYPE_STRING};
                            
                        //Store the public key and secret key combination in cache to
                        //avoid repeated calls to Dynamo DB
                        Yii::app()->cache->set(
                            $_SERVER['HTTP_X_CLIENTID'] . "_dbpassword", 
                            $this->_dbpassword
                        );
                        Yii::app()->cache->set(
                            $_SERVER['HTTP_X_CLIENTID'] . "_dbhost", 
                            $this->_dbhost
                        );
                        Yii::app()->cache->set(
                            $_SERVER['HTTP_X_CLIENTID'] . "_dbuser", 
                            $this->_dbuser
                        );
                        Yii::app()->cache->set(
                            $_SERVER['HTTP_X_CLIENTID'] . "_dbname", 
                            $this->_dbname
                        );
                        Yii::app()->cache->set(
                            $_SERVER['HTTP_X_CLIENTID'] . "_contactinfo", 
                            $this->_contactinfo
                        );                        
                    } else {
                        throw new Exception(
                            'Client ID not found'
                        );                        
                    }
                } else {
                                          
                    $this->_dbhost = Yii::app()->cache->get(
                        $_SERVER['HTTP_X_CLIENTID'] . "_dbhost"
                    );
                    $this->_dbname = Yii::app()->cache->get(
                        $_SERVER['HTTP_X_CLIENTID'] . "_dbname"
                    );
                    $this->_dbpassword = Yii::app()->cache->get(
                        $_SERVER['HTTP_X_CLIENTID'] . "_dbpassword"
                    );
                    $this->_contactinfo = Yii::app()->cache->get(
                        $_SERVER['HTTP_X_CLIENTID'] . "_contactinfo"
                    );                    
                }
            }

            //Check the string
            $this->_vtresturl = str_replace(
                '{clientid}', $this->_clientid, Yii::app()->params->vtRestUrl
            );            

            //If request is for Model About or Cron stop validating
            if ($_GET['model'] == 'About' || $_GET['model'] == 'Cron')
                return true;

            //Check if Username is provided in header
            if (!isset($_SERVER['HTTP_X_USERNAME']))
                throw new Exception('Could not find enough credentials');

            //Incase of password reset stop validating request
            if ($_GET['model'] == 'Authenticate' && $_GET['action'] == 'reset')
                return true;

            //Check if the password is provided in the request
            if (!isset($_SERVER['HTTP_X_PASSWORD']))
                throw new Exception('Could not find enough credentials');
            
            //Get the instance ID of amazon
            $this->_instanceid = file_get_contents(
                "http://instance-data/latest/meta-data/instance-id"
            );

            //Create a cache key for saving session
            $this->_cacheKey = json_encode(
                array(
                    'clientid' => $this->_clientid,
                    'instanceid' => $this->_instanceid,
                    'username' => $_SERVER['HTTP_X_USERNAME'],
                    'password' => $_SERVER['HTTP_X_PASSWORD']
                )
            );

            $cacheValue = false;
            
            //Check if the session stored in the cache key is valid 
            //as per vtiger a session can be valid till 1 day max
            //and unused session for 1800 seconds
            $lastUsed = Yii::app()->cache->get(
                $this->_instanceid . "_last_used_" . $this->_cacheKey
            );

            if ($lastUsed !== false) {
                if ($lastUsed < (time() - 1790)) {
                    Yii::app()->cache->delete($this->_cacheKey);
                } else {
                    $cacheValue = Yii::app()->cache->get($this->_cacheKey);
                    Yii::app()->cache->set(
                        $this->_instanceid . "_last_used_" . $this->_cacheKey, 
                        time()
                    );
                }
            }

            //Log
            Yii::log(
                " TRACE(" . $this->_traceId . "); " . 
                " FUNCTION(" . __FUNCTION__ . "); " . 
                " VALIDATION (Logging into vtiger REST API" .
                " or using preused session)", 
                CLogger::LEVEL_TRACE
            );              
            
            //Check if session was retrived from memcache
            if ($cacheValue === false) {
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " VALIDATION (No value in cache found: Logging in)" .
                    " (sending POST request to vt url: " .
                    $this->_vtresturl .
                    "?operation=logincustomer " . "username=" . 
                    $_SERVER['HTTP_X_USERNAME'] .
                    "&password=" . $_SERVER['HTTP_X_PASSWORD'] .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                );                
                
                //Get the Access Key and the Username from vtiger REST 
                //service of the customer portal user's vtiger account
                $rest = new RESTClient();
                $rest->format('json');

                $rest->set_header(
                    'Content-Type', 'application/x-www-form-urlencoded'
                );
                $response = $rest->post(
                    $this->_vtresturl . "?operation=logincustomer", 
                    "username=" . $_SERVER['HTTP_X_USERNAME'] .
                    "&password=" . $_SERVER['HTTP_X_PASSWORD']
                );
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $response .
                    (string)($response == '' || $response == null) .
                    ")", 
                    CLogger::LEVEL_TRACE
                );         
                
                //Save vtiger response
                $this->_vtresponse = $response;                
                
                if ($response == '' || $response == null)
                    throw new Exception(
                        "Blank response received from vtiger: LoginCustomer"
                    );                

                //Objectify the response and check its success
                $response = json_decode($response);
                if ($response->success == false)
                    throw new Exception("Invalid Username and Password");

                //Store values from response
                $username = $response->result->user_name;
                $userAccessKey = $response->result->accesskey;
                $accountId = $response->result->accountId;
                $contactId = $response->result->contactId;
                $timeZone = $response->result->time_zone;
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " VALIDATION (sending GET request to vt url: " .
                    $this->_vtresturl .
                    "?operation=getchallenge&username=$username" .
                    ")", 
                    CLogger::LEVEL_TRACE
                );                

                $rest = new RESTClient();
                $rest->format('json');

                //Login using $username and $userAccessKey
                $response = $rest->get(
                    $this->_vtresturl .
                    "?operation=getchallenge&username=$username"
                );
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $response .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                ); 
                
                $this->_vtresponse = $response;
                
                if ($response == '' || $response == null)
                    throw new Exception(
                        "Blank response received from vtiger: GetChallenge"
                    );                 
                
                //Objectify the response and check its success
                $response = json_decode($response);
                if ($response->success == false)
                    throw new Exception("Unable to get challenge token");
                
                //Store values from response
                $challengeToken = $response->result->token;
                $generatedKey = md5($challengeToken . $userAccessKey);

                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (sending request to vt url: " . 
                    $this->_vtresturl .
                    "?operation=login".
                    "username=$username&accessKey=$generatedKey" .                            
                    ")", 
                    CLogger::LEVEL_TRACE
                ); 

                $rest = new RESTClient();
                $rest->format('json');

                //Login using the generated key
                $response = $rest->post(
                    $this->_vtresturl .
                    "?operation=login", 
                    "username=$username&accessKey=$generatedKey"
                );
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $response .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                ); 
                
                $this->_vtresponse = $response;
                
                if ($response == '' || $response == null)
                    throw new Exception(
                        "Blank response received from vtiger: Login"
                    );                
                
                //Objectify the response and check its success
                $response = json_decode($response);
                if ($response->success == false)
                    throw new Exception("Invalid generated key");
                
                //Store the values from response
                $this->_session->sessionName = $response->result->sessionName;
                $response->result->accountId = $accountId;
                $response->result->contactId = $contactId;
                $response->result->timeZone = $timeZone;

                //Get Contact and Account Name 
                //Build vtiger query to fetch contacts
                $query = "select * from Contacts" .
                        " where id = " . $contactId . ";";

                //urlencode to as its sent over http.
                $queryParam = urlencode($query);

                //creating query string
                $params = "sessionName={$this->_session->sessionName}" .
                        "&operation=query&query=$queryParam";

                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (sending GET request to vt url: " . 
                    $this->_vtresturl . "?$params" .                            
                    ")", 
                    CLogger::LEVEL_TRACE
                );               
                
                //sending request to vtiger REST Service 
                $rest = new RESTClient();
                $rest->format('json');
                $contact = $rest->get(
                    $this->_vtresturl . "?$params"
                );
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $contact .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                );  
                
                //Save vtiger response
                $this->_vtresponse = $contact;                
                
                if ($contact == '' || $contact == null)
                    throw new Exception(
                        "Blank response received from vtiger: Contact"
                    );                
                
                //Objectify the response and check its success
                $contact = json_decode($contact, true);
                
                if (!$contact['success']) 
                    throw new Exception($contact['error']['message']);
                
                //Store values from response
                $response->result->contactname 
                    = $contact['result'][0]['firstname'] .
                        " " . $contact['result'][0]['lastname'];

                //Build Query to fetch Accounts
                $query 
                    = "select accountname, account_no from Accounts" .
                        " where id = " .
                        $contact['result'][0]['account_id'] . ";";

                //urlencode to as its sent over http.
                $queryParam = urlencode($query);

                //creating query string
                $params 
                    = "sessionName={$this->_session->sessionName}" .
                        "&operation=query&query=$queryParam";
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (sending GET request to vt url: " . 
                    $this->_vtresturl . "?$params" .                            
                    ")", 
                    CLogger::LEVEL_TRACE
                );                 

                //sending request to vtiger REST Service 
                $rest = new RESTClient();
                $rest->format('json');
                $account 
                    = $rest->get($this->_vtresturl . "?$params");
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $account .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                );   
                
                //Save vtiger response
                $this->_vtresponse = $account;                
                
                if ($account == '' || $account == null)
                    throw new Exception(
                        "Blank response received from vtiger: Account"
                    );                
                
                //Objectify the response and check its success
                $account = json_decode($account, true);
                if (!$account['success']) {
                    throw new Exception($account['error']['message']);
                }
                
                //Store values from response
                $response->result->accountname 
                    = $account['result'][0]['accountname'];
                $response->result->account_no 
                    = $account['result'][0]['account_no'];
                $cacheValue = json_encode($response->result);

                //Save userid and session id against customerportal 
                //credentials
                Yii::app()->cache->set($this->_cacheKey, $cacheValue, 86000);
                Yii::app()->cache->set(
                    $this->_instanceid . "_last_used_" . $this->_cacheKey, 
                    time()
                );
            }

            //Log
            Yii::log(
                " TRACE(" . $this->_traceId . "); " . 
                " FUNCTION(" . __FUNCTION__ . "); " . 
                " VALIDATION (Storing session)", 
                CLogger::LEVEL_TRACE
            );            
            
            //Used the received value as session throug out in this session 
            $this->_session = json_decode($cacheValue);
            $this->_session->challengeToken = $challengeToken;
            
            //Yes the user is valid let him run the operation he requested
            return true;
            
        } catch (Exception $e) {
            
            //Log
            Yii::log(
                " TRACE(" . $this->_traceId . "); " . 
                " FUNCTION(" . __FUNCTION__ . "); " . 
                " VALIDATION (Some error occured during " .
                "validation/Authentication)" . 
                " ERROR ( " . $e->getMessage() . ") ", 
                CLogger::LEVEL_TRACE
            );       
                       
            //Check if Model is Not About
            if ($_GET['model'] != 'About') {
                
                //Create an error response based on the exception thrown
                $response = new stdClass();
                $response->success = false;
                $response->error->code = $this->_errors[$e->getCode()];
                $response->error->message = $e->getMessage();
                $response->error->trace_id = $this->_traceId;
                $response->error->instance_id = $this->_instanceid;
                $response->error->vtresponse = $this->_vtresponse;

                //Check if the error code is TIME_NOT_IN_SYNC
                //if so send time delta
                if ($e->getCode() == 1003) {
                    if (isset($_SERVER['HTTP_X_TIMESTAMP'])) {
                        $reqTime = strtotime($_SERVER['HTTP_X_TIMESTAMP']);
                        $response->error->time_difference
                            = $_SERVER['REQUEST_TIME'] - $reqTime;
                    }
                    
                    $response->error->time_request_arrived 
                        = date("c", $_SERVER['REQUEST_TIME']);
                    
                    $response->error->time_request_sent 
                        = date("c", strtotime($_SERVER['HTTP_X_TIMESTAMP']));
                    
                    $response->error->time_server = date("c");
                }

                //Send the response with status code 403 Forbidden
                $this->_sendResponse(403, json_encode($response));
            } else {
                
                //Check if the MIME-TYPE is correct
                if ($e->getCode() == 1005) {
                    $response = new stdClass();
                    $response->success = false;
                    $response->error->code = $this->_errors[$e->getCode()];
                    $response->error->message = $e->getMessage();
                    $response->error->trace_id = $this->_traceId;
                    $response->error->instance_id = $this->_instanceid;
                    $response->error->vtresponse = $this->_vtresponse;
                    $this->_sendResponse(403, json_encode($response));
                } else {
                    
                    //Send about message in case the signature is not validated
                    $this->_sendResponse(
                        403, 'An account needs to setup in order to use ' .
                        'this service. Please contact ' .
                        '<a href="mailto://sales@gizur.com">sales@gizur.com' .
                        '</a> ' .
                        'in order to setup an account.',
                        'text/html'
                    );
                }
            }
            
            //User is not valid stop processing
            //This part of code is never reached though because _sendResponse
            //has a die statement
            return false;
        }
    }

    /**
     * Action for List various models
     *
     * This action handels the following models
     * - About
     *       Request Method: GET
     *       Response Type : json/html based on Accept HTTP Header 
     * - HelpDesk
     *       Request Method: GET
     *       Response Type : json
     *       Subactions    : 
     *           (
     *               $fieldname | 
     *               $category  | 
     *               $category/$year/$month/$trailerid/$reportdamage
     *           )
     *       Notes: $category can take three values inoperation|damaged|all; 
     *       $fieldname can take any value; $year, $month are numeric and if 
     *       they are 0000 or 00 respectively no filter is added for the same.
     *       $trailerid is the serial number of the trailer and can take any 
     *       value if it is 0 no filter will be added for it; $reportdamage can 
     *       take three value yes|no|all.
     * - Assets
     *       Request Method: GET
     *       Response Type : json
     * - Authenticate
     *       Request Method: POST
     *       Response Type : json
     *       Subactions    : (login|logout)
     *
     * The usual response is in json format, except for About model requests 
     * which may respond in both html and json based  on the accept mime type
     * specified in the header.
     * 
     * @return appropriate list
     */
    public function actionList()
    {

        //Log
        Yii::log(
            " TRACE(" . $this->_traceId . "); " . 
            " FUNCTION(" . __FUNCTION__ . "); " . 
            " PROCESSING REQUEST (" . json_encode($_GET) . ")", 
            CLogger::LEVEL_TRACE
        );
        
        //Tasks include Listing of Troubleticket, Picklists, Assets
        try {
            switch ($_GET['model']) {
            /*
             * *****************************************************************
             * *****************************************************************
             * * ABOUT MODEL
             * *****************************************************************
             * *****************************************************************
             */                
            case 'About':
                $body = 'This mobile app was built using';
                $body .= ' <a href="gizur.com">gizur.com</a> services.';  
                
                if (strpos($_SERVER['HTTP_ACCEPT'], 'json')!==false) {
                    $response = new stdClass();
                    $response->success = true;
                    $response->result = $body;
                    
                    $this->_sendResponse(200, json_encode($response));
                } elseif (strpos($_SERVER['HTTP_ACCEPT'], 'html')!==false) {
                    //add html tags
                    $body = "<html><body>$body</body></html>";
                    
                    $this->_sendResponse(200, $body, 'text/html');
                }                
                
                break;

                            
            case 'Batches':
                
                // Instantiate the class
                $dynamodb = new AmazonDynamoDB();
                $dynamodb->set_region(
                    constant(
                        "AmazonDynamoDB::" . 
                        Yii::app()->params->awsDynamoDBRegion
                    )
                );
                //Get all the batches
                $ddbResponse = $dynamodb->scan(
                    array(
                        'TableName' => Yii::app()->params->awsBatchDynamoDBTableName
                    )
                );

                $result = array();
                $x = 0;
                foreach ($ddbResponse->body->Items
                as $key => $item) {
                    $item = get_object_vars($item);
                    foreach ($item as $k => $v) {
                        $v = get_object_vars($v);
                        $result[$x][$k] = $v[AmazonDynamoDB::TYPE_STRING];
                    }
                    $x++;
                }
                $response = new stdClass();
                $response->success = true;
                $response->result = $result;

                $this->_sendResponse(200, json_encode($response));
                break;
            /*
             * *****************************************************************
             * *****************************************************************
             * * USER AUTHENTICATE MODEL
             * * Accepts two actions login and logout
             * *****************************************************************
             * *****************************************************************
             */
            case 'User':               
                
                if ($_GET['action'] == 'login') {
                    
                    $post = json_decode(file_get_contents('php://input'), true);
                    $clientID = $post['id'];
                    $password = $post['password'];
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST : User/login ($clientID)", 
                        CLogger::LEVEL_TRACE
                    );
                    
                    // Instantiate the class
                    $dynamodb = new AmazonDynamoDB();
                    $dynamodb->set_region(
                        constant(
                            "AmazonDynamoDB::" . 
                            Yii::app()->params->awsDynamoDBRegion
                        )
                    );

                    // Get an item
                    $ddbResponse = $dynamodb->get_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'Key' => $dynamodb->attributes(
                                array(
                                    'HashKeyElement' => $clientID,
                                )
                            ),
                            'ConsistentRead' => 'true'
                        )
                    );
                    
                    if(empty($ddbResponse->body->Item))
                        throw new Exception(
                            "Login Id / password incorrect.", 2003
                        );
                    
                    $status = (string)$ddbResponse->body->Item->status->{AmazonDynamoDB::TYPE_STRING};
                    
                    if($status != 'Active')
                        throw new Exception(
                            "Login Id / password incorrect.", 2003
                        );
                        
                    $securitySalt = (string)$ddbResponse->body->Item->security_salt->{AmazonDynamoDB::TYPE_STRING};
                    $hPassword = (string)$ddbResponse->body->Item->password->{AmazonDynamoDB::TYPE_STRING};
                    
                    $hSPassword = (string)hash(
                        "sha256", $password . $securitySalt
                    );
                    
                    if($hSPassword !== $hPassword)
                        throw new Exception(
                            "Login Id / password incorrect.", 2003
                        );
                    //Return response to client
                    $response = new stdClass();
                    $response->success = true;
                    $response->contactname = $this->_session->contactname;
                    $response->accountname = $this->_session->accountname;
                    $response->account_no = $this->_session->account_no;

                    //Send response
                    $this->_sendResponse(200, json_encode($response));
                }
                
                if ($_GET['action'] == 'forgotpassword') {
           
                    $post = json_decode(file_get_contents('php://input'), true);
                    $clientID = $post['id'];
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST : User/forgotpassword ($clientID)",
                        CLogger::LEVEL_TRACE
                    );
                    
                    // Get an item
                    $dynamodb = new AmazonDynamoDB();
                    $dynamodb->set_region(
                        constant(
                            "AmazonDynamoDB::" . 
                            Yii::app()->params->awsDynamoDBRegion
                        )
                    );
                    
                    $ddbResponse = $dynamodb->get_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'Key' => $dynamodb->attributes(
                                array(
                                    'HashKeyElement' => $clientID,
                                )
                            ),
                            'ConsistentRead' => 'true'
                        )
                    );
                    
                    if(empty($ddbResponse->body->Item))
                        throw new Exception("Invalid Login Id.", 2001);
                    
                    $securitySalt = (string)$ddbResponse->body->Item->security_salt->{AmazonDynamoDB::TYPE_STRING};
                                        
                    $password = substr(uniqid("", true), 0, 7);
                    $newHashedPassword = (string)hash(
                        "sha256", $password . $securitySalt
                    );
                    
                    $result = array();
                    foreach ($ddbResponse->body->Item->children()
                    as $key => $item) {
                        $result[$key] 
                            = (string) $item->{AmazonDynamoDB::TYPE_STRING};
                    }
                    
                    $result['password'] = $newHashedPassword;
                    
                    // Update the password
                    $ddbResponse = $dynamodb->put_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                             'Item' => $dynamodb->attributes($result)
                        )
                    );

                    if ($ddbResponse->isOK()) {
                        //SEND THE EMAIL TO USER
                        $email = new AmazonSES();
                        //$email->set_region(constant("AmazonSES::" . 
                        //Yii::app()->params->awsSESRegion));
                        $sesResponse = $email->send_email(
                            // Source (aka From)
                            Yii::app()->params->awsSESFromEmailAddress,
                            array(
                                'ToAddresses' => array(// Destination (aka To)
                                    $result['id']
                                )
                            ), 
                            array(// sesMessage (short form)
                                'Subject.Data' => 'Gizur SaaS',
                                'Body.Text.Data' => 'Hi ' . $result['name_1'] . 
                                ' ' . $result['name_2'] . ', ' . PHP_EOL .
                                PHP_EOL .
                                'Welcome to Gizur SaaS.' . PHP_EOL . PHP_EOL .
                                'Your username and password has been ' .
                                'updated and are as follows:' . PHP_EOL .
                                PHP_EOL .
                                'Portal Link: ' . Yii::app()->params->serverProtocol
                                . "://"
                                . $_SERVER['HTTP_HOST'] .  
                                PHP_EOL .
                                'Username: ' . $result['id']  . PHP_EOL .                            
                                'Password: ' . $password . PHP_EOL .
                                PHP_EOL .
                                PHP_EOL .
                                '--' .
                                PHP_EOL .
                                'Gizur Admin'
                            )
                        );

                        $response = new stdClass();
                        $response->success = $ddbResponse->isOK();
                        $this->_sendResponse(200, json_encode($response));
                    } else {
                        $response->success = false;
                        $response->error->code = "ERROR";
                        $response->error->message = "Problem reseting " .
                            "the password.";
                        $response->error->trace_id = $this->_traceId;
                        $this->_sendResponse(400, json_encode($response));
                    }
                }
                break;
            /*
             * *****************************************************************
             * *****************************************************************
             * * AUTHENTICATE MODEL
             * * Accepts two actions login and logout
             * *****************************************************************
             * *****************************************************************
             */
            case 'Authenticate':
                if ($_GET['action'] == 'login') {
                    
                    //Return response to client
                    $response = new stdClass();
                    $response->success = true;
                    $response->contactname = $this->_session->contactname;
                    $response->accountname = $this->_session->accountname;
                    $response->account_no = $this->_session->account_no;
                    $response->timeZone = $this->_session->timeZone;
                    $response->contactinfo = $this->_contactinfo;

                    //Send response
                    $this->_sendResponse(200, json_encode($response));
                }

                if ($_GET['action'] == 'logout') {
           
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending request to vt url: " . 
                        $this->_vtresturl .
                        "?operation=logout&sessionName=" . 
                        "{$this->_session->sessionName}" .                            
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    //Logout using {$this->_session->sessionName}
                    $rest = new RESTClient();
                    $rest->format('json');
                    $response = $rest->get(
                        $this->_vtresturl .
                        "?operation=logout&sessionName=" . 
                        "{$this->_session->sessionName}"
                    );
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $response .                          
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                      
                    
                    //Objectify the response and check its success
                    $response = json_decode($response);
                    
                    if ($response->success == false)
                        throw new Exception("Unable to Logout");

                    //send response to client
                    $response = new stdClass();
                    $response->success = true;
                    $this->_sendResponse(200, json_encode($response));
                }
                break;
            /*
             * *****************************************************************
             * *****************************************************************
             * * HelpDesk MODEL
             * * Accepts fieldnames and categories (inoperation|damaged)
             * *****************************************************************
             * *****************************************************************
             */
            case 'HelpDesk':
                
                //Is this a request for picklist?
                if (isset($_GET['fieldname'])) {
                    
                    $cachedValue = Yii::app()->cache->get(
                        $this->_clientid . 
                        '_picklist_'
                        . $_GET['model'] . '_'
                        . $_GET['fieldname']
                    );
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (Getting Value from cache for " . 
                        $this->_clientid . 
                        '_picklist_'
                        . $_GET['model'] . '_' 
                        . $_GET['fieldname'] . ' : ' 
                        . (string)$cachedValue .
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                    

                    if ($cachedValue === false) {
                        
                        //flip custome fields array
                        $flippedCustomFields 
                            = array_flip(Yii::app()->params[$this->_clientid . 
                                '_custom_fields'][$_GET['model']]);
                        
                        //Check if the requested field name is a vtiger
                        //custom field
                        if (in_array($_GET['fieldname'], $flippedCustomFields)) {
                            $fieldname 
                                = Yii::app()->params[$this->_clientid .
                                    '_custom_fields'][$_GET['model']][$_GET['fieldname']];
                        } else {
                            $fieldname = $_GET['fieldname'];
                        }
                        
                        //Receive response from vtiger REST service
                        //Return response to client 
                        $params = "sessionName={$this->_session->sessionName}" .
                                "&operation=describe" .
                                "&elementType=" . $_GET['model'];
                        
                        //Log
                        Yii::log(
                            " TRACE(" . $this->_traceId . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " PROCESSING REQUEST (sending GET " .
                            "request to vt url: " . 
                            $this->_vtresturl . "?$params" .                            
                            ")", 
                            CLogger::LEVEL_TRACE
                        );                        
                        
                        //Send request to vtiger
                        $rest = new RESTClient();
                        $rest->format('json');
                        $response = $rest->get(
                            $this->_vtresturl . "?$params"
                        );

                        //Log
                        Yii::log(
                            " TRACE(" . $this->_traceId . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " PROCESSING REQUEST (response received: " . 
                            $response .                          
                            ")", 
                            CLogger::LEVEL_TRACE
                        ); 
                        
                        //Save vtiger response
                        $this->_vtresponse = $response;                

                        if ($response == '' || $response == null)
                            throw new Exception(
                                "Blank response received from vtiger: Picklist"
                            );                        
                        
                        //Objectify the response and check its success
                        $response = json_decode($response, true);

                        if ($response['success'] == false)
                            throw new Exception('Fetching details failed');

                        $picklist = '';
                        $foundPicklist = false;
                        $notPicklist = false;
                        
                        //Find the appropriate field whose label value needs to
                        //be sent  
                        foreach ($response['result']['fields'] as $field) {
                            
                            //Check if the field is a picklist
                            if ($field['type']['name'] == 'picklist') {

                                //Loop through all values of the pick list
                                foreach ($field['type']['picklistValues'] 
                                    as &$option)

                                //Check if there is a dependency setup
                                //for the picklist value
                                if (isset($option['dependency'])) {

                                    foreach ($option['dependency'] 
                                        as $depFieldname => $dependency) {
                                        if (in_array(
                                            $depFieldname, 
                                            Yii::app()->params[$this->_clientid .
                                                '_custom_fields']['HelpDesk']
                                        )) {
                                                $newFieldname = 
                                                    $flippedCustomFields[$depFieldname];
                                                $option['dependency'][$newFieldname] = 
                                                    $option['dependency'][$depFieldname];
                                                unset($option['dependency'][$depFieldname]);
                                        }
                                    }
                                }

                                //Create response to be sent in proper
                                //format
                                $content = json_encode(
                                    array(
                                        'success' => true,
                                        'result' =>
                                        $field['type']['picklistValues']
                                    )
                                );
                                
                                if (!isset(
                                    $flippedCustomFields[$field['name']]
                                ))
                                    $flippedCustomFields[$field['name']] 
                                        = $field['name'];                                
                                
                                //Log
                                Yii::log(
                                    " TRACE(" . $this->_traceId . "); " . 
                                    " FUNCTION(" . __FUNCTION__ . "); " . 
                                    " PROCESSING REQUEST (Setting Value to " .
                                    "cache for " . 
                                    'picklist_'
                                    . $_GET['model']
                                    . '_'
                                    . $flippedCustomFields[$field['name']] . 
                                    ' : ' 
                                    . (string)$content .
                                    ")", 
                                    CLogger::LEVEL_TRACE
                                ); 

                                //Save the response in cache
                                Yii::app()->cache->set(
                                    $this->_clientid .
                                    '_picklist_'
                                    . $_GET['model']
                                    . '_'
                                    . $flippedCustomFields[$field['name']],
                                    $content
                                );
                                
                                if ($fieldname == $field['name']) {
                                    $foundPicklist = true;
                                    $picklist = $content;
                                }

                            } else {
                                
                                if ($fieldname == $field['name']) {
                                    $notPicklist = true;
                                }                                
                                
                            }
                            
                        }
                        
                        if ($foundPicklist) {
                            
                            //Dispatch the response
                            $this->_sendResponse(200, $picklist);                           
                            
                        }                        
                        
                        if ($notPicklist)
                            throw new Exception("Not an picklist field");
                        
                        if ($notPicklist == false and $foundPicklist == false )
                            throw new Exception("Fieldname not found");
                        
                    } else {
                        
                        //Log
                        Yii::log(
                            " TRACE(" . $this->_traceId . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " PROCESSING REQUEST ( FROM CACHE " . 
                            $cachedValue .                            
                            ")", 
                            CLogger::LEVEL_TRACE
                        );                        
                        
                        //Send cached response
                        $this->_sendResponse(200, $cachedValue);
                    }
                }

                //Is this a request for listing categories
                if (isset($_GET['category'])) {

                    //Send request to vtiger REST service
                    $query = "select * from " . $_GET['model'];

                    //creating where clause based on parameters
                    $whereClause = Array();
                    if ($_GET['category'] == 'inoperation') {
                        $whereClause[] = "ticketstatus = 'Closed'";
                    }
                    if ($_GET['category'] == 'damaged') {
                        $whereClause[] = "ticketstatus = 'Open'";
                    }

                    if (isset($_GET['reportdamage']))
                    if ($_GET['reportdamage'] != 'all') {
                        $whereClause[] = Yii::app()->params[$this->_clientid . '_custom_fields'][$_GET['model']]['reportdamage'] . 
                            " = '" . ucwords($_GET['reportdamage']) . "'";
                    }

                    //Adding date range filter
                    if (isset($_GET['year']) && isset($_GET['month'])) {
                        if ($_GET['year'] != '0000') {
                            if ($_GET['month'] == '00') {
                                $startmonth = '01';
                                $endmonth = '12';
                            } else {
                                $startmonth = $_GET['month'];
                                $endmonth = $_GET['month'];
                            }
                            if (!checkdate($startmonth, "01", $_GET['year']))
                                throw new Exception(
                                    "Invalid month specified in list criteria"
                                );
                            $whereClause[] 
                                = "createdtime >= '" .
                                    $_GET['year'] . "-" . $startmonth . "-01'";
                            $whereClause[] 
                                = "createdtime <= '" .
                                    $_GET['year'] . "-" . $endmonth . "-31'";
                        }
                    }

                    //Adding trailer filter
                    if (isset($_GET['trailerid'])) {
                        if ($_GET['trailerid'] != '0')
                            $whereClause[] = Yii::app()->params[$this->_clientid . '_custom_fields']
                                ['HelpDesk']['trailerid'] .
                                " = '" . $_GET['trailerid'] . "'";
                    }

                    //Attaching where clause to filter
                    if (count($whereClause) != 0)
                        $query = $query . " where " .
                                implode(" and ", $whereClause);
                    
                    //Terminating the query
                    $query = $query . ";";

                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);

                    //creating query string
                    $params = "sessionName={$this->_session->sessionName}" .
                            "&operation=query&query=$queryParam";
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending GET request to vt url: " .
                        $this->_vtresturl . "?$params" .
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                     

                    //Receive response from vtiger REST service
                    //Return response to client  
                    $rest = new RESTClient();
                
                    $rest->format('json');
                    $response = $rest->get(
                        $this->_vtresturl . "?$params"
                    );
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $response .                          
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    //Objectify the response and check its success
                    $response = json_decode($response, true);

                    if ($response['success'] == false)
                        throw new Exception('Fetching details failed');

                    //Get Accounts List
                    $query = "select * from Accounts;";
                 
                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);

                    //creating query string
                    $params = "sessionName={$this->_session->sessionName}" .
                            "&operation=query&query=$queryParam";
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending GET request to vt url: " .
                        $this->_vtresturl . "?$params" .    
                        ")", 
                        CLogger::LEVEL_TRACE
                    );

                    //Receive response from vtiger REST service
                    //Return response to client  
                    $rest = new RESTClient();
                
                    $rest->format('json');
                    $accounts = $rest->get(
                        $this->_vtresturl . "?$params"
                    );
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $accounts .
                        ")", 
                        CLogger::LEVEL_TRACE
                    );
                    
                    //Objectify the response and check its success
                    $accounts = json_decode($accounts, true);
                    
                    if ($accounts['success'] == true) {
                        $tmpAccounts = array();
                        if (isset($accounts['result']))
                            foreach ($accounts['result'] as $account)
                                $tmpAccounts[$account['id']] = $account['accountname'];
                    }


                    //Get Contact List
                    $query = "select * from Contacts;";
                    
                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);

                    //creating query string
                    $params = "sessionName={$this->_session->sessionName}" .
                            "&operation=query&query=$queryParam";
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending GET request to vt url: " .
                        $this->_vtresturl . "?$params" .
                        ")", 
                        CLogger::LEVEL_TRACE
                    );

                    //Receive response from vtiger REST service
                    //Return response to client  
                    $rest = new RESTClient();
                
                    $rest->format('json');
                    $contacts = $rest->get(
                        $this->_vtresturl . "?$params"
                    );
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $contacts .                          
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    //Objectify the response and check its success
                    $contacts = json_decode($contacts, true);
                    if ($contacts['success'] == true) {
                        $tmpContacts = array();
                        if (isset($contacts['result']))
                        foreach ($contacts['result'] as $contact) {
                            $tmpContacts[$contact['id']]['contactname'] = 
                                $contact['firstname'] . ' ' . 
                                $contact['lastname'];
                            $tmpContacts[$contact['id']]['accountname'] = 
                                $tmpAccounts[$contact['account_id']];
                        }
                    }

                    //Before sending response santise custom fields names to 
                    //human readable field names
                    $customFields = Yii::app()->params[$this->_clientid . 
                        '_custom_fields']['HelpDesk'];

                    foreach ($response['result'] as &$troubleticket) {
                        unset($troubleticket['update_log']);
                        unset($troubleticket['hours']);
                        unset($troubleticket['days']);
                        unset($troubleticket['modifiedtime']);
                        unset($troubleticket['from_portal']);
                        if (isset($tmpContacts)) {
                            if (isset($tmpContacts[$troubleticket['parent_id']])) {
                                $troubleticket['contactname'] = 
                                    $tmpContacts[$troubleticket['parent_id']]['contactname'];
                                $troubleticket['accountname'] = 
                                    $tmpContacts[$troubleticket['parent_id']]['accountname'];
                            } else {
                                $troubleticket['contactname'] = '';
                                $troubleticket['accountname'] = '';
                            }
                        }
                        foreach ($troubleticket as $fieldname => $value) {
                            $keyToReplace = array_search(
                                $fieldname, $customFields
                            );
                            if ($keyToReplace) {
                                unset($troubleticket[$fieldname]);
                                $troubleticket[$keyToReplace] = $value;
                                //unset($customFields[$keyToReplace]);
                            }
                        }
                    }

                    //Send response
                    $this->_sendResponse(200, json_encode($response));
                }
                break;
            /*
             * *****************************************************************
             * *****************************************************************
             * * Assets MODEL
             * * Accepts fieldnames 
             * *****************************************************************
             * *****************************************************************
             */
            case 'Assets':
                
                if (isset($_GET['fieldname'])) {

                    $cachedValue = Yii::app()->cache->get(
                        $this->_clientid .
                        '_picklist_'
                        . $_GET['model'] . '_'
                        . $_GET['fieldname']
                    );

                    if ($cachedValue === false) {
                        
                        //flip custome fields array
                        $flippedCustomFields 
                            = array_flip(Yii::app()->params[$this->_clientid .
                                '_custom_fields']['Assets']);
                        
                        //Check if the requested field name is a vtiger
                        //custom field
                        if (in_array($_GET['fieldname'], $flippedCustomFields)) {
                            $fieldname 
                                = Yii::app()->params[$this->_clientid . 
                                    '_custom_fields'][$_GET['model']][$_GET['fieldname']];
                        } else {
                            $fieldname = $_GET['fieldname'];
                        }
                        
                        //Receive response from vtiger REST service
                        //Return response to client 
                        $params = "sessionName={$this->_session->sessionName}" .
                                "&operation=describe" .
                                "&elementType=" . $_GET['model'];
                        
                        //Log
                        Yii::log(
                            " TRACE(" . $this->_traceId . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " .
                            " PROCESSING REQUEST (sending GET request " .
                            "to vt url: " . 
                            $this->_vtresturl . "?$params" .
                            ")", 
                            CLogger::LEVEL_TRACE
                        );
                        
                        //Send request to vtiger
                        $rest = new RESTClient();
                
                        $rest->format('json');
                        $response = $rest->get(
                            $this->_vtresturl . "?$params"
                        );

                        //Log
                        Yii::log(
                            " TRACE(" . $this->_traceId . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " PROCESSING REQUEST (response received: " . 
                            $response .
                            ")",
                            CLogger::LEVEL_TRACE
                        );
                        
                        //Save vtiger response
                        $this->_vtresponse = $response;

                        if ($response == '' || $response == null)
                            throw new Exception("Blank response received from" .
                                " vtiger: Asset Picklist");
                        
                        //Objectify the response and check its success
                        $response = json_decode($response, true);

                        if ($response['success'] == false)
                            throw new Exception('Fetching details failed');

                        //Find the appropriate field whose label value needs to
                        //be sent  
                        foreach ($response['result']['fields'] as $field) {
                            
                            if ($fieldname == $field['name']) {
                                
                                //Check if the field is a picklist
                                if ($field['type']['name'] == 'picklist') {
                                    
                                    //Loop through all values of the pick list
                                    foreach ($field['type']['picklistValues'] as
                                        &$option)
                                        
                                    //Check if there is a dependency setup
                                    //for the picklist value
                                    if (isset($option['dependency'])) {
                                        
                                        foreach ($option['dependency'] as $depFieldname => $dependency) {
                                            if (in_array($depFieldname, Yii::app()->params[$this->_clientid . '_custom_fields']['Assets'])) {
                                                $newFieldname = $flippedCustomFields[$depFieldname];
                                                $option['dependency'][$newFieldname] = $option['dependency'][$depFieldname];
                                                unset($option['dependency'][$depFieldname]);
                                            }
                                        }
                                    }
                                    
                                    //Create response to be sent in proper
                                    //format
                                    $content = json_encode(
                                        array(
                                            'success' => true,
                                            'result' =>
                                            $field['type']['picklistValues']
                                        )
                                    );
                                    
                                    //Save the response in cache
                                    Yii::app()->cache->set(
                                        $this->_clientid .
                                        '_picklist_'
                                        . $_GET['model']
                                        . '_'
                                        . $_GET['fieldname'], $content
                                    );
                                    
                                    //Dispatch the response
                                    $this->_sendResponse(200, $content);
                                    
                                    //eject 2 levels
                                    break 2;
                                }
                                throw new Exception("Not an picklist field");
                            }
                        }
                        throw new Exception("Fieldname not found");
                    } else {
                        
                        //Send cached response
                        $this->_sendResponse(200, $cachedValue);
                    }
                } else {
                    
                    //Check if Asset list is present in cache
                    if (isset($_GET['category'])) {
                        $cachedValue = false;
                    } else {
                        $cachedValue = Yii::app()->cache->get(
                            $this->_clientid . '_' .
                            $_GET['model']
                            . '_'
                            . 'list'
                        );                        
                    }

                    if ($cachedValue === false) {
                        //Send request to vtiger REST service
                        if (isset($_GET['category'])) {

                            if ($_GET['category'] == 'inoperation') {
                                $query = "select * from " . $_GET['model'] . " where assetstatus = 'In Service' LIMIT 1000;";
                            } else {
                                $query = "select * from " . $_GET['model'] . " where assetstatus = 'Out-of-service' LIMIT 1000;";
                            }

                        } else {
                            $query = "select * from " . $_GET['model'] . " LIMIT 1000;";
                        }

                        //urlencode to as its sent over http.
                        $queryParam = urlencode($query);

                        //creating query string
                        $params = "sessionName={$this->_session->sessionName}" .
                                "&operation=query&query=$queryParam";

                        //Log
                        Yii::log(
                            " TRACE(" . $this->_traceId . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " PROCESSING REQUEST (sending GET request " .
                            "to vt url: " . 
                            $this->_vtresturl . "?$params" .
                            ")", 
                            CLogger::LEVEL_TRACE
                        );

                        //Receive response from vtiger REST service
                        //Return response to client  
                        $rest = new RESTClient();
                
                        $rest->format('json');
                        $response = $rest->get(
                            $this->_vtresturl . "?$params"
                        );

                        //Log
                        Yii::log(
                            " TRACE(" . $this->_traceId . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " PROCESSING REQUEST (response received: " . 
                            $response .
                            ")", 
                            CLogger::LEVEL_TRACE
                        );

                        if ($response == '' || $response == null)
                            throw new Exception(
                                "Blank response received from " .
                                "vtiger: Get Asset List"
                            );

                        //Save vtiger response
                        $this->_vtresponse = $response;

                        //Objectify the response and check its success
                        $response = json_decode($response, true);

                        if ($response['success'] == false)
                        throw new Exception('Unable to fetch details');

                        $customFields = Yii::app()->params[$this->_clientid .
                            '_custom_fields']['Assets'];

                        //Before sending response santise custom fields names to 
                        //human readable field names                
                        foreach ($response['result'] as &$asset) {
                            unset($asset['update_log']);
                            unset($asset['hours']);
                            unset($asset['days']);
                            unset($asset['modifiedtime']);
                            unset($asset['from_portal']);
                            foreach ($asset as $fieldname => $value) {
                                $keyToReplace = array_search(
                                    $fieldname, $customFields
                                );
                                if ($keyToReplace) {
                                    unset($asset[$fieldname]);
                                    $asset[$keyToReplace] = $value;
                                    //unset($customFields[$keyToReplace]);
                                }
                            }
                        }

                        $cachedValue = json_encode($response);

                        //Save the response in cache
                        if (!isset($_GET['category'])) {  
                            Yii::app()->cache->set(
                                $this->_clientid . '_' .
                                $_GET['model'] .
                                '_' .
                                'list', $cachedValue
                            );                                      
                        }                        
                    }
                    
                    //Send the response
                    $this->_sendResponse(200, $cachedValue);
                    
                }
                break;
                
            case 'Background':
                
                if (isset($_GET['action']) && 
                    $_GET['action'] == 'backgroundstatus') {
              
                    $email = $_SERVER['HTTP_X_USERNAME'];
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST : FETCHING BACKGOUND" . 
                        " STATUS FROM DYNAMODB FOR $email", 
                        CLogger::LEVEL_TRACE
                    );
                    
                    // Instantiate the class
                    $dynamodb = new AmazonDynamoDB();
                    $dynamodb->set_region(
                        constant(
                            "AmazonDynamoDB::" . 
                            Yii::app()->params->awsDynamoDBRegion
                        )
                    );

                    $clientID = $_SERVER['HTTP_X_CLIENTID'];
                    
                    $ddbResponse = $dynamodb->scan(array(
                        'TableName' => Yii::app()->params->awsErrorDynamoDBTableName,
                        'ScanFilter' => array(
                            'clientid' => array(
                                'ComparisonOperator' => AmazonDynamoDB::CONDITION_EQUAL,
                                'AttributeValueList' => array(
                                    array(
                                        AmazonDynamoDB::TYPE_STRING => $clientID
                                    )
                                )
                            )
                        )
                    ));
                    
                    $result = array();
                    $response = new stdClass();
                    $response->success = true;
                    $k = 0;
                    
                    if($ddbResponse->body->Count > 0)
                    foreach ($ddbResponse->body->Items as $item)
                    {
                        $var = json_encode($item);
                        $item = json_decode($var, true);
                        
                        $result[$k]['ticket_no'] = $item['ticket_no'][AmazonDynamoDB::TYPE_STRING];
                        $result[$k]['username'] = $item['username'][AmazonDynamoDB::TYPE_STRING];
                        $result[$k]['datetime'] = $item['datetime'][AmazonDynamoDB::TYPE_NUMBER];
                        $result[$k]['clientid'] = $item['clientid'][AmazonDynamoDB::TYPE_STRING];
                        $result[$k]['message']  = $item['message'][AmazonDynamoDB::TYPE_STRING];
                        
                        $k++;
                    }
                     
                    $tmp = Array();
                    foreach($result as &$srt)
                        $tmp[] = &$srt["datetime"];
                    
                    array_multisort($tmp, SORT_DESC, $result);

                    $response->result = json_encode($result);
                    //Send response
                    $this->_sendResponse(200, json_encode($response));

                }                
                break;

            default :
                
                //Default case this case should never be executed
                $response = new stdClass();
                $response->success = false;

                $response->error->code = $this->_errors[1004];
                $response->error->message = "Not a valid method" .
                        " for model " . $_GET['model'];
                $response->error->trace_id = $this->_traceId;
                $this->_sendResponse(405, json_encode($response));

                break;
            }
        } catch (Exception $e) {
            
            if (isset($this->_vtresponse->error->code))
                if ($this->_vtresponse->error->code == 'AUTHENTICATION_REQUIRED')
                    Yii::app()->cache->delete($this->_cacheKey);
            
            //Generating error response
            $response = new stdClass();
            $response->success = false;
            $response->error->code = $this->_errors[$e->getCode()];
            $response->error->message = $e->getMessage();
            $response->error->trace_id = $this->_traceId;
            $response->error->vtresponse = $this->_vtresponse;
            $response->error->instance_id = $this->_instanceid;
            $this->_sendResponse(400, json_encode($response));
        }
    }
    
    /**
     * Action for viewing various models in detail
     * 
     * This action handles the following actions:
     * - User
     *       Request Method: GET
     *       Response Type : json
     *       Subactions    : $email
     *       Notes: Users data is stored in Amazon's Dynamo DB.
     * - Helpdesk
     *       Request Method: GET
     *       Response Type : json
     *       Subactions    : $id
     * - DocumentAttachment
     *       Request Method: GET
     *       Response Type : json
     *       Subactions    : $id
     *       Notes: The actual document is stored in Amazon's S3
     * - Assets
     *       Request Method: GET
     *       Response Type : json
     *       Subactions    : $id
     *
     * Notes: $id is vTiger webservice ID and is of 
     * the form [modelid]x[entityid]
     * 
     * @return appropriate details
     */
    public function actionView()
    {
        //Tasks include detail view of a specific Troubleticket and Assets
        try {
            
            //Log
            Yii::log(
                "TRACE(" . $this->_traceId . 
                "); FUNCTION(" . __FUNCTION__ . 
                "); PROCESSING REQUEST ", 
                CLogger::LEVEL_TRACE
            );
            
            switch ($_GET['model']) {
            /*
                * **************************************************************
                * **************************************************************
                * * User MODEL
                * * Accepts id
                * **************************************************************
                * **************************************************************
                */
            case 'User':
                
                if(isset($_GET['email'])) {
                    // Instantiate the class for Dynamo DB
                    $dynamodb = new AmazonDynamoDB();
                    $dynamodb->set_region(
                        constant(
                            "AmazonDynamoDB::" . 
                            Yii::app()->params->awsDynamoDBRegion
                        )
                    );

                    //It match username sent in the header and email
                    //sent in the GET request
                    if($_SERVER['HTTP_X_USERNAME'] !== $_GET['email'])
                        throw new Exception("Credentials are invalid.", 2004);

                    // Get an item
                    $ddbResponse = $dynamodb->get_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'Key' => $dynamodb->attributes(
                                array(
                                    'HashKeyElement' => $_GET['email'],
                                )
                            ),
                            'ConsistentRead' => 'true'
                        )
                    );

                    //Checking if DynamoDB response has items
                    if (isset($ddbResponse->body->Item)) {

                        //create response
                        foreach ($ddbResponse->body->Item->children()
                        as $key => $item) {
                            $result->{$key} 
                                = (string) $item->{AmazonDynamoDB::TYPE_STRING};
                        }
                        $response->success = true;
                        $response->result = $result;

                        //Send response
                        $this->_sendResponse(200, json_encode($response));
                    } else {

                        //Create User not found error
                        $response->success = false;
                        $response->error->code = "NOT_FOUND";
                        $response->error->message = $_GET['email'] . " was " .
                                " not found";
                        $response->error->trace_id = $this->_traceId;
                        $this->_sendResponse(404, json_encode($response));
                    }
                }
                
                break;
                /*
                 * *************************************************************
                 * *************************************************************
                 * * HelpDesk MODEL
                 * * Accepts id
                 * *************************************************************
                 * *************************************************************
                 */
            case 'HelpDesk':
                
                Yii::log(
                    "TRACE(" . $this->_traceId . "); FUNCTION(" . 
                    __FUNCTION__ . "); PROCESSING REQUEST " .
                    json_encode($_GET),
                    CLogger::LEVEL_TRACE
                );                
                
                if (preg_match('/[0-9]?x[0-9]?/i', $_GET['id'])==0)
            throw new Exception('Invalid format of Id');

                //Get HelpDesk details 
                //Creating vTiger Query
                $query = "select * from " . $_GET['model'] .
                        " where id = " . $_GET['id'] . ";";

                //urlencode to as its sent over http.
                $queryParam = urlencode($query);

                //creating query string
                $params = "sessionName={$this->_session->sessionName}" .
                        "&operation=query&query=$queryParam";
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (sending GET request to vt url: " . 
                    $this->_vtresturl . "?$params" .
                    ")", 
                    CLogger::LEVEL_TRACE
                );                  

                //sending Request vtiger REST service
                $rest = new RESTClient();
                
                $rest->format('json');
                $response = $rest->get(
                    $this->_vtresturl . "?$params"
                );
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $response .
                    ")", 
                    CLogger::LEVEL_TRACE
                );                
                
                //Objectify the response and check its success
                $response = json_decode($response, true);
                $response['result'] = $response['result'][0];

                if (!$response['success'])
                throw new Exception($response['error']['message']);

                //Get Documents Ids
                //creating query string
                $params = "sessionName={$this->_session->sessionName}" .
                        "&operation=getrelatedtroubleticketdocument" .
                        "&crmid=" . $_GET['id'];
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (sending GET request to vt url: " . 
                    $this->_vtresturl . "?$params" .
                    ")", 
                    CLogger::LEVEL_TRACE
                );                  

                //sending request vtiger REST service
                $rest = new RESTClient();
                
                $rest->format('json');
                $documentids = $rest->get(
                    $this->_vtresturl . "?$params"
                );
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $documentids .
                    ")", 
                    CLogger::LEVEL_TRACE
                );
                
                //Arrayfy the response and check its success 
                $documentids = json_decode($documentids, true);
                if ($documentids['success']==false)
                    throw new Exception('Unable to fetch Documents');
                
                $documentids = $documentids['result'];

                // Get Document Details 
                if (count($documentids) != 0) {
                
                    //Building query for fetching documents
                    $query = "select * from Documents" .
                            " where id in (" . $this->_wsEntities['Documents']
                            . "x" .
                            implode(
                                ", " . $this->_wsEntities['Documents']
                                . "x", $documentids
                            ) . ");";

                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);

                    //creating query string
                    $params = "sessionName={$this->_session->sessionName}" .
                            "&operation=query&query=$queryParam";

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending GET request to vt url: " .
                        $this->_vtresturl . "?$params" .
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                     
                    
                    //sending request to vtiger REST Service 
                    $rest = new RESTClient();
                
                    $rest->format('json');
                    $documents = $rest->get(
                        $this->_vtresturl . "?$params"
                    );
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $documents .
                        ")", 
                        CLogger::LEVEL_TRACE
                    );
                    
                    //Objectify the response and check its success
                    $documents = json_decode($documents, true);
                    
                    if (!$documents['success'])
                        throw new Exception($documents['error']['message']);
                    
                    $response['result']['documents'] = $documents['result'];
                }

                    /* Get Contact's Name */
                if ($response['result']['parent_id'] != '') {
                    $query = "select * from Contacts" .
                            " where id = " .
                            $response['result']['parent_id'] . ";";

                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);

                    //creating query string
                    $params = "sessionName={$this->_session->sessionName}" .
                            "&operation=query&query=$queryParam";

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending GET request to vt url: " .
                        $this->_vtresturl . "?$params" .
                        ")", 
                        CLogger::LEVEL_TRACE
                    );
                    
                    //sending request to vtiger REST Service 
                    $rest = new RESTClient();
                
                    $rest->format('json');
                    $contact = $rest->get(
                        $this->_vtresturl . "?$params"
                    );
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $contacts .
                        ")", 
                        CLogger::LEVEL_TRACE
                    );
                    
                    //Objectify the response and check its success
                    $contact = json_decode($contact, true);
                    
                    if (!$contact['success'])
                        throw new Exception($contact['error']['message']);
                    
                    //Storing contact name to response
                    $response['result']['contactname'] 
                        = $contact['result'][0];

                    //Building response
                    $query = "select accountname from Accounts" .
                            " where id = " .
                            $contact['result'][0]['account_id'] . ";";

                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);

                    //creating query string
                    $params = "sessionName={$this->_session->sessionName}" .
                            "&operation=query&query=$queryParam";

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending GET request to vt url: " .
                        $this->_vtresturl . "?$params" .
                        ")", 
                        CLogger::LEVEL_TRACE
                    );
                    
                    //sending request to vtiger REST Service 
                    $rest = new RESTClient();
                
                    $rest->format('json');
                    $account = $rest->get(
                        $this->_vtresturl . "?$params"
                    );
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $account .
                        ")", 
                        CLogger::LEVEL_TRACE
                    );
                    
                    $account = json_decode($account, true);
                    if (!$account['success'])
                        throw new Exception($account['error']['message']);
                    $response['result']['accountname'] 
                        = $account['result'][0]['accountname'];
                }

                $customFields = Yii::app()->params[$this->_clientid . 
                    '_custom_fields']['HelpDesk'];

                unset($response['result']['update_log']);
                unset($response['result']['hours']);
                unset($response['result']['days']);
                unset($response['result']['modifiedtime']);
                unset($response['result']['from_portal']);
                
                if (is_array($response['result']))
                foreach ($response['result'] as $fieldname => $value) {
                        $keyToReplace = array_search($fieldname, $customFields);
                    if ($keyToReplace) {
                            unset($response['result'][$fieldname]);
                            $response['result'][$keyToReplace] = $value;
                    }
                }

                $this->_sendResponse(200, json_encode($response));
                break;

                /*
                 * *************************************************************
                 * *************************************************************
                 * * Assets MODEL
                 * * Accepts id
                 * *************************************************************
                 * *************************************************************
                 */
            case 'Assets':
                
                    if (preg_match('/[0-9]?x[0-9]?/i', $_GET['id'])==0)
                throw new Exception('Invalid format of Id');

                    //Send request to vtiger REST service
                    $query = "select * from " . $_GET['model'] .
                            " where id = " . $_GET['id'] . ";";

                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);

                    //creating query string
                    $params = "sessionName={$this->_session->sessionName}" .
                            "&operation=query&query=$queryParam";

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending GET request to vt url: " .
                        $this->_vtresturl . "?$params" .
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                     
                    
                    //Receive response from vtiger REST service
                    //Return response to client  
                    $rest = new RESTClient();
                
                    $rest->format('json');
                    $response = $rest->get(
                        $this->_vtresturl . "?$params"
                    );

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $response .
                        ")", 
                        CLogger::LEVEL_TRACE
                    );
                    
                    $response = json_decode($response, true);
                    $response['result'] = $response['result'][0];

                    $customFields = Yii::app()->params[$this->_clientid . 
                        '_custom_fields']['Assets'];

                foreach ($response['result'] as $fieldname => $value) {
                    $keyToReplace = array_search($fieldname, $customFields);
                    if ($keyToReplace) {
                        unset($response['result'][$fieldname]);
                        $response['result'][$keyToReplace] = $value;
                        //unset($customFields[$keyToReplace]); 
                    }
                }

                $this->_sendResponse(200, json_encode($response));
                break;

                /*
                 * *************************************************************
                 * *************************************************************
                 * * DocumentAttachments MODEL
                 * * Accepts notesid
                 * *************************************************************
                 * *************************************************************
                 */
            case 'DocumentAttachments':

                //urlencode to as its sent over http.
                $queryParam = urlencode($query);

                //creating query string
                $params = "sessionName={$this->_session->sessionName}" .
                        "&operation=gettroubleticketdocumentfile" .
                        "&notesid=" . $_GET['id'];

                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (sending GET request to vt url: " . 
                    $this->_vtresturl . "?$params" .
                    ")", 
                    CLogger::LEVEL_TRACE
                );
                
                //Receive response from vtiger REST service
                //Return response to client  
                $rest = new RESTClient();
                
                $rest->format('json');
                $response = $rest->get(
                    $this->_vtresturl . "?$params"
                );
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $response .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                );                
                
                $response = json_decode($response);                
                
                $sThree = new AmazonS3();
                $sThree->set_region(
                    constant("AmazonS3::" . Yii::app()->params->awsS3Region)
                );

                $uniqueId = uniqid();

                $fileResource = fopen(
                    'protected/data/' . $uniqueId . 
                    $response->result->filename, 'x'
                );
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (sending request to s3 to get file: " .
                    $response->result->filename .
                    ")", 
                    CLogger::LEVEL_TRACE
                );                 
                
                $sThreeResponse = $sThree->get_object(
                    Yii::app()->params->awsS3Bucket, 
                    $response->result->filename, 
                    array(
                        'fileDownload' => $fileResource
                    )
                );

                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received from s3: " . 
                    json_encode($sThreeResponse) .
                    ")", 
                    CLogger::LEVEL_TRACE
                );
                
                if (!$sThreeResponse->isOK())
                throw new Exception("File not found.");

                $response->result->filecontent 
                    = base64_encode(
                        file_get_contents(
                            'protected/data/' . $uniqueId .
                            $response->result->filename
                        )
                    );
                unlink(
                    'protected/data/' . $uniqueId . $response->result->filename
                );

                $filenameSanitizer = explode("_", $response->result->filename);
                unset($filenameSanitizer[0]);
                unset($filenameSanitizer[1]);
                $response->result->filename = implode('_', $filenameSanitizer);
                $this->_sendResponse(200, json_encode($response));
                break;

            default :
                $response = new stdClass();
                $response->success = false;
                $response->error->code = $this->_errors[1004];
                $response->error->message = "Not a valid method" .
                        " for model " . $_GET['model'];
                $response->error->trace_id = $this->_traceId;
                $this->_sendResponse(405, json_encode($response));
                break;
            }
        } catch (Exception $e) {
            
            if (isset($this->_vtresponse->error->code))
                if ($this->_vtresponse->error->code == 'AUTHENTICATION_REQUIRED')
                    Yii::app()->cache->delete($this->_cacheKey);
            
            $response = new stdClass();
            $response->success = false;
            $response->error->code = "ERROR";
            $response->error->message = $e->getMessage();
            $response->error->trace_id = $this->_traceId;
            $this->_sendResponse(400, json_encode($response));
        }
    }
    
    /**
     * Action for Creating records of various models
     * 
     * This action handles the following actions:
     * - User
     *       Request Method: POST
     *       Response Type : json
     *       Notes: Users data is stored in Amazon's Dynamo DB.
     * - Helpdesk
     *       Request Method: POST
     *       Response Type : json
     *       Notes: Data is validated before entering, thus ticketstatus, 
     *       reportdamage, ticket_title, trailerid are required fields. Ticket 
     *       is created using vtiger webservices once successfull all files in 
     *       post attachment are one by one sent to Amazon's S3. For each file
     *       a document record is created in the vtiger and the TroubleTicket is
     *       linked with the document record using custom vtiger webservice.
     * 
     * @return appropriate list after creation
     */
    public function actionCreate()
    {
        //Tasks include detail view of a specific Troubleticket and Assets
        try {
            
            Yii::log(
                "TRACE(" . $this->_traceId . 
                "); FUNCTION(" . __FUNCTION__ . 
                "); PROCESSING REQUEST ",
                CLogger::LEVEL_TRACE
            );            
            
            switch ($_GET['model']) {
                /*
                 * *************************************************************
                 * *************************************************************
                 * * User MODEL
                 * * Accepts id
                 * *************************************************************
                 * *************************************************************
                 */
            case 'User':
                
                if ($_GET['action'] == 'copyuser') {
                    
                    // MAKE IT ASYNC
                    //                 
                    ignore_user_abort(true);
                    set_time_limit(0);
                    
                    Yii::log(
                        "TRACE(" . $this->_traceId . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " CREATING MDB OBJECT ", 
                        CLogger::LEVEL_TRACE
                    );
                    include("protected/config/config.inc.php");

                    /**
                    * Database connection 
                    *
                    */                    

                    Yii::log(
                        "TRACE(" . $this->_traceId . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " CREATING DATABASE CONNNECTION TO " . 
                        $dbconfig['db_server'], 
                        CLogger::LEVEL_TRACE
                    );
                    $mysqli = new mysqli(
                        $dbconfig['db_server'] . $dbconfig['db_port'],
                        $dbconfig['db_username'],
                        $dbconfig['db_password'],
                        $dbconfig['db_name']
                    );
                    
                    if ($mysqli->connect_error) 
                        throw New Exception($mysqli->connect_error);
                    
                    Yii::log(
                        "TRACE(" . $this->_traceId . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " CREATING AmazonDynamoDB CONNNECTION ", 
                        CLogger::LEVEL_TRACE
                    );
                    // Instantiate the class
                    $dynamodb = new AmazonDynamoDB(); 
                    $dynamodb->set_region(
                        constant(
                            "AmazonDynamoDB::" . 
                            Yii::app()->params->awsDynamoDBRegion
                        )
                    );
                    
                    Yii::log(
                        "TRACE(" . $this->_traceId . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " GET POST VALUES ", 
                        CLogger::LEVEL_TRACE
                    );
                    $post = json_decode(file_get_contents('php://input'), true);
                    
                    Yii::log(
                        "TRACE(" . $this->_traceId . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " RECEIVED POST : " . json_encode($post), 
                        CLogger::LEVEL_TRACE
                    );
                    
                    // Validations
                    
                    Yii::log(
                        "TRACE(" . $this->_traceId . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " VALIDATE NEW CLIENT ID " . $post['clientid'], 
                        CLogger::LEVEL_TRACE
                    );
                    //Validate Client ID
                    $ddbResponse = $dynamodb->scan(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'AttributesToGet' => array('clientid'),
                            'ScanFilter' => array(
                                'clientid' => array(
                                    'ComparisonOperator' => AmazonDynamoDB::CONDITION_EQUAL,
                                    'AttributeValueList' => array(
                                        array(
                                            AmazonDynamoDB::TYPE_STRING => $post['clientid']
                                        )
                                    )
                                )
                            )
                        )
                    );
                    
                    if(!empty($ddbResponse->body->Items))
                        throw New Exception(
                            "Client id is not available.", 2001
                        );
                    
                    Yii::log(
                        "TRACE(" . $this->_traceId . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " VALIDATE NEW CLIENT EMAIL " . $post['id'], 
                        CLogger::LEVEL_TRACE
                    );
                    // Validate Email
                    $ddbResponse = $dynamodb->get_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'Key' => $dynamodb->attributes(
                                array(
                                    'HashKeyElement' => $post['id'],
                                )
                            ),
                            'ConsistentRead' => 'true'
                        )
                    );
                    if (isset($ddbResponse->body->Item))
                        throw New Exception(
                            "Email is already registered.", 2002
                        );
                    
                    Yii::log(
                        "TRACE(" . $this->_traceId . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " EMAIL and CLIENT ID are uqique," .
                        " so processing further. ", 
                        CLogger::LEVEL_TRACE
                    );
                    
                    Yii::log(
                        "TRACE(" . $this->_traceId . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " GETING ID_SEQUENCE FROM DYNAMO_DB. ", 
                        CLogger::LEVEL_TRACE
                    );
                    $ddbResponse = $dynamodb->scan(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'AttributesToGet' => array('id_sequence'),
                        )
                    );                  

                    $maxIdSequence = 1000;
                    foreach ($ddbResponse->body->Items
                    as $key => $item) {
                        $idSequence
                            = intval((string) $item->id_sequence->{AmazonDynamoDB::TYPE_STRING});
                        if ($idSequence > $maxIdSequence) {
                            $maxIdSequence = $idSequence;
                        }
                    }  
                    $maxIdSequence += 1000;

                    Yii::log(
                        "TRACE(" . $this->_traceId . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " NEW ID_SEQUENCE FOR THE CLIENT IS $maxIdSequence.", 
                        CLogger::LEVEL_TRACE
                    );
                    Yii::log(
                        "TRACE(" . $this->_traceId . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " GET FROM CLIENT (WHICH IS BEING COPIED " .
                        "TO NEW) DETAILS. ", 
                        CLogger::LEVEL_TRACE
                    );
                    
                    $oldClientId = $_SERVER['HTTP_X_USERNAME'];
                    // GET FROM CLIENT DETAIL
                    $ddbResponse = $dynamodb->get_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'Key' => $dynamodb->attributes(
                                array(
                                    'HashKeyElement' => $oldClientId,
                                )
                            ),
                            'ConsistentRead' => 'true'
                        )
                    );
                    
                    if (!isset($ddbResponse->body->Item))
                        throw New Exception(
                            "From client is not available.", 2005
                    );
                    
                    Yii::log(
                        "TRACE(" . $this->_traceId . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " FROM CLIENT (WHICH IS BEING COPIED TO NEW) " .
                        " DETAILS FETCHED FROM DYNAMODB. ", 
                        CLogger::LEVEL_TRACE
                    );
                    $oldClient = $ddbResponse->body->Item;
                    $oldClient = get_object_vars($oldClient);
                    
                    $clientArr = array();
                    foreach($oldClient as $k => $val){
                        $clientArr[$k] = (string)$val->{AmazonDynamoDB::TYPE_STRING}; 
                    }
                    Yii::log(
                        "TRACE(" . $this->_traceId . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " FROM CLIENT (WHICH IS BEING COPIED TO NEW)" .
                        " DETAILS : " . json_encode($clientArr), 
                        CLogger::LEVEL_TRACE
                    );
                    
                    $post = array_merge($clientArr, $post);
                    
                    if(!empty($clientArr['id_sequence']))
                        $clientIdSequence = $clientArr['id_sequence'];
                    else
                        $clientIdSequence = 0;
                    $plusSequence = $maxIdSequence - $clientIdSequence;
                    /**
                    * Database connection options
                    * @global string $options
                    */
                    $options = array(
                        'persistent' => true,
                    );
                    
                    Yii::log(
                        "TRACE(" . $this->_traceId . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " CREATING MDB OBJECT ", 
                        CLogger::LEVEL_TRACE
                    );                                       
                    
                    //Create Default DB credentials
                    
                    $dbServer     = $dbconfig['db_server'];
                    $dbPort       = str_replace(":", "", $dbconfig['db_port']);
                    $dbUsername   = 'user_' . substr($post['clientid'], 0, 5) . 
                        '_' . substr(strrev(uniqid()), 1, 5);
                    $dbPassword   = substr(strrev(uniqid()), 1, 16);
                    $dbName       = 'vtiger_' . 
                        substr($post['clientid'], 0, 7) . '_' . 
                        substr(strrev(uniqid()), 1, 8);

                    $post['secretkey_1'] = uniqid("", true) . uniqid("", true);
                    $post['apikey_1'] = strtoupper(uniqid("GZCLD" . uniqid()));

                    $post['secretkey_2'] = uniqid("", true) . uniqid("", true);
                    $post['apikey_2'] = strtoupper(uniqid("GZCLD" . uniqid()));
                    
                    $post['databasename'] = $dbName;
                    $post['server'] = $dbServer;
                    $post['port'] = $dbPort;
                    $post['username'] = $dbUsername;
                    $post['dbpassword'] = $dbPassword;
                    $post['id_sequence'] = (String)$maxIdSequence;
                       
                    // ADD STATUS DBPeding
                    $post['status'] = 'DBPending';
                    //
                    // PUT ITEM IN THE DYNAMODB AND TELL THAT
                    // USER TO WAIT FOR THE EMAIL.
                    // 
                    // MYSQL DB WILL BE UPDATED IN THE BACKGROUND.
                    //
                    //
                    // Instantiate the class                   
                    //                    
                    $dynamodb = new AmazonDynamoDB();
                    $dynamodb->set_region(
                        constant(
                            "AmazonDynamoDB::" .
                            Yii::app()->params->awsDynamoDBRegion
                        )
                    );
                    $ddbResponse = $dynamodb->put_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'Item' => $dynamodb->attributes($post)
                        )
                    );

                    $res['id'] = $post['id'];
                    $res['clientid'] = $post['clientid'];
                    $res['status'] = $post['status'];

                    $response->success = true;
                    $response->result = $res;

                    unset($res);

                    ob_start();
                    header('HTTP/1.1 200 OK');
                    // and the content type
                    header('Content-type: text/json');
                    header('Access-Control-Allow-Origin: *');
                    echo json_encode($response);
                    // get the size of the output
                    $size = ob_get_length();
                    // send headers to tell the browser to close the connection
                    header("Content-Length: $size");
                    header('Connection: close');
                    ob_end_flush();
                    ob_flush();
                    flush();
                    
                    // close current session
                    if (session_id()) session_write_close();
                    try {
                        Yii::log(
                            "TRACE(" . $this->_traceId . ");" . 
                            " FUNCTION(" . __FUNCTION__ . ");" . 
                            " NEW CLIENT DETAILS : " . json_encode($post), 
                            CLogger::LEVEL_TRACE
                        );

                        Yii::log(
                            "TRACE(" . $this->_traceId . ");" . 
                            " FUNCTION(" . __FUNCTION__ . ");" . 
                            " CREATING USER ($dbUsername) IN MYSQL FOR NEW CLIENT ", 
                            CLogger::LEVEL_TRACE
                        );

                        //Create User
                        //===========
                        $query = "GRANT USAGE ON *.* TO '$dbUsername'@'%' " .
                            "IDENTIFIED BY '$dbPassword' ";
                        $query .= "WITH MAX_QUERIES_PER_HOUR 0 " .
                            "MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR " .
                            "0 MAX_USER_CONNECTIONS 0;";                    

                        // Execute the query
                        // check if the query was executed properly
                        if ($mysqli->query($query)===false)
                            throw New Exception(
                                "Unable to create user and grant permission: " . 
                                $mysqli->error, 0
                            );

                        Yii::log(
                            "TRACE(" . $this->_traceId . ");" . 
                            " FUNCTION(" . __FUNCTION__ . ");" . 
                            " CREATING DATABASE ($dbName) IN MYSQL FOR NEW CLIENT ", 
                            CLogger::LEVEL_TRACE
                        );
                        //Create Database
                        //===============
                        $query = "CREATE DATABASE IF NOT EXISTS `$dbName`;";

                        // Execute the query
                        // check if the query was executed properly
                        if ($mysqli->query($query)===false) {
                            $mysqli->query("DROP USER $dbUsername;");
                            throw New Exception(
                                "Unable to create database " . $mysqli->error, 0
                            );                    
                        }

                        Yii::log(
                            "TRACE(" . $this->_traceId . ");" . 
                            " FUNCTION(" . __FUNCTION__ . ");" . 
                            " GRANTING PRIVILEGES ON $dbName TO $dbUsername ", 
                            CLogger::LEVEL_TRACE
                        );
                        //Grant Permission
                        //================
                        $query = "GRANT ALL PRIVILEGES ON `$dbName`.* TO " .
                            "'$dbUsername'@'%';";

                        // Execute the query
                        // check if the query was executed properly
                        if ($mysqli->query($query)===false) {
                            $mysqli->query("DROP USER $dbUsername;");
                            $mysqli->query("DROP DATABASE IF EXISTS $dbUsername;");
                            throw New Exception($mysqli->error, 0);
                        }

                        Yii::log(
                            "TRACE(" . $this->_traceId . ");" . 
                            " FUNCTION(" . __FUNCTION__ . ");" . 
                            " IMPORTING DATABASE DUMP TO $dbName ", 
                            CLogger::LEVEL_TRACE
                        );
                        //Import Database
                        //===============
                        $execStmt = "mysqldump -u" . $clientArr['username'] . 
                            " -p" . $clientArr['dbpassword'] . 
                            " -h" . $clientArr['server'] . 
                            " -P " . $clientArr['port'] . 
                            " " . $clientArr['databasename'] . 
                            " | mysql -u$dbUsername -p$dbPassword -h$dbServer" .
                            " -P $dbPort $dbName";

                        $output = shell_exec($execStmt);

                        if ($output === false) {
                            $mysqli->query("DROP USER $dbUsername;");
                            $mysqli->query("DROP DATABASE IF EXISTS $dbName;");
                            throw New Exception(
                                "Unable to populate data in $dbName.", 0
                            );
                        }

                        //Add User Sequence
                        //======================
                        $queries[] = "USE $dbName;";
                        $queries[] = "START TRANSACTION;";
                        $queries[] = "SET foreign_key_checks = 0;";
                        $queries[] = "update vtiger_users2group set userid = " .
                            "$plusSequence + userid;";
                        $queries[] = "update vtiger_user2role set userid = " .
                            "$plusSequence + userid;";
                        $queries[] = "update vtiger_users set id = " .
                            "$plusSequence + id;";
                        $queries[] = "update vtiger_users_seq set id = " .
                            "$plusSequence + id;";
                        $queries[] = "update vtiger_crmentity set smcreatorid = " .
                            "$plusSequence + smcreatorid, smownerid = smownerid +" .
                            " $plusSequence, modifiedby = modifiedby" .
                            " + $plusSequence;";
                        $queries[] = "update vtiger_homestuff set userid = ".
                            "$plusSequence + userid;";
                        $queries[] = "update vtiger_mail_accounts set user_id =" .
                            " $plusSequence + user_id;";
                        $queries[] = "update vtiger_user2mergefields set userid =" .
                            " $plusSequence + userid;";
                        $queries[] = "update vtiger_user_module_preferences set" .
                            " userid = $plusSequence + userid;";
                        $queries[] = "update vtiger_users_last_import set" .
                            " assigned_user_id = $plusSequence + assigned_user_id;";
                        $queries[] = "update vtiger_customview set userid =" .
                            " $plusSequence + userid;";
                        $queries[] = "UPDATE `vtiger_customerportal_prefs` SET" .
                            " `prefvalue` = $plusSequence + prefvalue " . 
                            "WHERE `vtiger_customerportal_prefs`.`prefkey` =" .
                            " 'userid';";
                        $queries[] = "UPDATE `vtiger_customerportal_prefs` SET" .
                            " `prefvalue` = $plusSequence + prefvalue " . 
                            "WHERE `vtiger_customerportal_prefs`.`prefkey` =" .
                            " 'defaultassignee';";
                        $queries[] = "SET foreign_key_checks = 1;";
                        $queries[] = "COMMIT;";

                        Yii::log(
                            "TRACE(" . $this->_traceId . ");" . 
                            " FUNCTION(" . __FUNCTION__ . ");" . 
                            " UPDATING $dbName USERS IDs with $maxIdSequence ", 
                            CLogger::LEVEL_TRACE
                        );
                        foreach ($queries as $query) {
                            // Execute the query
                            // check if the query was executed properly
                            if ($mysqli->query($query)===false) {
                                $mysqli->query('ROLLBACK;');
                                $mysqli->query("DROP USER $dbUsername;");
                                $mysqli->query("DROP DATABASE IF EXISTS $dbName;");
                                throw New Exception(
                                    $mysqli->error . " Query:" . $query, 0
                                );                        
                            }
                        }

                        $mysqli->close();

                        Yii::log(
                            "TRACE(" . $this->_traceId . ");" . 
                            " FUNCTION(" . __FUNCTION__ . ");" . 
                            " INSERTING INTO DYNAMODB ", 
                            CLogger::LEVEL_TRACE
                        );
                        
                        // UPDATE THE NEW CLIENT STATUS
                        $post['status'] = 'Active';
                        
                        // Instantiate the class
                        $dynamodb = new AmazonDynamoDB();
                        $dynamodb->set_region(
                            constant(
                                "AmazonDynamoDB::" . 
                                Yii::app()->params->awsDynamoDBRegion
                            )
                        );
                        $ddbResponse = $dynamodb->put_item(
                            array(
                                'TableName' => Yii::app()->params->awsDynamoDBTableName,
                                 'Item' => $dynamodb->attributes($post)
                            )
                        );

                        if ($ddbResponse->isOK()) {
                            Yii::app()->cache->set(
                                $post['apikey_1'], $post['secretkey_1']
                            );
                            Yii::app()->cache->set(
                                $post['apikey_2'], $post['secretkey_2']
                            );
                            //SEND THE EMAIL TO USER
                            $email = new AmazonSES();

                            $sesResponse = $email->send_email(
                                // Source (aka From)
                                Yii::app()->params->awsSESFromEmailAddress,
                                array(
                                    'ToAddresses' => array(
                                        $post['id']
                                    )
                                ), 
                                array(// sesMessage (short form)
                                    'Subject.Data' => 'Welcome to Gizur SaaS',
                                    'Body.Text.Data' => 'Hi ' . $post['name_1'] . 
                                    ' ' . $post['name_2'] . ', ' . PHP_EOL .
                                    PHP_EOL .
                                    'Welcome to Gizur SaaS.' . PHP_EOL . PHP_EOL .
                                    'Your username and password are as follows:' .
                                    PHP_EOL .
                                    PHP_EOL .
                                    'Portal Link: ' . Yii::app()->params->serverProtocol
                                    . "://"
                                    . $_SERVER['HTTP_HOST'] . 
                                    PHP_EOL .
                                    'Username: ' . $post['id']  . PHP_EOL .
                                    'Password: [Your Gizur SaaS Password]' . PHP_EOL .

                                    PHP_EOL .
                                    'vTiger Link: ' . Yii::app()->params->serverProtocol
                                    . "://"
                                    . $_SERVER['HTTP_HOST'] . '/' . 
                                    $post['clientid'] . '/' . PHP_EOL .
                                    'Username: admin'  . PHP_EOL .
                                    'Password: [Your old account vTiger password]' . PHP_EOL .
                                    PHP_EOL .
                                    PHP_EOL .
                                    '--' .
                                    PHP_EOL .
                                    'Gizur Admin'
                                )
                            );

                            Yii::log(
                                "TRACE(" . $this->_traceId . ");" . 
                                " FUNCTION(" . __FUNCTION__ . ");" . 
                                " ACCOUNT CREATED : " . json_encode($post), 
                                CLogger::LEVEL_TRACE
                            );
                        } else {
                            throw new Exception("DynamoDB update failed.");
                        }
                    } catch (Exception $e) {
                        $response->success = false;
                        $response->error->code = "NOT_CREATED";
                        $response->error->message = $e->getMessage();
                        $response->error->trace_id = $this->_traceId;

                        // NOTIFY ADMIN ABOUT AN ERROR.
                        $email = new AmazonSES();

                        $sesResponse = $email->send_email(
                            // Source (aka From)
                            Yii::app()->params->awsSESFromEmailAddress,
                            array(
                                'ToAddresses' => Yii::app()->params->awsSESAdminEmailAddresses
                            ), 
                            array(// sesMessage (short form)
                                'Subject.Data' => 'Error in coping account (Gizur SaaS)',
                                'Body.Text.Data' => 'Hi, ' . PHP_EOL .
                                PHP_EOL .
                                'Follwing error has occured.' . PHP_EOL . PHP_EOL .
                                PHP_EOL .
                                'User : ' . $post['id']  . PHP_EOL .
                                PHP_EOL .
                                'Data : ' . json_encode($post) .
                                PHP_EOL .
                                'Response : ' . json_encode($response) .
                                PHP_EOL .
                                PHP_EOL .
                                '--' .
                                PHP_EOL .
                                'Gizur Admin'
                            )
                        );
                    }
                } else {
                    // MAKE IT ASYNC
                    //                 
                    ignore_user_abort(true);
                    set_time_limit(0);

                    Yii::log(
                        "TRACE(" . $this->_traceId . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " CREATING MDB OBJECT ", 
                        CLogger::LEVEL_TRACE
                    );
                    include("protected/config/config.inc.php");

                    /**
                    * Database connection 
                    *
                    */                    

                    $mysqli = new mysqli(
                        $dbconfig['db_server'] . $dbconfig['db_port'],
                        $dbconfig['db_username'],
                        $dbconfig['db_password'],
                        $dbconfig['db_name']
                    );

                    if ($mysqli->connect_error) 
                        throw New Exception($mysqli->connect_error);

                    // Instantiate the class
                    $dynamodb = new AmazonDynamoDB(); 
                    $dynamodb->set_region(
                        constant(
                            "AmazonDynamoDB::" .
                            Yii::app()->params->awsDynamoDBRegion
                        )
                    );

                    $post = json_decode(file_get_contents('php://input'), true);

                    //GET THE CLIENT ID
                    if(empty($post['clientid']))
                        $post['clientid'] = array_shift(
                            explode('@', $post['id'])
                        );

                    //REPLACE UN-WANTED CHARS FROM CLIENTID
                    $replacable = array('_', '.', '#', '-');
                    $post['clientid'] = str_replace(
                        $replacable, '', $post['clientid']
                    );

                    //Validations

                    //Validate Client ID
                    $ddbResponse = $dynamodb->scan(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'AttributesToGet' => array('clientid'),
                            'ScanFilter' => array(
                                'clientid' => array(
                                    'ComparisonOperator' => AmazonDynamoDB::CONDITION_EQUAL,
                                    'AttributeValueList' => array(
                                        array( AmazonDynamoDB::TYPE_STRING => $post['clientid'] )
                                    )
                                )
                            )
                        )
                    );

                    if(!empty($ddbResponse->body->Items))
                        throw New Exception(
                            "Client id is not available.", 2001
                        );

                    // Validate Email
                    $ddbResponse = $dynamodb->get_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'Key' => $dynamodb->attributes(
                                array(
                                    'HashKeyElement' => $post['id'],
                                )
                            ),
                            'ConsistentRead' => 'true'
                        )
                    );
                    if (isset($ddbResponse->body->Item))
                        throw New Exception(
                            "Email is already registered.", 2002
                        );

                    $ddbResponse = $dynamodb->scan(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'AttributesToGet' => array('id_sequence'),
                        )
                    );                  

                    $maxIdSequence = 1000;
                    foreach ($ddbResponse->body->Items
                    as $key => $item) {
                        $idSequence
                            = intval((string) $item->id_sequence->{AmazonDynamoDB::TYPE_STRING});
                        if ($idSequence > $maxIdSequence) {
                            $maxIdSequence = $idSequence;
                        }
                    }  
                    $maxIdSequence += 1000;

                    /**
                    * Database connection options
                    * @global string $options
                    */
                    $options = array(
                        'persistent' => true,
                    );

                    Yii::log(
                        "TRACE(" . $this->_traceId . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " CREATING MDB OBJECT ", 
                        CLogger::LEVEL_TRACE
                    );                                       

                    //Create Default DB credentials

                    $dbServer     = $dbconfig['db_server'];
                    $dbPort       = str_replace(":", "", $dbconfig['db_port']);
                    $dbUsername   = 'user_' . substr($post['clientid'], 0, 5) .
                        '_' . substr(strrev(uniqid()), 1, 5);
                    $dbPassword   = substr(strrev(uniqid()), 1, 16);
                    $dbName       = 'vtiger_' . 
                        substr($post['clientid'], 0, 7) . 
                        '_' . substr(strrev(uniqid()), 1, 8);  

                    $post['secretkey_1'] = uniqid("", true) . uniqid("", true);
                    $post['apikey_1'] = strtoupper(uniqid("GZCLD" . uniqid()));

                    $post['secretkey_2'] = uniqid("", true) . uniqid("", true);
                    $post['apikey_2'] = strtoupper(uniqid("GZCLD" . uniqid()));

                    $post['databasename'] = $dbName;
                    $post['server'] = $dbServer;
                    $post['port'] = $dbPort;
                    $post['username'] = $dbUsername;
                    $post['dbpassword'] = $dbPassword;
                    $post['port'] = $dbPort;
                    $post['id_sequence'] = (String)$maxIdSequence;

                    //Hash password
                    if(empty($post['password']))
                        $originalPassword = substr(uniqid("", true), 0, 7);
                    else
                        $originalPassword = $post['password'];

                    // ADD STATUS DBPeding
                    $post['status'] = 'DBPending';
                    //
                    // PUT ITEM IN THE DYNAMODB AND TELL THAT
                    // USER TO WAIT FOR THE EMAIL.
                    // 
                    // MYSQL DB WILL BE UPDATED IN THE BACKGROUND.
                    //
                    //
                    // Instantiate the class                   
                    //                    
                    $dynamodb = new AmazonDynamoDB();
                    $dynamodb->set_region(
                        constant(
                            "AmazonDynamoDB::" .
                            Yii::app()->params->awsDynamoDBRegion
                        )
                    );
                    $ddbResponse = $dynamodb->put_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'Item' => $dynamodb->attributes($post)
                        )
                    );

                    $res['id'] = $post['id'];
                    $res['clientid'] = $post['clientid'];
                    $res['status'] = $post['status'];

                    $response->success = true;
                    $response->result = $res;

                    unset($res);

                    ob_start();
                    header('HTTP/1.1 200 OK');
                    // and the content type
                    header('Content-type: text/json');
                    header('Access-Control-Allow-Origin: *');
                    echo json_encode($response);
                    // get the size of the output
                    $size = ob_get_length();
                    // send headers to tell the browser to close the connection
                    header("Content-Length: $size");
                    header('Connection: close');
                    ob_end_flush();
                    ob_flush();
                    flush();

                    // close current session
                    if (session_id()) session_write_close();
                    
                    $error_msgs = array();

                    // BELOW LINES OF CODE SHALL BE PROCESSED IN
                    // THE BACKGROUND.
                    //

                    //Create User
                    //===========
                    $query = "GRANT USAGE ON *.* TO '$dbUsername'@'%'" .
                        " IDENTIFIED BY '$dbPassword' ";
                    $query .= "WITH MAX_QUERIES_PER_HOUR 0 " .
                        "MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0" .
                        " MAX_USER_CONNECTIONS 0;";

                    // Execute the query
                    // check if the query was executed properly
                    if ($mysqli->query($query)===false)
                        $error_msgs[] = "Unable to create user and grant permission: " . 
                            $mysqli->error;

                    if(empty($error_msgs)) {
                        //Create Database
                        //===============
                        $query = "CREATE DATABASE IF NOT EXISTS `$dbName`;";

                        // Execute the query
                        // check if the query was executed properly
                        if ($mysqli->query($query)===false) {
                            $mysqli->query("DROP USER $dbUsername;");
                            $error_msgs[] = "Unable to create database " . 
                                $mysqli->error;                    
                        }
                    }

                    if(empty($error_msgs)) {
                        //Grant Permission
                        //================
                        $query = "GRANT ALL PRIVILEGES ON `$dbName`.* TO" .
                            " '$dbUsername'@'%';";

                        // Execute the query
                        // check if the query was executed properly
                        if ($mysqli->query($query)===false) {
                            $mysqli->query("DROP USER $dbUsername;");
                            $mysqli->query("DROP DATABASE IF EXISTS $dbUsername;");
                            $error_msgs[] = $mysqli->error;
                        }
                    }

                    if(empty($error_msgs)) {
                        //Import Database
                        //===============
                        $execStmt = "mysql -u$dbUsername -p$dbPassword " . 
                            "-h$dbServer -P $dbPort $dbName" .
                            " < /var/www/html/lib/vtiger-5.4.0-database.sql";

                        $output = shell_exec($execStmt);

                        if ($output === false) {
                            $mysqli->query("DROP USER $dbUsername;");
                            $mysqli->query("DROP DATABASE IF EXISTS $dbName;");
                            $error_msgs[] = "Unable to populate data in $dbName.";
                        }
                    }

                    if(empty($error_msgs)) {
                        //To update vTiger Admin password
                        //===============================
                        $salt = substr("admin", 0, 2);
                        $salt = '$1$' . str_pad($salt, 9, '0');
                        $oPassword = substr(strrev(uniqid()), 0, 9);
                        $userHash = strtolower(md5($oPassword));
                        $computedEncryptedPassword = crypt($oPassword, $salt);

                        //Add User Sequence
                        //======================
                        $queries[] = "USE $dbName;";
                        $queries[] = "START TRANSACTION;";
                        $queries[] = "SET foreign_key_checks = 0;";
                        $queries[] = "update vtiger_users2group set " . 
                            "userid = $maxIdSequence + userid;";
                        $queries[] = "update vtiger_user2role set userid = " . 
                            "$maxIdSequence + userid;";
                        $queries[] = "update vtiger_users set id = " .
                            "$maxIdSequence + id;";
                        $queries[] = "update vtiger_users_seq set id = " .
                            "$maxIdSequence + id;";
                        $queries[] = "update vtiger_crmentity set smcreatorid = " .
                            "$maxIdSequence + smcreatorid, smownerid = smownerid" .
                            " + $maxIdSequence, modifiedby = modifiedby" .
                            " + $maxIdSequence;";
                        $queries[] = "update vtiger_homestuff set userid = " .
                            "$maxIdSequence + userid;";
                        $queries[] = "update vtiger_mail_accounts set user_id = " .
                            "$maxIdSequence + user_id;";
                        $queries[] = "update vtiger_user2mergefields set userid =" .
                            " $maxIdSequence + userid;";
                        $queries[] = "update vtiger_user_module_preferences set" .
                            " userid = $maxIdSequence + userid;";
                        $queries[] = "update vtiger_users_last_import set " .
                            "assigned_user_id = $maxIdSequence + assigned_user_id;";
                        $queries[] = "update vtiger_customview set userid =" .
                            " $maxIdSequence + userid;";
                        $queries[] = "UPDATE `vtiger_customerportal_prefs` SET" .
                            " `prefvalue` = $maxIdSequence + prefvalue " . 
                            "WHERE `vtiger_customerportal_prefs`.`prefkey` " .
                            "= 'userid';";
                        $queries[] = "UPDATE `vtiger_customerportal_prefs` SET " .
                            "`prefvalue` = $maxIdSequence + prefvalue " . 
                            "WHERE `vtiger_customerportal_prefs`.`prefkey`" .
                            " = 'defaultassignee';";
                        $queries[] = "update vtiger_users set user_password = " . 
                            "'$computedEncryptedPassword', crypt_type = " . 
                            "'PHP5.3MD5', user_hash = '$userHash' where " .
                            "user_name = 'admin'";
                        $queries[] = "SET foreign_key_checks = 1;";
                        $queries[] = "COMMIT;";

                        foreach ($queries as $query) {
                            // Execute the query
                            // check if the query was executed properly
                            if ($mysqli->query($query)===false) {
                                $mysqli->query('ROLLBACK;');
                                $mysqli->query("DROP USER $dbUsername;");
                                $mysqli->query("DROP DATABASE IF EXISTS $dbName;");
                                $error_msgs[] = $mysqli->error . " Query:" . $query;
                                break;
                            }
                        }
                    }
                    $mysqli->close();

                    // UPDATE THE DYNAMODB
                    // 
                    // UPDATE STATUS
                    $post['status'] = 'Active';

                    $dynamodb = new AmazonDynamoDB();
                    $dynamodb->set_region(
                        constant(
                            "AmazonDynamoDB::" .
                            Yii::app()->params->awsDynamoDBRegion
                        )
                    );
                    $ddbResponse = $dynamodb->put_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'Item' => $dynamodb->attributes($post)
                        )
                    );

                    if ($ddbResponse->isOK() && empty($error_msgs)) {
                        Yii::app()->cache->set(
                            $post['apikey_1'], $post['secretkey_1']
                        );
                        Yii::app()->cache->set(
                            $post['apikey_2'], $post['secretkey_2']
                        );

                        //SEND THE EMAIL TO USER
                        $email = new AmazonSES();

                        $sesResponse = $email->send_email(
                            // Source (aka From)
                            Yii::app()->params->awsSESFromEmailAddress,
                            array(
                                'ToAddresses' => array(
                                    $post['id']
                                )
                            ), 
                            array(// sesMessage (short form)
                                'Subject.Data' => 'Welcome to Gizur SaaS',
                                'Body.Text.Data' => 'Hi ' . $post['name_1'] . 
                                ' ' . $post['name_2'] . ', ' . PHP_EOL .
                                PHP_EOL .
                                'Welcome to Gizur SaaS.' . PHP_EOL . PHP_EOL .
                                'Your username and password are as follows:' .
                                PHP_EOL .
                                PHP_EOL .
                                'Portal Link: ' . Yii::app()->params->serverProtocol
                                . "://"
                                . $_SERVER['HTTP_HOST'] . 
                                PHP_EOL .
                                'Username: ' . $post['id']  . PHP_EOL .
                                'Password: [Your Gizur SaaS Password]' . PHP_EOL .

                                PHP_EOL .
                                'vTiger Link: ' . Yii::app()->params->serverProtocol
                                . "://"
                                . $_SERVER['HTTP_HOST'] . '/' . 
                                $post['clientid'] . '/' . PHP_EOL .
                                'Username: admin'  . PHP_EOL .
                                'Password: ' . $oPassword . PHP_EOL .
                                PHP_EOL .
                                PHP_EOL .
                                '--' .
                                PHP_EOL .
                                'Gizur Admin'
                            )
                        );

                        Yii::log(
                            "TRACE(" . $this->_traceId . ");" . 
                            " FUNCTION(" . __FUNCTION__ . ");" . 
                            " ACCOUNT CREATED : " . json_encode($post), 
                            CLogger::LEVEL_TRACE
                        );


                    } else {
                        $response->success = false;
                        $response->error->code = "NOT_CREATED";
                        $response->error->message = $e->getMessage();
                        $response->error->trace_id = $this->_traceId;

                        // NOTIFY ADMIN ABOUT AN ERROR.
                        $email = new AmazonSES();

                        $sesResponse = $email->send_email(
                            // Source (aka From)
                            Yii::app()->params->awsSESFromEmailAddress,
                            array(
                                'ToAddresses' => Yii::app()->params->awsSESAdminEmailAddresses
                            ), 
                            array(// sesMessage (short form)
                                'Subject.Data' => 'Error at Gizur SaaS',
                                'Body.Text.Data' => 'Hi, ' . PHP_EOL .
                                PHP_EOL .
                                'Follwing error has occured.' . PHP_EOL . PHP_EOL .
                                PHP_EOL .
                                'User : ' . $post['id']  . PHP_EOL .
                                'Error : ' . json_encode($error_msgs) .
                                PHP_EOL .
                                PHP_EOL .
                                '--' .
                                PHP_EOL .
                                'Gizur Admin'
                            )
                        );
                    }
                }
                break;
                /*
                 * *************************************************************
                 * *************************************************************
                 * * HelpDesk MODEL
                 * * Accepts id
                 * *************************************************************
                 * *************************************************************
                 */
            case 'HelpDesk':


                /**
                 * Validations
                 */

                $scriptStarted = date("c");
                if (!isset($_POST['ticketstatus'])
                    || empty($_POST['ticketstatus'])
                )
                throw new Exception("ticketstatus does not have a value", 1001);

                if (!isset($_POST['reportdamage']) 
                    || empty($_POST['reportdamage'])
                )
                throw new Exception("reportdamage does not have a value", 1001);

                if (!isset($_POST['trailerid']) 
                    || empty($_POST['trailerid'])
                )
                throw new Exception("trailerid does not have a value", 1001);

                if (!isset($_POST['ticket_title'])
                    || empty($_POST['ticket_title'])
                )
                throw new Exception("ticket_title does not have a value", 1001);

                if ($_POST['ticketstatus'] == 'Open' 
                    && $_POST['reportdamage'] == 'No'
                )
                throw new Exception(
                    "Ticket can be opened for damaged trailers only", 1002
                );

                /** Creating Touble Ticket* */
                $post = $_POST;
                $customFields = array_flip(
                    Yii::app()->params[$this->_clientid . 
                        '_custom_fields']['HelpDesk']
                );

                foreach ($post as $k => $v) {
                    $keyToReplace = array_search($k, $customFields);
                    if ($keyToReplace) {
                        unset($post[$k]);
                        $post[$keyToReplace] = $v;
                    }
                }

                //get data json 
                $dataJson = json_encode(
                    array_merge(
                        $post, 
                        array(
                            'parent_id' => $this->_session->contactId,
                            'assigned_user_id' => $this->_session->userId,
                            'ticketstatus' => (isset($post['ticketstatus']) && !empty($post['ticketstatus'])) ? $post['ticketstatus'] : 'Closed',
                        )
                    )
                );
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (sending POST request to vt url: " . 
                    $this->_vtresturl . "  " .
                    json_encode(
                        array(
                            'sessionName' => $this->_session->sessionName,
                            'operation' => 'create',
                            'element' => $dataJson,
                            'elementType' => $_GET['model']
                        )
                    ) . ")", 
                    CLogger::LEVEL_TRACE
                );                 
                
                //Receive response from vtiger REST service
                //Return response to client  
                $rest = new RESTClient();
                
                $rest->format('json');
                $response = $rest->post(
                    $this->_vtresturl, array(
                        'sessionName' => $this->_session->sessionName,
                        'operation' => 'create',
                        'element' => $dataJson,
                        'elementType' => $_GET['model']
                    )
                );

                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $response .
                    ")", 
                    CLogger::LEVEL_TRACE
                );
                
                if ($response == '' | $response == null)
                    throw new Exception(
                        'Blank response received from vtiger: Creating TT'
                    ); 
                
                $globalresponse = json_decode($response);
                
                /**
                 * The following section creates a response buffer
                 * 
                 */

                //Continue to run script even when the connection is over
                ignore_user_abort(true);
                set_time_limit(0);

                // buffer all upcoming output
                ob_start();

                $response = new stdClass();
                $response->success = true;
                $response->message = "Processing the request, you will be notified by mail on successfull completion"; 
                $response->result = $globalresponse->result;
                
                echo json_encode($response);

                // get the size of the output
                $size = ob_get_length();

                // send headers to tell the browser to close the connection
                header("Content-Length: $size");
                header('Connection: close');

                // flush all output
                ob_end_flush();
                ob_flush();
                flush();

                // close current session
                if (session_id()) session_write_close();
                /* * Creating Document* */
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " DOCUMENT CREATE STARTED: " . 
                    ")", 
                    CLogger::LEVEL_TRACE
                );
                
                if ($globalresponse->success == false)
                    throw new Exception($globalresponse->error->message);

                //Create Documents if any is attached
                $crmid = $globalresponse->result->id;
                $globalresponse->result->documents = Array();
                $globalresponse->result->message = Array();
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " DOCUMENT CREATE STARTED (CRMID): " . $crmid . 
                    ")", 
                    CLogger::LEVEL_TRACE
                );
                
                $dataJson = array(
                    'notes_title' => 'Attachement',
                    'assigned_user_id' => $this->_session->userId,
                    'notecontent' => 'Attachement',
                    'filelocationtype' => 'I',
                    'filedownloadcount' => null,
                    'filestatus' => 1,
                    'fileversion' => '',
                );
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " DOCUMENT CREATE STARTED (DATA JSON): " . 
                    json_encode($dataJson) . 
                    ")", 
                    CLogger::LEVEL_TRACE
                );
                
                if (!empty($_FILES) && $globalresponse->success) {
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " DOCUMENT CREATE STARTED ($ globalresponse->success): " .
                        $globalresponse->success . 
                        ")", 
                        CLogger::LEVEL_TRACE
                    );
                    
                    foreach ($_FILES as $key => $file) {
                        $uniqueid = uniqid();

                        $dataJson['filename'] = $crmid . "_" . $uniqueid . 
                            "_" . $file['name'];
                        $dataJson['filesize'] = $file['size'];
                        $dataJson['filetype'] = $file['type'];

                        //Log
                        Yii::log(
                            " TRACE(" . $this->_traceId . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " DOCUMENT CREATE STARTED (FILE NAME): " .
                            $dataJson['filename'] . 
                            ")", 
                            CLogger::LEVEL_TRACE
                        );
                        //Upload file to Amazon S3
                        $sThree = new AmazonS3();
                        $sThree->set_region(
                            constant("AmazonS3::" . Yii::app()->params->awsS3Region)
                        );

                        $response = $sThree->create_object(
                            Yii::app()->params->awsS3Bucket, 
                            $crmid . '_' . $uniqueid . '_' . $file['name'], 
                            array(
                                'fileUpload' => $file['tmp_name'],
                                'contentType' => $file['type'],
                                'headers' => array(
                                    'Cache-Control' => 'max-age',
                                    'Content-Language' => 'en-US',
                                    'Expires' =>
                                    'Thu, 01 Dec 1994 16:00:00 GMT',
                                )
                            )
                        );
                        
                        //Log
                        Yii::log(
                            " TRACE(" . $this->_traceId . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " DOCUMENT CREATE STARTED (S3 Response): " .
                            json_encode($response) . 
                            ")", 
                            CLogger::LEVEL_TRACE
                        );

                        if ($response->isOK()) {

                            //Log
                            Yii::log(
                                " TRACE(" . $this->_traceId . "); " . 
                                " FUNCTION(" . __FUNCTION__ . "); " . 
                                " PROCESSING REQUEST (sending POST request" .
                                " to vt url: " . 
                                $this->_vtresturl . "  " .
                                json_encode(
                                    array(
                                        'sessionName' => $this->_session->sessionName,
                                        'operation' => 'create',
                                        'element' => json_encode($dataJson),
                                        'elementType' => 'Documents'
                                    )                           
                                ) . ")", 
                                CLogger::LEVEL_TRACE
                            );

                            //Create document
                            $rest = new RESTClient();

                            $rest->format('json');
                            $document = $rest->post(
                                $this->_vtresturl, array(
                                    'sessionName' => $this->_session->sessionName,
                                    'operation' => 'create',
                                    'element' =>
                                    json_encode($dataJson),
                                    'elementType' => 'Documents'
                                )
                            );

                            //Log
                            Yii::log(
                                " TRACE(" . $this->_traceId . "); " . 
                                " FUNCTION(" . __FUNCTION__ . "); " . 
                                " PROCESSING REQUEST (response received: " . 
                                $document . ")", 
                                CLogger::LEVEL_TRACE
                            );

                            $document = json_decode($document);
                            if ($document->success) {
                                $notesid = $document->result->id;

                                //Log
                                Yii::log(
                                    " TRACE(" . $this->_traceId . "); " . 
                                    " FUNCTION(" . __FUNCTION__ . "); " . 
                                    " PROCESSING REQUEST (sending POST " .
                                    "request to vt url: " . 
                                    $this->_vtresturl . "  " .
                                    json_encode(
                                        array(
                                            'sessionName' => $this->_session->sessionName,
                                            'operation' =>
                                            'relatetroubleticketdocument',
                                            'crmid' => $crmid,
                                            'notesid' => $notesid
                                        )
                                    ) . ")", 
                                    CLogger::LEVEL_TRACE
                                );

                                //Relate Document with Trouble Ticket
                                $rest = new RESTClient();

                                $rest->format('json');
                                $response = $rest->post(
                                    $this->_vtresturl, 
                                    array(
                                        'sessionName' => $this->_session->sessionName,
                                        'operation' =>
                                        'relatetroubleticketdocument',
                                        'crmid' => $crmid,
                                        'notesid' => $notesid
                                    )
                                );

                                //Log
                                Yii::log(
                                    " TRACE(" . $this->_traceId . "); " . 
                                    " FUNCTION(" . __FUNCTION__ . "); " . 
                                    " PROCESSING REQUEST (response received: " . 
                                    $response . ")", 
                                    CLogger::LEVEL_TRACE
                                );

                                $response = json_decode($response);
                                if ($response->success) {
                                    $globalresponse->result->documents[]
                                        = $document->result;
                                    $globalresponse->result->message[] = 'File' .
                                        ' (' . $file['name'] . ') updated.';
                                } else {
                                    $globalresponse->result->message[] = 'not' .
                                        ' uploaded - relating ' .
                                        'document failed:' . $file['name'];
                                }
                            } else {
                                $globalresponse->result->message[] = 'not' . 
                                    ' uploaded - creating document failed:' . 
                                    $file['name'];
                            }
                        } else {
                            $globalresponse->result->message[] = 'not' .
                                ' uploaded - upload to storage ' .
                                'service failed:' . $file['name'];
                        }
                    }
                }

                // Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " DOCUMENT CREATE STARTED (FILE SAVE): " .
                    $globalresponse->result->document_stats . 
                    ")", 
                    CLogger::LEVEL_TRACE
                );
                
                $globalresponse = json_encode($globalresponse);
                $globalresponse = json_decode($globalresponse, true);

                $customFields = Yii::app()->params[$this->_clientid . 
                    '_custom_fields']['HelpDesk'];


                unset($globalresponse['result']['update_log']);
                unset($globalresponse['result']['hours']);
                unset($globalresponse['result']['days']);
                unset($globalresponse['result']['modifiedtime']);
                unset($globalresponse['result']['from_portal']);
                unset($globalresponse['result']['documents']);
                
                foreach ($globalresponse['result'] as $fieldname => $value) {
                    $keyToReplace = array_search($fieldname, $customFields);
                    if ($keyToReplace) {
                        unset($globalresponse['result'][$fieldname]);
                        $globalresponse['result'][$keyToReplace] = $value;
                        //unset($customFields[$keyToReplace]);
                    }
                }
                
                if ($post['ticketstatus'] != 'Closed') {
                    $email = new AmazonSES();
                    //$email->set_region(constant("AmazonSES::" . 
                    //Yii::app()->params->awsSESRegion));

                    if ($globalresponse['result']['drivercauseddamage']=='Yes')
                        $globalresponse['result']['drivercauseddamage'] == 'Ja';

                    if ($globalresponse['result']['drivercauseddamage']=='No')
                        $globalresponse['result']['drivercauseddamage'] == 'Nej';

                    $sesBody = 'Hej ' . $this->_session->contactname .
                        ', ' . PHP_EOL .
                        PHP_EOL .
                        'En skaderapport har skapats.' . PHP_EOL .
                        PHP_EOL .
                        'Datum och tid: ' . date("Y-m-d H:i") . PHP_EOL .
                        'Ticket ID: ' . 
                        $globalresponse['result']['ticket_no'] . PHP_EOL .
                        PHP_EOL .
                        '- Besiktningsuppgifter -' . PHP_EOL .
                        'Trailer ID: ' . 
                        $globalresponse['result']['trailerid'] . PHP_EOL .
                        'Plats: ' . 
                        $globalresponse['result']['damagereportlocation'] . 
                        PHP_EOL .
                        'Plomerad: ' . $globalresponse['result']['sealed'] . 
                        PHP_EOL;

                    if($globalresponse['result']['sealed'] == 'No' || 
                        $globalresponse['result']['sealed'] == 'Nej')
                        $sesBody .= 'Skivor: ' . 
                            $globalresponse['result']['plates'] . PHP_EOL .
                            'Spännband: ' . $globalresponse['result']['straps'] . 
                            PHP_EOL;

                    $sesBody .= PHP_EOL .
                        '- Skadeuppgifter -' . PHP_EOL .
                        'Position: ' . $globalresponse['result']['damageposition'] .
                        PHP_EOL .
                        'Skada orsakad av chaufför: ' . 
                        $globalresponse['result']['drivercauseddamage'] . PHP_EOL .
                        PHP_EOL .
                        PHP_EOL .
                        '--' .
                        PHP_EOL .
                        'Gizur Admin';

                    $sesResponse = $email->send_email(
                        Yii::app()->params->awsSESFromEmailAddress, 
                        array(
                            'ToAddresses' => array(// Destination (aka To)
                                $_SERVER['HTTP_X_USERNAME']
                            )
                        ), 
                        array(// sesMessage (short form)
                            'Subject.Data' => date("F j, Y") . 
                            ': Besiktningsprotokoll för  ' . 
                            $globalresponse['result']['ticket_no'],
                            'Body.Text.Data' => $sesBody
                        )
                    );
                }

                // Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " DOCUMENT CREATE STARTED (UPDATE DYNAMODB SAVE): " .
                    ")", 
                    CLogger::LEVEL_TRACE
                );
                
                //Save result to DynamoDB
                $dynamodb = new AmazonDynamoDB();
                $dynamodb->set_region(
                    constant(
                        "AmazonDynamoDB::" .
                        Yii::app()->params->awsDynamoDBRegion
                    )
                );
                
                $ddbResponse = $dynamodb->put_item(
                    array(
                        'TableName' => Yii::app()->params->awsErrorDynamoDBTableName,
                        'Item' => $dynamodb->attributes(array(
                            "id" => uniqid(''),
                            "username" => $_SERVER['HTTP_X_USERNAME'],
                            //"data" => json_encode($globalresponse),
                            "ticket_no" => $globalresponse['result']['ticket_no'],
                            "clientid" => $this->_clientid,
                            "message" => json_encode($globalresponse['result']['message']),
                            "datetime" => strtotime("now")
                        ))
                    )
                );

                // Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " DYNAMODB UPDATED: " . json_encode($ddbResponse) .
                    ")", 
                    CLogger::LEVEL_TRACE
                );
                break;

            default :
                $response = new stdClass();
                $response->success = false;
                $response->error->code = $this->_errors[1004];
                $response->error->message = "Not a valid method" .
                        " for model " . $_GET['model'];
                $response->error->trace_id = $this->_traceId;
                $this->_sendResponse(405, json_encode($response));
                break;
            }
        } catch (Exception $e) {
            
            if (isset($this->_vtresponse->error->code))
                if ($this->_vtresponse->error->code == 'AUTHENTICATION_REQUIRED')
                    Yii::app()->cache->delete($this->_cacheKey);
            
            $response = new stdClass();
            $response->success = false;
            $response->error->code = $this->_errors[$e->getCode()];
            $response->error->message = $e->getMessage();
            $response->error->trace_id = $this->_traceId;
            $this->_sendResponse(400, json_encode($response));
        }
    }
    
    /**
     * Action for common Error
     * 
     * This action is executed when none of the patterns match in URL Manager 
     * router. If the model mentioned is correct the error must be because
     * of incorrect method used, else we can be sure that the requested service
     * is not provided by the API. 
     *
     * @return appropriate error message
     */
    public function actionError()
    {
        
        Yii::log(
            "TRACE(" . $this->_traceId . "); FUNCTION(" .
            __FUNCTION__ . "); ERROR IN REQUEST ",
            CLogger::LEVEL_TRACE
        );
        
        $response = new stdClass();
        $response->success = false;
        if (isset($this->_validModels[$_GET['model']])) {
            $response->error->code = $this->_errors[1004];
            $response->error->message = "Not a valid method" .
                    " for model " . $_GET['model'];
            $response->error->trace_id = $this->_traceId;
            $this->_sendResponse(405, json_encode($response));
        } else {
            $response->error->code = "NOT_FOUND";
            $response->error->message = "Such a service is not provided by" .
                    " this REST service";
            $response->error->trace_id = $this->_traceId;
            $this->_sendResponse(404, json_encode($response));
        }
    }

    /**
     * Action for updating record for various model
     * 
     * This action handles the following actions:
     * - User
     *       Request Method: PUT
     *       Response Type : json
     *       Subaction: ($field/$email|)
     *       Notes: Users data is stored in Amazon's Dynamo DB. Field can take 
     *       two values keypair1 and keypair2 . $email should be address id of
     *       the user. In this case the keypair is changed to a new keypair i.e.
     *       the GIZURCLOUD_API_KEY AND GIZURCLOUD_SECRET_KEY. In case the 
     *       $field is not set the value provided in the body is just replaced
     *       with a new value for the given field.
     * - Cron
     *       Request Method: PUT
     *       Response Type : json
     *       Subaction     : mailscanner  
     *       Notes: Accepts request from the node.js server (Heroku based), and 
     *       executes the mail scanning cron
     * - Authenticate
     *       Request Method: PUT
     *       Response Type : json
     *       Notes: Change and reset the password. In case of reset password
     *       username and password is not required. This is provided by vtiger
     *       custom made webservice.
     * - Helpdesk
     *       Request Method: PUT
     *       Response Type : json
     *       Notes: Changes the ticketstatus to 'Closed' or 'Open'
     * - Assets
     *       Request Method: PUT
     *       Response Type : json
     *       Notes: Changes the status of Asset to 'In-Service' or 
     *      'Out-Of-Service'
     * 
     * @return appropriate error message
     */    
    public function actionUpdate()
    {
        //Tasks include detail updating Troubleticket
        try {
            
            Yii::log(
                "TRACE(" . $this->_traceId . "); FUNCTION(" .
                __FUNCTION__ . "); PROCESSING REQUEST ", 
                CLogger::LEVEL_TRACE
            );
            
            switch ($_GET['model']) {
                /*
                 * ************************************************************
                 * ************************************************************
                 * * Cron MODEL
                 * * Accepts as action mailscan
                 * ************************************************************
                 * ************************************************************
                 */
            case 'Cron':
                
                if ($_GET['action'] == 'mailscan') {
                    
                    $filename = Yii::app()->params->vtCronPath . 
                        'MailScannerCron.sh';
                    $response = new stdClass();
                    if (file_exists($filename))
                        $response->fileexists = true;
                    else 
                        $response->fileexist = false;
                   
                    if (is_executable($filename)) {
                        $response->executable = true;
                    } else {
                        $response->executable = false;
                        chmod($filename, 0755);
                    }
                    
                    $response->filename = $filename;   
                    $response->result = shell_exec($filename);
                   
                    $this->_sendResponse(200, json_encode($response));
                }
                
                if ($_GET['action'] == 'dbbackup') {
                    
                    $httpStatus = 200;
                    
                    $path = Yii::getPathOfAlias('application') . '/data/';
                    $filename = 'backup-' . $this->_clientid . 
                        "_" . date("c") . '.sql';
                    
                    $command = "mysqldump --host={$this->_dbhost} -u " .
                        "{$this->_dbuser} -p{$this->_dbpassword} " .
                        "{$this->_dbname}> {$path}{$filename}";
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " Command to be executed " . 
                        $command . ")", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    $response->result = shell_exec($command);
                    $response->filename = $filename;
                    $response->command = $command;
                    $response->bucketname = Yii::app()->params->awsS3BackupBucket;
                    
                    if (file_exists($path.$filename)) {
                        
                        //Log
                        Yii::log(
                            " TRACE(" . $this->_traceId . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " File has been created " . 
                            $filename . ")", 
                            CLogger::LEVEL_TRACE
                        );                        
                        
                        //Upload file to Amazon S3
                        $sThree = new AmazonS3();
                        $sThree->set_region(
                            constant(
                                "AmazonS3::" . Yii::app()->params->awsS3Region
                            )
                        );

                        $responseSThree = $sThree->create_object(
                            Yii::app()->params->awsS3BackupBucket, 
                            $filename, 
                            array(
                                'fileUpload' => $path.$filename,
                                'contentType' => 'plain/text',
                                'headers' => array(
                                    'Cache-Control' => 'max-age',
                                    'Content-Language' => 'en-US',
                                    'Expires' =>
                                    'Thu, 01 Dec 1994 16:00:00 GMT',
                                )
                            )
                        );

                        if ($responseSThree->isOK()) {
                            $response->success = true;
                        } else {
                            $response->error = "Unable to upload file to server";
                            $response->xml = $responseSThree->body->asXML();
                            $response->success = false;
                            $httpStatus = 500;
                        }
                        
                        unlink($path . $filename);
                        
                    } else {
                        $response->error = "Unable to create file";
                        $response->success = false;
                        $httpStatus = 500;
                    }
                    
                    $this->_sendResponse($httpStatus, json_encode($response));
                }                
                
                break;
                /*
                 * *************************************************************
                 * *************************************************************
                 * * Authenticate MODEL
                 * * Accepts reset / changepw
                 * *************************************************************
                 * *************************************************************
                 */
            case 'Authenticate':
                if ($_GET['action'] == 'reset') {

                    $email = new AmazonSES();
                    //$email->set_region(constant("AmazonSES::" . 
                    //Yii::app()->params->awsSESRegion));
                    $response = $email->list_verified_email_addresses();

                    if ($response->isOK()) {
                        $verifiedEmailAddresses = (Array) $response->body->ListVerifiedEmailAddressesResult->VerifiedEmailAddresses;
                        $verifiedEmailAddresses = $verifiedEmailAddresses['member'];
                        
                        //Log
                        Yii::log(
                            " TRACE(" . $this->_traceId . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " PROCESSING REQUEST (List of Verified Email" .
                            " Addresses: " . 
                            json_encode($verifiedEmailAddresses) . 
                            "  From Email Address" .
                            json_encode(Yii::app()->params->awsSESFromEmailAddress) .                            
                            ")", 
                            CLogger::LEVEL_TRACE
                        );        
                        
                        if (!is_array($verifiedEmailAddresses)) {
                            $verifiedEmailAddresses = (array)$verifiedEmailAddresses;
                        }
                        
                        if (in_array(Yii::app()->params->awsSESFromEmailAddress, $verifiedEmailAddresses) == false) {
                            $email->verify_email_address(Yii::app()->params->awsSESFromEmailAddress);
                            throw new Exception(
                                'From Email Address not verified. ' .
                                'Contact Gizur Admin.'
                            );
                        }
                    }

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending POST request " .
                        "to vt url: " . 
                        $this->_vtresturl . "  " .
                        json_encode(
                            array(
                                'operation' => 'resetpassword',
                                'username' => $_SERVER['HTTP_X_USERNAME'],
                            )                     
                        ) .                            
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                      
                    
                    //Receive response from vtiger REST service
                    //Return response to client  
                    $rest = new RESTClient();
                
                    $rest->format('json');
                    $response = $rest->post(
                        $this->_vtresturl, 
                        array(
                            'operation' => 'resetpassword',
                            'username' => $_SERVER['HTTP_X_USERNAME'],
                        )
                    );
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $response .                          
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    $response = json_decode($response);

                    if ($response->success == false)
                        throw new Exception("Unable to reset password");

                    $sesResponse = $email->send_email(
                        Yii::app()->params->awsSESFromEmailAddress,
                        array(
                            'ToAddresses' => array(
                                $_SERVER['HTTP_X_USERNAME']
                            )
                        ), 
                        array(// Message (short form)
                            'Subject.Data' => 'Your Gizur Account password has been reset',
                            'Body.Text.Data' => 'Dear Gizur Account Holder, ' .
                            PHP_EOL .
                            PHP_EOL .
                            'Your password has been reset to: ' .
                            $response->result->newpassword .
                            PHP_EOL .
                            'Please change it the next time you login.' .
                            PHP_EOL .
                            PHP_EOL .
                            '--' .
                            PHP_EOL .
                            ' Gizur Admin'
                        )
                    );
                    if ($sesResponse->isOK()) {
                        $this->_sendResponse(200, json_encode($response));
                    } else {
                        throw new Exception(
                            'Password has been reset but unable to send email.'
                        );
                    }
                }

                if ($_GET['action'] == 'changepw') {
                    $_PUT = Array();
                    parse_str(file_get_contents('php://input'), $_PUT);
                    if (!isset($_PUT['newpassword']))
                        throw new Exception('New Password not provided.');
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending POST request " .
                        "to vt url: " . 
                        $this->_vtresturl . "  " .
                        json_encode(
                            array(
                                'sessionName' => $this->_session->sessionName,
                                'operation' => 'changepw',
                                'username' => $_SERVER['HTTP_X_USERNAME'],
                                'oldpassword' => $_SERVER['HTTP_X_PASSWORD'],
                                'newpassword' => $_PUT['newpassword']
                            )                  
                        ) .                            
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                       
                    
                    //Receive response from vtiger REST service
                    //Return response to client  
                    $rest = new RESTClient();
                
                    $rest->format('json');
                    $response = $rest->post(
                        $this->_vtresturl, array(
                            'sessionName' => $this->_session->sessionName,
                            'operation' => 'changepw',
                            'username' => $_SERVER['HTTP_X_USERNAME'],
                            'oldpassword' => $_SERVER['HTTP_X_PASSWORD'],
                            'newpassword' => $_PUT['newpassword']
                        )
                    );
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $response .                          
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    $response = json_decode($response);
                    if ($response->success == false)
                        throw new Exception($response->error->message);
                    
                    Yii::app()->cache->delete($this->_cacheKey);
                    
                    $this->_sendResponse(200, json_encode($response));
                }
                /*
                 * *************************************************************
                 * *************************************************************
                 * * User MODEL
                 * * Accepts id
                 * *************************************************************
                 * *************************************************************
                 */
            case 'User':
                if (isset($_GET['field'])) {
                    $keyid = str_replace('keypair', '', $_GET['field']);

                    //It match username sent in the header and email
                    //sent in the GET request
                    if($_SERVER['HTTP_X_USERNAME'] !== $_GET['email'])
                        throw new Exception("Credentials are invalid.", 2004);
                    
                    // Instantiate the class
                    $dynamodb = new AmazonDynamoDB();
                    $dynamodb->set_region(
                        constant(
                            "AmazonDynamoDB::" . 
                            Yii::app()->params->awsDynamoDBRegion
                        )
                    );

                    // Get an item
                    $ddbResponse = $dynamodb->get_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'Key' => $dynamodb->attributes(
                                array(
                                    'HashKeyElement' => $_GET['email'],
                                )
                            ),
                            'ConsistentRead' => 'true'
                        )
                    );

                    foreach ($ddbResponse->body->Item->children()
                    as $key => $item) {
                        $result[$key] 
                            = (string) $item->{AmazonDynamoDB::TYPE_STRING};
                    }


                    Yii::app()->cache->delete($result['apikey_' . $keyid]);

                    /* Create the private and public key */
                    $result['secretkey_' . $keyid] = uniqid("", true) .
                            uniqid("", true);
                    $result['apikey_' . $keyid] = strtoupper(
                        uniqid("GZCLD" . uniqid())
                    );

                    Yii::app()->cache->set(
                        $result['apikey_' . $keyid],
                        $result['secretkey_' . $keyid]
                    );

                    $ddbResponse = $dynamodb->put_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'Item' => $dynamodb->attributes($result)
                        )
                    );


                    if ($response->success = $ddbResponse->isOK())
                        $response->result = $result;

                    $this->_sendResponse(200, json_encode($response));
                } else {
                    $post = json_decode(file_get_contents('php://input'), true);
                    // Instantiate the class
                    $dynamodb = new AmazonDynamoDB();
                    $dynamodb->set_region(
                        constant(
                            "AmazonDynamoDB::" .
                            Yii::app()->params->awsDynamoDBRegion
                        )
                    );
                    
                    // Get an item
                    $ddbResponse = $dynamodb->get_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'Key' => $dynamodb->attributes(
                                array(
                                    'HashKeyElement' => $post['id'],
                                )
                            ),
                            'ConsistentRead' => 'true'
                        )
                    );
                    
                    $oldClientId = (string)$ddbResponse->body->Item->clientid->{AmazonDynamoDB::TYPE_STRING};
                    
                    if($oldClientId == $post['clientid'])
                        $noToMatch = 1;
                    else
                        $noToMatch = 0;
                    
                    //Validate Client ID
                    $ddbResponse = $dynamodb->scan(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'AttributesToGet' => array('clientid'),
                            'ScanFilter' => array(
                                'clientid' => array(
                                    'ComparisonOperator' => AmazonDynamoDB::CONDITION_EQUAL,
                                    'AttributeValueList' => array(
                                        array( AmazonDynamoDB::TYPE_STRING => $post['clientid'] )
                                    )
                                )
                            )
                        )
                    );
                    
                    if(!empty($ddbResponse->body->Items) && $ddbResponse->body->Count > $noToMatch)
                        throw New Exception("Client id is not available.", 2001);
                    
                    $ddbResponse = $dynamodb->put_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'Item' => $dynamodb->attributes($post)
                        )
                    );
                    $response = new stdClass();
                    $response->success = $ddbResponse->isOK();
                    $this->_sendResponse(200, json_encode($response));
                }
                break;
                /*
                 * *************************************************************
                 * *************************************************************
                 * * HelpDesk MODEL
                 * * Accepts id
                 * *************************************************************
                 * *************************************************************
                 */
            case 'HelpDesk':

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending GET request to vt url: " . 
                        $this->_vtresturl . "  " .
                        json_encode(
                            array(
                                'sessionName' => $this->_session->sessionName,
                                'operation' => 'retrieve',
                                'id' => $_GET['id']
                            )                  
                        ) .                            
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                  
                
                    //Receive response from vtiger REST service
                    //Return response to client  
                    $rest = new RESTClient();
                
                    $rest->format('json');
                    $response = $rest->get(
                        $this->_vtresturl, array(
                            'sessionName' => $this->_session->sessionName,
                            'operation' => 'retrieve',
                            'id' => $_GET['id']
                        )
                    );

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $response .                          
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    $response = json_decode($response, true);

                    //get data json 
                    $retrivedObject = $response['result'];
                    
                    /*
                     * UPDATE DAMAGE STATUS AND NOTES
                     * UPPER CODE IS COMMON FOR BOTH
                     */
                    
                    if($_GET['action'] == 'updatedamagenotes') {
                        $_PUT = Array();
                        parse_str(file_get_contents('php://input'), $_PUT);
                        
                        $customFields = Yii::app()->params[$this->_clientid . 
                        '_custom_fields']['HelpDesk'];
                        
                        $retrivedObject[$customFields['damagestatus']] = $_PUT['damagestatus'];
                        $retrivedObject[$customFields['notes']] = $_PUT['notes'];
                    } else {
                        $retrivedObject['ticketstatus'] = 'Closed';
                    }
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending POST request to vt url: " . 
                        $this->_vtresturl . "  " .
                        json_encode(
                            array(
                                'sessionName' => $this->_session->sessionName,
                                'operation' => 'update',
                                'element' => json_encode($retrivedObject)
                            )                  
                        ) .                            
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                           
                    
                    //Receive response from vtiger REST service
                    //Return response to client  
                    $rest = new RESTClient();
                
                    $rest->format('json');
                    $response = $rest->post(
                        $this->_vtresturl, array(
                            'sessionName' => $this->_session->sessionName,
                            'operation' => 'update',
                            'element' => json_encode($retrivedObject)
                        )
                    );

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $response .                          
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    $response = json_decode($response, true);

                    $customFields = Yii::app()->params[$this->_clientid . 
                        '_custom_fields']['HelpDesk'];


                    unset($response['result']['update_log']);
                    unset($response['result']['hours']);
                    unset($response['result']['days']);
                    unset($response['result']['modifiedtime']);
                    unset($response['result']['from_portal']);
                    foreach ($response['result'] as $fieldname => $value) {
                        $keyToReplace = array_search($fieldname, $customFields);
                        if ($keyToReplace) {
                            unset($response['result'][$fieldname]);
                            $response['result'][$keyToReplace] = $value;
                            //unset($customFields[$keyToReplace]);                                
                        }
                    }

                    $this->_sendResponse(200, json_encode($response));

                break;
                /*
                 * *************************************************************
                 * *************************************************************
                 * * HelpDesk MODEL
                 * * Accepts id
                 * *************************************************************
                 * *************************************************************
                 */
            case 'Assets':
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (sending GET request to vt url: " . 
                    $this->_vtresturl . "  " .
                    json_encode(
                        array(
                            'sessionName' => $this->_session->sessionName,
                            'operation' => 'retrieve',
                            'id' => $_GET['id']
                        )                  
                    ) .                            
                    ")", 
                    CLogger::LEVEL_TRACE
                );                  
                
                //Receive response from vtiger REST service
                //Return response to client  
                $rest = new RESTClient();
                
                $rest->format('json');

                $_PUT = Array();
                parse_str(file_get_contents('php://input'), $_PUT);

                $response = $rest->get(
                    $this->_vtresturl, array(
                        'sessionName' => $this->_session->sessionName,
                        'operation' => 'retrieve',
                        'id' => $_GET['id']
                    )
                );

                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $response .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                );                
                
                $response = json_decode($response, true);

                //get data json 
                $retrivedObject = $response['result'];
                if ($_PUT['assetstatus'] == 'In Service')
                    $retrivedObject['assetstatus'] = 'In Service';
                else
                    $retrivedObject['assetstatus'] = 'Out-of-service';

                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (sending POST request to vt url: " . 
                    $this->_vtresturl . "  " .
                    json_encode(
                        array(
                            'sessionName' => $this->_session->sessionName,
                            'operation' => 'retrieve',
                            'id' => $_GET['id']
                        )                  
                    ) .                            
                    ")", 
                    CLogger::LEVEL_TRACE
                );                 
                
                //Receive response from vtiger REST service
                //Return response to client  
                $rest = new RESTClient();
                
                $rest->format('json');
                $response = $rest->post(
                    $this->_vtresturl, array(
                        'sessionName' => $this->_session->sessionName,
                        'operation' => 'update',
                        'element' => json_encode($retrivedObject)
                    )
                );

                //Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $response .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                );                
                
                $response = json_decode($response, true);

                if ($response['success'] == false)
                throw new Exception($response['error']['message']);

                $customFields = Yii::app()->params[$this->_clientid . 
                    '_custom_fields']['Assets'];

                unset($response['result']['update_log']);
                unset($response['result']['hours']);
                unset($response['result']['days']);
                unset($response['result']['modifiedtime']);
                unset($response['result']['from_portal']);
                
                foreach ($response['result'] as $fieldname => $value) {
                    $keyToReplace = array_search($fieldname, $customFields);
                    if ($keyToReplace) {
                        unset($response['result'][$fieldname]);
                        $response['result'][$keyToReplace] = $value;
                    }
                }

                $this->_sendResponse(200, json_encode($response));
                break;

            case 'DocumentAttachment' :

                // Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " In DocumentAttachment ", 
                    CLogger::LEVEL_TRACE
                );
                //Continue to run script even when the connection is over
                ignore_user_abort(true);
                set_time_limit(0);

                // buffer all upcoming output
                ob_start();

                $response = new stdClass();
                $response->success = true;
                $response->message = "Request received.";
                
                echo json_encode($response);

                // get the size of the output
                $size = ob_get_length();

                // send headers to tell the browser to close the connection
                header("Content-Length: $size");
                header('Connection: close');

                // flush all output
                ob_end_flush();
                ob_flush();
                flush();
                
                // close current session
                if (session_id()) session_write_close();
                
                // Loop through all Files
                // Attach file to trouble ticket
                $crmid = $_GET['id'];
                $ticket_no = $_POST['ticket_no'];
                
                $globalresponse->result->documents = Array();
                $globalresponse->result->message = Array();

                $dataJson = array(
                    'notes_title' => 'Attachement',
                    'assigned_user_id' => $this->_session->userId,
                    'notecontent' => 'Attachement',
                    'filelocationtype' => 'I',
                    'filedownloadcount' => null,
                    'filestatus' => 1,
                    'fileversion' => ''
                );

                $globalresponse =  new stdClass();
                
                foreach ($_FILES as $key => $file) {

                    $uniqueid = uniqid();

                    $dataJson['filename'] = $crmid . "_" . $uniqueid . 
                        "_" . $file['name'];
                    $dataJson['filesize'] = $file['size'];
                    $dataJson['filetype'] = $file['type'];

                    // Log
                    Yii::log(
                        " TRACE(" . $this->_traceId . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " SAVING FILE " . $file['name'] . " TO S3", 
                        CLogger::LEVEL_TRACE
                    );
                
                    // Upload file to Amazon S3
                    $sThree = new AmazonS3();
                    $sThree->set_region(
                        constant("AmazonS3::" . Yii::app()->params->awsS3Region)
                    );

                    $response = $sThree->create_object(
                        Yii::app()->params->awsS3Bucket, 
                        $crmid . '_' . $uniqueid . '_' . $file['name'], 
                        array(
                            'fileUpload' => $file['tmp_name'],
                            'contentType' => $file['type'],
                            'headers' => array(
                                'Cache-Control' => 'max-age',
                                'Content-Language' => 'en-US',
                                'Expires' =>
                                'Thu, 01 Dec 1994 16:00:00 GMT',
                            )
                        )
                    );

                    if ($response->isOK()) {
                        
                        //Log
                        Yii::log(
                            " TRACE(" . $this->_traceId . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " PROCESSING REQUEST (sending POST request" .
                            " to vt url: " . 
                            $this->_vtresturl . "  " .
                            json_encode(
                                array(
                                    'sessionName' => $this->_session->sessionName,
                                    'operation' => 'create',
                                    'element' => json_encode($dataJson),
                                    'elementType' => 'Documents'
                                )                           
                            ) . ")", 
                            CLogger::LEVEL_TRACE
                        );
                        
                        //Create document
                        $rest = new RESTClient();
                
                        $rest->format('json');
                        $document = $rest->post(
                            $this->_vtresturl, array(
                                'sessionName' => $this->_session->sessionName,
                                'operation' => 'create',
                                'element' =>
                                json_encode($dataJson),
                                'elementType' => 'Documents'
                            )
                        );
                        
                        //Log
                        Yii::log(
                            " TRACE(" . $this->_traceId . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " PROCESSING REQUEST (response received: " . 
                            $document . ")", 
                            CLogger::LEVEL_TRACE
                        );
                        
                        $document = json_decode($document);
                        if ($document->success) {
                            $notesid = $document->result->id;
                            
                            //Log
                            Yii::log(
                                " TRACE(" . $this->_traceId . "); " . 
                                " FUNCTION(" . __FUNCTION__ . "); " . 
                                " PROCESSING REQUEST (sending POST " .
                                "request to vt url: " . 
                                $this->_vtresturl . "  " .
                                json_encode(
                                    array(
                                        'sessionName' => $this->_session->sessionName,
                                        'operation' =>
                                        'relatetroubleticketdocument',
                                        'crmid' => $crmid,
                                        'notesid' => $notesid
                                    )
                                ) . ")", 
                                CLogger::LEVEL_TRACE
                            );

                            //Relate Document with Trouble Ticket
                            $rest = new RESTClient();
                
                            $rest->format('json');
                            $response = $rest->post(
                                $this->_vtresturl, 
                                array(
                                    'sessionName' => $this->_session->sessionName,
                                    'operation' =>
                                    'relatetroubleticketdocument',
                                    'crmid' => $crmid,
                                    'notesid' => $notesid
                                )
                            );
                            
                            //Log
                            Yii::log(
                                " TRACE(" . $this->_traceId . "); " . 
                                " FUNCTION(" . __FUNCTION__ . "); " . 
                                " PROCESSING REQUEST (response received: " . 
                                $response . ")", 
                                CLogger::LEVEL_TRACE
                            );
                            
                            $response = json_decode($response);
                            if ($response->success) {
                                $globalresponse->result->documents[]
                                    = $document->result;
                                $globalresponse->result->message[] = 'File ' .
                                    ' (' . $file['name'] . ') updated.';
                            } else {
                                $globalresponse->result->message[] = 'not' .
                                    ' uploaded - relating ' .
                                    'document failed:' . $file['name'];
                            }
                        } else {
                            $globalresponse->result->message[] = 'not uploaded' .
                                ' - creating document failed:' . $file['name'];
                        }
                    } else {
                        $globalresponse->result->message[] = 'not uploaded - ' .
                            'upload to storage service failed:' . $file['name'];
                    }                    
                }

                $globalresponse = json_encode($globalresponse);
                $globalresponse = json_decode($globalresponse, true);
                
                // Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " Image saved at S3: " . json_encode($globalresponse) .
                    ")", 
                    CLogger::LEVEL_TRACE
                );
                
                $dynDB = array(
                    "id" => uniqid(''),
                    "username" => $_SERVER['HTTP_X_USERNAME'],
                    //"data" => json_encode($globalresponse),
                    "ticket_no" => $ticket_no,
                    "clientid" => $this->_clientid,
                    "message" => json_encode($globalresponse['result']['message']),
                    "datetime" => strtotime("now")
                );
                // Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " UPDATING DYNAMODB : " . json_encode($dynDB), 
                    CLogger::LEVEL_TRACE
                );
                //Save result to DynamoDB
                $dynamodb = new AmazonDynamoDB();
                $dynamodb->set_region(
                    constant(
                        "AmazonDynamoDB::" .
                        Yii::app()->params->awsDynamoDBRegion
                    )
                );
                
                $ddbResponse = $dynamodb->put_item(
                    array(
                        'TableName' => Yii::app()->params->awsErrorDynamoDBTableName,
                        'Item' => $dynamodb->attributes($dynDB)
                    )
                );

                // Log
                Yii::log(
                    " TRACE(" . $this->_traceId . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " DYNAMODB UPDATED: " . json_encode($ddbResponse) .
                    ")", 
                    CLogger::LEVEL_TRACE
                );
                break;

            default :
                $response = new stdClass();
                $response->success = false;
                $response->error->code = $this->_errors[1004];
                $response->error->message = "Not a valid method" .
                        " for model ";
                $response->error->trace_id = $this->_traceId;
                $this->_sendResponse(405, json_encode($response));
                break;
            }
        } catch (Exception $e) {
            
            if (isset($this->_vtresponse->error->code))
                if ($this->_vtresponse->error->code == 'AUTHENTICATION_REQUIRED')
                    Yii::app()->cache->delete($this->_cacheKey);            
            
            $response = new stdClass();
            $response->success = false;
            $response->error->code = "ERROR";
            $response->error->message = $e->getMessage();
            $response->error->trace_id = $this->_traceId;
            $this->_sendResponse(400, json_encode($response));
        }
    }
}
