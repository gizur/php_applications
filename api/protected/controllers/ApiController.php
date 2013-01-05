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
 * (GET|POST) /url/to/gizur/rest/api/$model/($id|$fieldname|$action|$category) HTTP/1.1
 * Host: giruz.com
 * Http_x_username: $username
 * Http_x_password: $password
 * Http_x_timestamp: $timestamp
 * Http_x_gizurcloud_api_key: $GIZURCLOUD_API_KEY
 * Http_x_signature: $signature
 * Http_x_unique_salt: $unique_string
 * User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:12.0) Gecko/20100101 Firefox/12.0
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
        1006 => "INVALID_SESSIONID"
    );

    /**
     * The vTiger REST Web Services Entities
     */
    private $_ws_entities = Array(
        'Documents' => 15,
        'Contacts' => 12
    );

    /**
     * List of valid models
     */
    private $_valid_models = Array(
        'User',
        'HelpDesk',
        'Assets',
        'About',
        'DocumentAttachments',
        'Authenticate',
        'Cron'
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
    private $_cache_key = "";
    
    /**
     * Trace ID
     */
    private $_trace_id = "";  
    
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
        $content_type = 'text/json'
    ) {
        // set the status
        $status_header = 'HTTP/1.1 ' . $status . ' '
                . ((isset($this->_codes[$status])) ? $this->_codes[$status] : '');
        header($status_header);

        // and the content type
        header('Content-type: ' . $content_type);

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
                $message 
                    = 'The server encountered an error processing your request.';
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
            <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
            <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
                <title>' . $status . ' ' . ((isset($this->_codes[$status])) ?
                            $codes[$status] : '') . '</title>
            </head>
            <body>
                <h1>' . ((isset($this->_codes[$status])) ? $codes[$status] : '') . '</h1>
                <h2> Trace ID:' . $this->_trace_id . '</h2>
                <p>' . $message . '</p>
                <hr />
                <address>' . $signature . '</address>
            </body>
            </html>';

            echo $body;
        }
        
        //Log
        Yii::log("TRACE(" . $this->_trace_id . "); FUNCTION(" . __FUNCTION__ . "); DISPATCH RESPONSE: " . $body, CLogger::LEVEL_TRACE);
        
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
            $this->_trace_id = uniqid();
            
            //Log
            Yii::log(
                " TRACE(" . $this->_trace_id . "); " . 
                " FUNCTION(" . __FUNCTION__ . "); " . 
                " RECEIVED REQUEST, STARTING VALIDATION " . json_encode($_SERVER) .
                " GET VAR " . json_encode($_GET), 
                CLogger::LEVEL_TRACE
            );
            
            //First we validate the requests using logic do not consume
            //resources 
            if ($_GET['model'] == 'User')
                return true;
            
            //Check Acceptable language of request
            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
                if (!is_null($_SERVER['HTTP_ACCEPT_LANGUAGE']))
                    if ($_SERVER['HTTP_ACCEPT_LANGUAGE'] != 'null')
                        if (strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'en')===false)
                            throw new Exception('Language not supported');
               
            //Check Acceptable mime-type of request    
            if (isset($_SERVER['HTTP_ACCEPT'])) {
                if (!is_null($_SERVER['HTTP_ACCEPT_LANGUAGE']))
                    if ($_SERVER['HTTP_ACCEPT_LANGUAGE'] != 'null')                
                        if (strpos($_SERVER['HTTP_ACCEPT'], 'json')===false) {
                            if (!(strpos($_SERVER['HTTP_ACCEPT'], 'html')!==false 
                                && $_GET['model'] == 'About'))
                                throw new Exception('Mime-Type not supported', 1005); 
                        }
            }
            
            //Check if timestamp is present in the header
            if (!isset($_SERVER['HTTP_X_TIMESTAMP']))
                throw new Exception('Timestamp not found in request');

            //Check if signature is present in the header
            if (!isset($_SERVER['HTTP_X_SIGNATURE']))
                throw new Exception('Signature not found');

            //Check if Unique Salt is present in request
            if (!isset($_SERVER['HTTP_X_UNIQUE_SALT']))
                throw new Exception('Unique Salt not found');

            //check if public key exists
            if (!isset($_SERVER['HTTP_X_GIZURCLOUD_API_KEY']))
                throw new Exception('Public Key Not Found in request');
            
            //Check if request is in acceptable timestamp negative error
            if ($_SERVER["REQUEST_TIME"] - Yii::app()->params->acceptableTimestampError > strtotime($_SERVER['HTTP_X_TIMESTAMP']))
                throw new Exception('Stale request', 1003);

            //Check if request is in acceptable timestamp positive error
            if ($_SERVER["REQUEST_TIME"] + Yii::app()->params->acceptableTimestampError < strtotime($_SERVER['HTTP_X_TIMESTAMP']))
                throw new Exception('Oh, Oh, Oh, request from the FUTURE! ', 1003);

            //Log
            Yii::log(
                " TRACE(" . $this->_trace_id . "); " . 
                " FUNCTION(" . __FUNCTION__ . "); " . 
                " VALIDATION (Fetch API Key details from Dynamodb, resource  intensive validation)", 
                CLogger::LEVEL_TRACE
            );

            //Fetch API Key details from Dynamodb, resource  intensive validation
            if (($GIZURCLOUD_SECRET_KEY = Yii::app()->cache->get($_SERVER['HTTP_X_GIZURCLOUD_API_KEY'])) === false) {
                // Retreive Key pair from Amazon Dynamodb
                $dynamodb = new AmazonDynamoDB();
                $dynamodb->set_region(constant("AmazonDynamoDB::" . Yii::app()->params->awsDynamoDBRegion));

                //Log
                Yii::log(
                    " TRACE(" . $this->_trace_id . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " VALIDATION (Scan API KEY 1)", 
                    CLogger::LEVEL_TRACE
                );
                
                //Scan for API KEYS
                $ddb_response = $dynamodb->scan(
                    array(
                    'TableName' => Yii::app()->params->awsDynamoDBTableName,
                    'AttributesToGet' => array('id', 'apikey_1', 'secretkey_1'),
                    'ScanFilter' => array(
                        'apikey_1' => array(
                            'ComparisonOperator' => AmazonDynamoDB::CONDITION_EQUAL,
                            'AttributeValueList' => array(
                                array(AmazonDynamoDB::TYPE_STRING => $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'])
                            )
                        )
                    )
                        )
                );
                
                //If API Keys are not found for apikey_1 then look in apikey_2
                //can this be done in a better way?
                if ($publicKeyNotFound = ($ddb_response->body->Count == 0)) {
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " VALIDATION (Scan API KEY 2)", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    //Scan for API KEYS
                    $ddb_response = $dynamodb->scan(
                        array(
                        'TableName' => Yii::app()->params->awsDynamoDBTableName,
                        'AttributesToGet' => array('id', 'apikey_2', 'secretkey_2'),
                        'ScanFilter' => array(
                            'apikey_2' => array(
                                'ComparisonOperator' => AmazonDynamoDB::CONDITION_EQUAL,
                                'AttributeValueList' => array(
                                    array(AmazonDynamoDB::TYPE_STRING => $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'])
                                )
                            )
                        )
                            )
                    );
                    
                    //Check if public key is found in apikey_2
                    if (!($publicKeyNotFound = ($ddb_response->body->Count == 0)))
                        $GIZURCLOUD_SECRET_KEY = (string) $ddb_response->body->Items->secretkey_2->{AmazonDynamoDB::TYPE_STRING};
                } else {
                    //Get secret key which belongs to apikey_1
                    $GIZURCLOUD_SECRET_KEY = (string) $ddb_response->body->Items->secretkey_1->{AmazonDynamoDB::TYPE_STRING};
                }

                //If public key is not found throw an exception
                if ($publicKeyNotFound)
                    throw new Exception('Could not identify public key'); 
                
                $this->_clientid = $ddb_response->body->Items->clientid->{AmazonDynamoDB::TYPE_STRING};
                    
                //Store the public key and secret key combination in cache to
                //avoid repeated calls to Dynamo DB
                Yii::app()->cache->set($_SERVER['HTTP_X_GIZURCLOUD_API_KEY'], $GIZURCLOUD_SECRET_KEY);
                Yii::app()->cache->set($_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] . "_clientid", $this->_clientid);
            } else {
                $this->_clientid = Yii::app()->cache->get($_SERVER['HTTP_X_GIZURCLOUD_API_KEY']);
            }
            
            //Check the string
            $this->_vtresturl = str_replace('{clientid}', $this->_clientid, Yii::app()->params->vtRestUrl);            
            
            //Log
            Yii::log(
                " TRACE(" . $this->_trace_id . "); " . 
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
            $string_to_sign = "";
            foreach ($params as $k => $v)
                $string_to_sign .= "{$k}{$v}";

            // Generate signature
            $verify_signature = base64_encode(hash_hmac('SHA256', $string_to_sign, $GIZURCLOUD_SECRET_KEY, 1));
            
            //Log
            Yii::log(
                " TRACE(" . $this->_trace_id . "); " . 
                " FUNCTION(" . __FUNCTION__ . "); " . 
                " VALIDATION (Signature Dump) STRING_TO_SIGN: $string_to_sign" .
                "    GENERATED SIGNATURE: " . $verify_signature .
                "    SIGNATURE RECEIVED:" . $_SERVER['HTTP_X_SIGNATURE'] , 
                CLogger::LEVEL_TRACE
            );             

            //Verify if the signature is valid
            if ($_SERVER['HTTP_X_SIGNATURE'] != $verify_signature)
                throw new Exception('Could not verify signature ');

            //Check if the signature has been used before
            //This is a security loop hole to reply attacks in case memcache
            //is not working
            if (Yii::app()->cache->get($_SERVER['HTTP_X_SIGNATURE']) !== false)
                throw new Exception('Used signature');

            //Save the signature for 10 minutes
            Yii::app()->cache->set($_SERVER['HTTP_X_SIGNATURE'], 1, 600);

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
            $this->_cache_key = json_encode(
                array(
                'instanceid' => $this->_instanceid,
                'username' => $_SERVER['HTTP_X_USERNAME'],
                'password' => $_SERVER['HTTP_X_PASSWORD']
                    )
            );

            $cache_value = false;
            
            //Check if the session stored in the cache key is valid 
            //as per vtiger a session can be valid till 1 day max
            //and unused session for 1800 seconds
            $last_used = Yii::app()->cache->get($this->_instanceid . "_last_used_" . $this->_cache_key);

            if ($last_used !== false) {
                if ($last_used < (time() - 1790)) {
                    Yii::app()->cache->delete($this->_cache_key);
                } else {
                    $cache_value = Yii::app()->cache->get($this->_cache_key);
                    Yii::app()->cache->set($this->_instanceid . "_last_used_" . $this->_cache_key, time());
                }
            }

            //Log
            Yii::log(
                " TRACE(" . $this->_trace_id . "); " . 
                " FUNCTION(" . __FUNCTION__ . "); " . 
                " VALIDATION (Logging into vtiger REST API or using preused session)", 
                CLogger::LEVEL_TRACE
            );              
            
            //Check if session was retrived from memcache
            if ($cache_value === false) {
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_trace_id . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " VALIDATION (No value in cache found: Logging in)" .
                    " (sending POST request to vt url: " .
                    $this->_vtresturl .
                    "?operation=logincustomer " . "username=" . $_SERVER['HTTP_X_USERNAME'] .
                    "&password=" . $_SERVER['HTTP_X_PASSWORD'] .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                );                
                
                //Get the Access Key and the Username from vtiger REST 
                //service of the customer portal user's vtiger account
                $rest = new RESTClient();
                $rest->format('json');

                $rest->set_header('Content-Type', 'application/x-www-form-urlencoded');
                $response = $rest->post(
                    $this->_vtresturl .
                    "?operation=logincustomer", "username=" . $_SERVER['HTTP_X_USERNAME'] .
                    "&password=" . $_SERVER['HTTP_X_PASSWORD']
                );
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_trace_id . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $response .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                );         
                
                if ($response == '' || $response == null)
                    throw new Exception("Blank response received from vtiger: LoginCustomer");                

                //Save vtiger response
                $this->_vtresponse = $response;

                //Objectify the response and check its success
                $response = json_decode($response);
                if ($response->success == false)
                    throw new Exception("Invalid Username and Password");

                //Store values from response
                $username = $response->result->user_name;
                $userAccessKey = $response->result->accesskey;
                $accountId = $response->result->accountId;
                $contactId = $response->result->contactId;
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_trace_id . "); " . 
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
                    " TRACE(" . $this->_trace_id . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $response .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                ); 
                
                if ($response == '' || $response == null)
                    throw new Exception("Blank response received from vtiger: GetChallenge");                 
                
                //Objectify the response and check its success
                $response = json_decode($response);
                if ($response->success == false)
                    throw new Exception("Unable to get challenge token");
                
                //Store values from response
                $challengeToken = $response->result->token;
                $generatedKey = md5($challengeToken . $userAccessKey);

                //Log
                Yii::log(
                    " TRACE(" . $this->_trace_id . "); " . 
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
                    " TRACE(" . $this->_trace_id . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $response .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                ); 
                
                $this->_vtresponse = $response;
                
                if ($response == '' || $response == null)
                    throw new Exception("Blank response received from vtiger: Login");                
                
                //Objectify the response and check its success
                $response = json_decode($response);
                if ($response->success == false)
                    throw new Exception("Invalid generated key ");
                
                //Store the values from response
                $sessionId = $response->result->sessionName;
                $response->result->accountId = $accountId;
                $response->result->contactId = $contactId;

                //Get Contact and Account Name 
                //Build vtiger query to fetch contacts
                $query = "select * from Contacts" .
                        " where id = " . $contactId . ";";

                //urlencode to as its sent over http.
                $queryParam = urlencode($query);

                //creating query string
                $params = "sessionName=$sessionId" .
                        "&operation=query&query=$queryParam";

                //Log
                Yii::log(
                    " TRACE(" . $this->_trace_id . "); " . 
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
                    " TRACE(" . $this->_trace_id . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $contact .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                );  
                
                //Save vtiger response
                $this->_vtresponse = $contact;                
                
                if ($contact == '' || $contact == null)
                    throw new Exception("Blank response received from vtiger: Contact");                
                
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
                    = "sessionName=$sessionId" .
                        "&operation=query&query=$queryParam";
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_trace_id . "); " . 
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
                    " TRACE(" . $this->_trace_id . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $account .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                );   
                
                //Save vtiger response
                $this->_vtresponse = $account;                
                
                if ($account == '' || $account == null)
                    throw new Exception("Blank response received from vtiger: Account");                
                
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
                $cache_value = json_encode($response->result);

                //Save userid and session id against customerportal 
                //credentials
                Yii::app()->cache->set($this->_cache_key, $cache_value, 86000);
                Yii::app()->cache->set($this->_instanceid . "_last_used_" . $this->_cache_key, time());
            }

            //Log
            Yii::log(
                " TRACE(" . $this->_trace_id . "); " . 
                " FUNCTION(" . __FUNCTION__ . "); " . 
                " VALIDATION (Storing session)", 
                CLogger::LEVEL_TRACE
            );            
            
            //Used the received value as session throug out in this session 
            $this->_session = json_decode($cache_value);
            $this->_session->challengeToken = $challengeToken;
            
            //Yes the user is valid let him run the operation he requested
            return true;
            
        } catch (Exception $e) {
            
            //Log
            Yii::log(
                " TRACE(" . $this->_trace_id . "); " . 
                " FUNCTION(" . __FUNCTION__ . "); " . 
                " VALIDATION (Some error occured during validation/Authentication)" . 
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
                $response->error->trace_id = $this->_trace_id;
                $response->error->vtresponse = json_decode($this->_vtresponse);

                //Check if the error code is TIME_NOT_IN_SYNC
                //if so send time delta
                if ($e->getCode() == 1003) {
                    if (isset($_SERVER['HTTP_X_TIMESTAMP']))
                        $response->error->time_difference
                            = $_SERVER['REQUEST_TIME'] - strtotime($_SERVER['HTTP_X_TIMESTAMP']);
                    
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
                    $response->error->trace_id = $this->_trace_id;
                    $response->error->vtresponse = $this->_vtresponse;
                    $this->_sendResponse(403, json_encode($response));
                } else {
                    
                    //Send about message in case the signature is not validated
                    $this->_sendResponse(
                        403, 'An account needs to setup in order to use ' .
                        'this service. Please contact ' .
                        '<a href="mailto://sales@gizur.com">sales@gizur.com</a> ' .
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
            " TRACE(" . $this->_trace_id . "); " . 
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
                }elseif (strpos($_SERVER['HTTP_ACCEPT'], 'html')!==false) {
                    //add html tags
                    $body = "<html><body>$body</body></html>";
                    
                    $this->_sendResponse(200, $body, 'text/html');
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

                    //Send response
                    $this->_sendResponse(200, json_encode($response));
                }

                if ($_GET['action'] == 'logout') {
                    $sessionId = $this->_session->sessionName;
           
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending request to vt url: " . 
                        $this->_vtresturl .
                        "?operation=logout&sessionName=$sessionId" .                            
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    //Logout using $sessionId
                    $rest = new RESTClient();
                    $rest->format('json');
                    $response = $rest->get(
                        $this->_vtresturl .
                        "?operation=logout&sessionName=$sessionId"
                    );
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
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

                    $cached_value = Yii::app()->cache->get(
                        'picklist_'
                        . $_GET['model'] . '_'
                        . $_GET['fieldname']
                    );

                    if ($cached_value === false) {
                        
                        //retrieve session id
                        $sessionId = $this->_session->sessionName;
                        
                        //flip custome fields array
                        $flipped_custom_fields 
                            = array_flip(Yii::app()->params->custom_fields[$_GET['model']]);
                        
                        //Check if the requested field name is a vtiger
                        //custom field
                        if (in_array($_GET['fieldname'], $flipped_custom_fields)) {
                            $fieldname 
                                = Yii::app()->params->custom_fields[$_GET['model']][$_GET['fieldname']];
                        } else {
                            $fieldname = $_GET['fieldname'];
                        }
                        
                        //Receive response from vtiger REST service
                        //Return response to client 
                        $params = "sessionName=$sessionId" .
                                "&operation=describe" .
                                "&elementType=" . $_GET['model'];
                        
                        //Log
                        Yii::log(
                            " TRACE(" . $this->_trace_id . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " PROCESSING REQUEST (sending GET request to vt url: " . 
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
                            " TRACE(" . $this->_trace_id . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " PROCESSING REQUEST (response received: " . 
                            $response .                          
                            ")", 
                            CLogger::LEVEL_TRACE
                        ); 
                        
                        //Save vtiger response
                        $this->_vtresponse = $response;                

                        if ($response == '' || $response == null)
                            throw new Exception("Blank response received from vtiger: Picklist");                        
                        
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
                                foreach ($field['type']['picklistValues'] as &$option)

                                //Check if there is a dependency setup
                                //for the picklist value
                                if (isset($option['dependency'])) {

                                    foreach ($option['dependency'] as $dep_fieldname => $dependency) {
                                        if (in_array($dep_fieldname, Yii::app()->params->custom_fields['HelpDesk'])) {
                                                $new_fieldname = $flipped_custom_fields[$dep_fieldname];
                                                $option['dependency'][$new_fieldname] = $option['dependency'][$dep_fieldname];
                                                unset($option['dependency'][$dep_fieldname]);
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
                                    'picklist_'
                                    . $_GET['model']
                                    . '_'
                                    . $flipped_custom_fields[$field['name']]
                                    , $content, 3600
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
                            " TRACE(" . $this->_trace_id . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " PROCESSING REQUEST ( FROM CACHE " . 
                            $cached_value .                            
                            ")", 
                            CLogger::LEVEL_TRACE
                        );                        
                        
                        //Send cached response
                        $this->_sendResponse(200, $cached_value);
                    }
                }

                //Is this a request for listing categories
                if (isset($_GET['category'])) {
                    
                    //Store values
                    $sessionId = $this->_session->sessionName;
                    $accountId = $this->_session->accountId;

                    //Send request to vtiger REST service
                    $query = "select * from " . $_GET['model'];

                    //creating where clause based on parameters
                    $where_clause = Array();
                    if ($_GET['category'] == 'inoperation') {
                        $where_clause[] = "ticketstatus = 'Closed'";
                    }
                    if ($_GET['category'] == 'damaged') {
                        $where_clause[] = "ticketstatus = 'Open'";
                    }

                    if (isset($_GET['reportdamage']))
                    if ($_GET['reportdamage'] != 'all') {
                        $where_clause[] = Yii::app()->params['custom_fields'][$_GET['model']]['reportdamage'] . " = '" . ucwords($_GET['reportdamage']) . "'";
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
                            $where_clause[] 
                                = "createdtime >= '" .
                                    $_GET['year'] . "-" . $startmonth . "-01'";
                            $where_clause[] 
                                = "createdtime <= '" .
                                    $_GET['year'] . "-" . $endmonth . "-31'";
                        }
                    }

                    //Adding trailer filter
                    if (isset($_GET['trailerid'])) {
                        if ($_GET['trailerid'] != '0')
                            $where_clause[] = Yii::app()->params->custom_fields
                                        ['HelpDesk']['trailerid'] .
                                        " = '" . $_GET['trailerid'] . "'";
                    }

                    //Attaching where clause to filter
                    if (count($where_clause) != 0)
                        $query = $query . " where " .
                                implode(" and ", $where_clause);
                    
                    //Terminating the query
                    $query = $query . ";";

                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);

                    //creating query string
                    $params = "sessionName=$sessionId" .
                            "&operation=query&query=$queryParam";
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
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
                        " TRACE(" . $this->_trace_id . "); " . 
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
                    $params = "sessionName=$sessionId" .
                            "&operation=query&query=$queryParam";
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
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
                        " TRACE(" . $this->_trace_id . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $accounts .                          
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    //Objectify the response and check its success
                    $accounts = json_decode($accounts, true);
                    
                    if ($accounts['success'] == true) {
                        $tmp_accounts = array();
                        if (isset($accounts['result']))
                            foreach ($accounts['result'] as $account)
                                $tmp_accounts[$account['id']] = $account['accountname'];
                    }


                    //Get Contact List
                    $query = "select * from Contacts;";
                    
                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);

                    //creating query string
                    $params = "sessionName=$sessionId" .
                            "&operation=query&query=$queryParam";
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
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
                        " TRACE(" . $this->_trace_id . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $contacts .                          
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    //Objectify the response and check its success
                    $contacts = json_decode($contacts, true);
                    if ($contacts['success'] == true) {
                        $tmp_contacts = array();
                        if (isset($contacts['result']))
                        foreach ($contacts['result'] as $contact) {
                            $tmp_contacts[$contact['id']]['contactname'] = $contact['firstname'] . ' ' . $contact['lastname'];
                            $tmp_contacts[$contact['id']]['accountname'] = $tmp_accounts[$contact['account_id']];
                        }
                    }

                    //Before sending response santise custom fields names to 
                    //human readable field names
                    $custom_fields = Yii::app()->params->custom_fields['HelpDesk'];

                    foreach ($response['result'] as &$troubleticket) {
                        unset($troubleticket['update_log']);
                        unset($troubleticket['hours']);
                        unset($troubleticket['days']);
                        unset($troubleticket['modifiedtime']);
                        unset($troubleticket['from_portal']);
                        if (isset($tmp_contacts)) {
                            if (isset($tmp_contacts[$troubleticket['parent_id']])) {
                                $troubleticket['contactname'] = $tmp_contacts[$troubleticket['parent_id']]['contactname'];
                                $troubleticket['accountname'] = $tmp_contacts[$troubleticket['parent_id']]['accountname'];
                            } else {
                                $troubleticket['contactname'] = '';
                                $troubleticket['accountname'] = '';
                            }
                        }
                        foreach ($troubleticket as $fieldname => $value) {
                            $key_to_replace = array_search($fieldname, $custom_fields);
                            if ($key_to_replace) {
                                unset($troubleticket[$fieldname]);
                                $troubleticket[$key_to_replace] = $value;
                                //unset($custom_fields[$key_to_replace]);                                
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

                    $cached_value = Yii::app()->cache->get(
                        'picklist_'
                        . $_GET['model'] . '_'
                        . $_GET['fieldname']
                    );

                    if ($cached_value === false) {
                        
                        //retrieve session id
                        $sessionId = $this->_session->sessionName;
                        
                        //flip custome fields array
                        $flipped_custom_fields 
                            = array_flip(Yii::app()->params->custom_fields['Assets']);
                        
                        //Check if the requested field name is a vtiger
                        //custom field
                        if (in_array($_GET['fieldname'], $flipped_custom_fields)) {
                            $fieldname 
                                = Yii::app()->params->custom_fields[$_GET['model']][$_GET['fieldname']];
                        } else {
                            $fieldname = $_GET['fieldname'];
                        }
                        
                        //Receive response from vtiger REST service
                        //Return response to client 
                        $params = "sessionName=$sessionId" .
                                "&operation=describe" .
                                "&elementType=" . $_GET['model'];
                        
                        //Log
                        Yii::log(
                            " TRACE(" . $this->_trace_id . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " PROCESSING REQUEST (sending GET request to vt url: " . 
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
                            " TRACE(" . $this->_trace_id . "); " . 
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

                        //Find the appropriate field whose label value needs to
                        //be sent  
                        foreach ($response['result']['fields'] as $field) {
                            
                            if ($fieldname == $field['name']) {
                                
                                //Check if the field is a picklist
                                if ($field['type']['name'] == 'picklist') {
                                    
                                    //Loop through all values of the pick list
                                    foreach ($field['type']['picklistValues'] as &$option)
                                        
                                    //Check if there is a dependency setup
                                    //for the picklist value
                                    if (isset($option['dependency'])) {
                                        
                                        foreach ($option['dependency'] as $dep_fieldname => $dependency) {
                                            if (in_array($dep_fieldname, Yii::app()->params->custom_fields['Assets'])) {
                                                    $new_fieldname = $flipped_custom_fields[$dep_fieldname];
                                                    $option['dependency'][$new_fieldname] = $option['dependency'][$dep_fieldname];
                                                    unset($option['dependency'][$dep_fieldname]);
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
                                        'picklist_'
                                        . $_GET['model']
                                        . '_'
                                        . $_GET['fieldname'], $content, 3600
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
                        $this->_sendResponse(200, $cached_value);
                    }
                } else {

                    $sessionId = $this->_session->sessionName;
                    $accountId = $this->_session->accountId;

                    //Send request to vtiger REST service
                    $query = "select * from " . $_GET['model'] . ";";

                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);

                    //creating query string
                    $params = "sessionName=$sessionId" .
                            "&operation=query&query=$queryParam";

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
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
                        " TRACE(" . $this->_trace_id . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $response .                          
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                

                    //Objectify the response and check its success
                    $response = json_decode($response, true);

                    if ($response['success'] == false)
                    throw new Exception('Unable to fetch details');

                    $custom_fields = Yii::app()->params->custom_fields['Assets'];

                //Before sending response santise custom fields names to 
                //human readable field names                
                foreach ($response['result'] as &$asset) {
                    unset($asset['update_log']);
                    unset($asset['hours']);
                    unset($asset['days']);
                    unset($asset['modifiedtime']);
                    unset($asset['from_portal']);
                    foreach ($asset as $fieldname => $value) {
                        $key_to_replace = array_search($fieldname, $custom_fields);
                        if ($key_to_replace) {
                            unset($asset[$fieldname]);
                            $asset[$key_to_replace] = $value;
                            //unset($custom_fields[$key_to_replace]);                                
                        }
                    }
                }
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
                $response->error->trace_id = $this->_trace_id;
                $this->_sendResponse(405, json_encode($response));

                break;
            }
        } catch (Exception $e) {
            
            //Generating error response
            $response = new stdClass();
            $response->success = false;
            $response->error->code = "ERROR";
            $response->error->message = $e->getMessage();
            $response->error->trace_id = $this->_trace_id;
            $response->error->vtresponse = $this->_vtresponse;
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
     * Notes: $id is vTiger webservice ID and is of the form [modelid]x[entityid]
     * 
     * @return appropriate details
     */
    public function actionView()
    {
        //Tasks include detail view of a specific Troubleticket and Assets
        try {
            
            //Log
            Yii::log("TRACE(" . $this->_trace_id . "); FUNCTION(" . __FUNCTION__ . "); PROCESSING REQUEST ", CLogger::LEVEL_TRACE);            
            
            switch ($_GET['model']) {
            /*
                * ******************************************************************
                * ******************************************************************
                * * User MODEL
                * * Accepts id
                * ******************************************************************
                * ******************************************************************
                */
            case 'User':
                // Instantiate the class for Dynamo DB
                $dynamodb = new AmazonDynamoDB();
                $dynamodb->set_region(constant("AmazonDynamoDB::" . Yii::app()->params->awsDynamoDBRegion));

                // Get an item
                $ddb_response = $dynamodb->get_item(
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
                if (isset($ddb_response->body->Item)) {
                    
                    //create response
                    foreach ($ddb_response->body->Item->children()
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
                    $response->error->trace_id = $this->_trace_id;
                    $this->_sendResponse(404, json_encode($response));
                }
                break;
                /*
                 * ******************************************************************
                 * ******************************************************************
                 * * HelpDesk MODEL
                 * * Accepts id
                 * ******************************************************************
                 * ******************************************************************
                 */
            case 'HelpDesk':
                $sessionId = $this->_session->sessionName;
                
                Yii::log(
                   "TRACE(" . $this->_trace_id . "); FUNCTION(" . __FUNCTION__ . "); PROCESSING REQUEST " .
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
                $params = "sessionName=$sessionId" .
                        "&operation=query&query=$queryParam";
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_trace_id . "); " . 
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
                    " TRACE(" . $this->_trace_id . "); " . 
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
                $params = "sessionName=$sessionId" .
                        "&operation=getrelatedtroubleticketdocument" .
                        "&crmid=" . $_GET['id'];
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_trace_id . "); " . 
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
                    " TRACE(" . $this->_trace_id . "); " . 
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
                            " where id in (" . $this->_ws_entities['Documents']
                            . "x" .
                            implode(
                                ", " . $this->_ws_entities['Documents']
                                . "x", $documentids
                            ) . ");";

                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);

                    //creating query string
                    $params = "sessionName=$sessionId" .
                            "&operation=query&query=$queryParam";

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
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
                        " TRACE(" . $this->_trace_id . "); " . 
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
                    $params = "sessionName=$sessionId" .
                            "&operation=query&query=$queryParam";

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
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
                        " TRACE(" . $this->_trace_id . "); " . 
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
                    $params = "sessionName=$sessionId" .
                            "&operation=query&query=$queryParam";

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
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
                        " TRACE(" . $this->_trace_id . "); " . 
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

                $custom_fields = Yii::app()->params->custom_fields['HelpDesk'];

                unset($response['result']['update_log']);
                unset($response['result']['hours']);
                unset($response['result']['days']);
                unset($response['result']['modifiedtime']);
                unset($response['result']['from_portal']);
                
            if (is_array($response['result']))
            foreach ($response['result'] as $fieldname => $value) {
                    $key_to_replace = array_search($fieldname, $custom_fields);
                if ($key_to_replace) {
                        unset($response['result'][$fieldname]);
                        $response['result'][$key_to_replace] = $value;
                }
            }

                $this->_sendResponse(200, json_encode($response));
                break;

                /*
                 * ******************************************************************
                 * ******************************************************************
                 * * Assets MODEL
                 * * Accepts id
                 * ******************************************************************
                 * ******************************************************************
                 */
            case 'Assets':
                    $sessionId = $this->_session->sessionName;
                
                    if (preg_match('[0-9]?x[0-9]?', $_GET['id'])==0)
                        throw new Exception('Invalid format of Id');                

                    //Send request to vtiger REST service
                    $query = "select * from " . $_GET['model'] .
                            " where id = " . $_GET['id'] . ";";

                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);

                    //creating query string
                    $params = "sessionName=$sessionId" .
                            "&operation=query&query=$queryParam";

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
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
                        " TRACE(" . $this->_trace_id . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $response .                          
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    $response = json_decode($response, true);
                    $response['result'] = $response['result'][0];

                    $custom_fields = Yii::app()->params->custom_fields['Assets'];

                foreach ($response['result'] as $fieldname => $value) {
                    $key_to_replace = array_search($fieldname, $custom_fields);
                    if ($key_to_replace) {
                        unset($response['result'][$fieldname]);
                        $response['result'][$key_to_replace] = $value;
                        //unset($custom_fields[$key_to_replace]);                                
                    }
                }

                $this->_sendResponse(200, json_encode($response));
                break;

                /*
                 * ******************************************************************
                 * ******************************************************************
                 * * DocumentAttachments MODEL
                 * * Accepts notesid
                 * ******************************************************************
                 * ******************************************************************
                 */
            case 'DocumentAttachments':
                $sessionId = $this->_session->sessionName;

                //urlencode to as its sent over http.
                $queryParam = urlencode($query);

                //creating query string
                $params = "sessionName=$sessionId" .
                        "&operation=gettroubleticketdocumentfile" .
                        "&notesid=" . $_GET['id'];

                //Log
                Yii::log(
                    " TRACE(" . $this->_trace_id . "); " . 
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
                    " TRACE(" . $this->_trace_id . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $response .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                );                
                
                $response = json_decode($response);                
                
                $s3 = new AmazonS3();
                $s3->set_region(constant("AmazonS3::" . Yii::app()->params->awsS3Region));

                $unique_id = uniqid();

                $file_resource = fopen(
                    'protected/data/' . $unique_id . 
                    $response->result->filename, 'x'
                );
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_trace_id . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (sending request to s3 to get file: " . 
                    $response->result->filename .                            
                    ")", 
                    CLogger::LEVEL_TRACE
                );                 
                
                $s3response = $s3->get_object(
                    Yii::app()->params->awsS3Bucket, 
                    $response->result->filename, 
                    array(
                        'fileDownload' => $file_resource
                    )
                );

                //Log
                Yii::log(
                    " TRACE(" . $this->_trace_id . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received from s3: " . 
                    json_encode($s3response) .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                );                
                
                if (!$s3response->isOK())
                throw new Exception("File not found.");

                $response->result->filecontent 
                    = base64_encode(
                        file_get_contents(
                            'protected/data/' . $unique_id .
                            $response->result->filename
                        )
                    );
                unlink('protected/data/' . $unique_id . $response->result->filename);

                $filename_sanitizer = explode("_", $response->result->filename);
                unset($filename_sanitizer[0]);
                unset($filename_sanitizer[1]);
                $response->result->filename = implode('_', $filename_sanitizer);
                $this->_sendResponse(200, json_encode($response));
                break;

            default :
                $response = new stdClass();
                $response->success = false;
                $response->error->code = $this->_errors[1004];
                $response->error->message = "Not a valid method" .
                        " for model " . $_GET['model'];
                $response->error->trace_id = $this->_trace_id;
                $this->_sendResponse(405, json_encode($response));
                break;
            }
        } catch (Exception $e) {
            $response = new stdClass();
            $response->success = false;
            $response->error->code = "ERROR";
            $response->error->message = $e->getMessage();
            $response->error->trace_id = $this->_trace_id;
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
            
            Yii::log("TRACE(" . $this->_trace_id . "); FUNCTION(" . __FUNCTION__ . "); PROCESSING REQUEST ", CLogger::LEVEL_TRACE);            
            
            switch ($_GET['model']) {
                /*
                 * ******************************************************************
                 * ******************************************************************
                 * * User MODEL
                 * * Accepts id
                 * ******************************************************************
                 * ******************************************************************
                 */
            case 'User':
                    error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
                    ini_set('display_errors', 'On');
                    Yii::log(
                        "TRACE(" . $this->_trace_id . ");" . 
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


                    /**
                    * Database connection options
                    * @global string $options
                    */
                    $options = array(
                        'persistent' => true,
                    );
                    
                    Yii::log(
                        "TRACE(" . $this->_trace_id . ");" . 
                        " FUNCTION(" . __FUNCTION__ . ");" . 
                        " CREATING MDB OBJECT ", 
                         CLogger::LEVEL_TRACE
                    );                                       
                    
                    //Create Default DB credentials
                    $db_server     = $dbconfig['db_server'];
                    $db_port       = str_replace(":", "", $dbconfig['db_port']);
                    $db_username   = 'user_' . substr(strrev(uniqid()), 1, 8);
                    $db_password   = substr(strrev(uniqid()), 1, 16);
                    $db_name       = 'vtiger_' . substr(strrev(uniqid()), 1, 8);                    
                
                    $post = json_decode(file_get_contents('php://input'), true);

                    $post['secretkey_1'] = uniqid("", true) . uniqid("", true);
                    $post['apikey_1'] = strtoupper(uniqid("GZCLD" . uniqid()));

                    $post['secretkey_2'] = uniqid("", true) . uniqid("", true);
                    $post['apikey_2'] = strtoupper(uniqid("GZCLD" . uniqid()));
                    
                    $post['databasename'] = $db_name;
                    $post['server'] = $db_server;
                    $post['port'] = $db_port;
                    $post['username'] = $db_username;
                    $post['dbpassword'] = $db_password;
                    $post['port'] = $db_port;

                    //Create User
                    //===========
                    $query = "GRANT USAGE ON *.* TO '$db_username'@'%' IDENTIFIED BY '$db_password' ";
                    $query .= "WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;";                    
                    
                    // Execute the query
                    // check if the query was executed properly
                    if ($mysqli->query($query)===false)
                        throw New Exception("Unable to create user and grant permission: " . $mysqli->error);
                    
                    //Create Database
                    //===============
                    $query = "CREATE DATABASE IF NOT EXISTS `$db_name`;";
                    
                    // Execute the query
                    // check if the query was executed properly
                    if ($mysqli->query($query)===false)
                        throw New Exception("Unable to create database " . $mysqli->error);                    

                    //Grant Permission
                    //================
                    $query = "GRANT ALL PRIVILEGES ON `$db_name`.* TO '$db_username'@'%';";
                    
                    // Execute the query
                    // check if the query was executed properly
                    if ($mysqli->query($query)===false)
                        throw New Exception($mysqli->error);
                    
                    $mysqli->close();

                    //Import Database
                    //===============
                    $exec_stmt = "mysql -u$db_username -p$db_password -h$db_server -P $db_port $db_name < ../lib/vtiger-5.4.0-database.sql";

                    $output = shell_exec($exec_stmt);
                    
                    // Instantiate the class
                    $dynamodb = new AmazonDynamoDB();
                    $dynamodb->set_region(constant("AmazonDynamoDB::" . Yii::app()->params->awsDynamoDBRegion));
                    $ddb_response = $dynamodb->put_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                             'Item' => $dynamodb->attributes($post)
                        )
                    );

                    // Get an item
                    $ddb_response = $dynamodb->get_item(
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

                if (isset($ddb_response->body->Item)) {
                    Yii::app()->cache->set($post['apikey_1'], $post['secretkey_1']);
                    Yii::app()->cache->set($post['apikey_2'], $post['secretkey_2']);
                    foreach ($ddb_response->body->Item->children()
                    as $key => $item) {
                        $result_ddb->{$key} 
                            = (string) $item->{AmazonDynamoDB::TYPE_STRING};
                    }
                    $result_ddb->exec_stmt = $exec_stmt;
                    $result_ddb->output = $output;
                    $response->success = true;
                    $response->result = $result_ddb;
                    $this->_sendResponse(200, json_encode($response));
                } else {
                    $response->success = false;
                    $response->error->code = "NOT_CREATED";
                    $response->error->message = $_GET['email'] . " could "
                            . " not be created";
                    $response->error->trace_id = $this->_trace_id;
                    $this->_sendResponse(400, json_encode($response));
                }
                break;
                /*
                 * ******************************************************************
                 * ******************************************************************
                 * * HelpDesk MODEL
                 * * Accepts id
                 * ******************************************************************
                 * ******************************************************************
                 */
            case 'HelpDesk':
                $script_started = date("c");
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

                $sessionId = $this->_session->sessionName;
                $userId = $this->_session->userId;

                /** Creating Touble Ticket* */
                $post = $_POST;
                $custom_fields = array_flip(
                    Yii::app()->params->custom_fields['HelpDesk']
                );

            foreach ($post as $k => $v) {
                $key_to_replace = array_search($k, $custom_fields);
                if ($key_to_replace) {
                    unset($post[$k]);
                    $post[$key_to_replace] = $v;
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
                    " TRACE(" . $this->_trace_id . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (sending POST request to vt url: " . 
                    $this->_vtresturl . "  " .
                    json_encode(
                        array(
                            'sessionName' => $sessionId,
                            'operation' => 'create',
                            'element' => $dataJson,
                            'elementType' => $_GET['model']
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
                        'sessionName' => $sessionId,
                        'operation' => 'create',
                        'element' => $dataJson,
                        'elementType' => $_GET['model']
                        )
                );

                //Log
                Yii::log(
                    " TRACE(" . $this->_trace_id . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $response .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                );                
                
                $globalresponse = json_decode($response);
                /*                     * Creating Document* */

                if ($globalresponse->success == false)
                    throw new Exception($globalresponse->error->message);

                //Create Documents if any is attached
                $crmid = $globalresponse->result->id;
                $globalresponse->result->documents = Array();
                $dataJson = array(
                    'notes_title' => 'Attachement',
                    'assigned_user_id' => $userId,
                    'notecontent' => 'Attachement',
                    'filelocationtype' => 'I',
                    'filedownloadcount' => null,
                    'filestatus' => 1,
                    'fileversion' => '',
                );
            if (!empty($_FILES) && $globalresponse->success) {
                foreach ($_FILES as $key => $file) {
                    $uniqueid = uniqid();

                    $dataJson['filename'] = $crmid . "_" . $uniqueid . "_" . $file['name'];
                    $dataJson['filesize'] = $file['size'];
                    $dataJson['filetype'] = $file['type'];

                    //Upload file to Amazon S3
                    $s3 = new AmazonS3();
                    $s3->set_region(constant("AmazonS3::" . Yii::app()->params->awsS3Region));

                    $response = $s3->create_object(
                        Yii::app()->params->awsS3Bucket, $crmid . '_' . $uniqueid . '_' . $file['name'], array(
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
                            " TRACE(" . $this->_trace_id . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " PROCESSING REQUEST (sending POST request to vt url: " . 
                            $this->_vtresturl . "  " .
                            json_encode(
                                array(
                                    'sessionName' => $sessionId,
                                    'operation' => 'create',
                                    'element' => json_encode($dataJson),
                                    'elementType' => 'Documents'
                                )                           
                            ) .                            
                            ")", 
                            CLogger::LEVEL_TRACE
                        );                         
                        
                        //Create document
                        $rest = new RESTClient();
                        $rest->format('json');
                        $document = $rest->post(
                            $this->_vtresturl, array(
                                'sessionName' => $sessionId,
                                'operation' => 'create',
                                'element' =>
                                json_encode($dataJson),
                                'elementType' => 'Documents'
                            )
                        );
                        
                        //Log
                        Yii::log(
                            " TRACE(" . $this->_trace_id . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " PROCESSING REQUEST (response received: " . 
                            $document .                          
                            ")", 
                            CLogger::LEVEL_TRACE
                        );                        
                        
                        $document = json_decode($document);
                        if ($document->success) {
                            $notesid = $document->result->id;
                            
                            //Log
                            Yii::log(
                                " TRACE(" . $this->_trace_id . "); " . 
                                " FUNCTION(" . __FUNCTION__ . "); " . 
                                " PROCESSING REQUEST (sending POST request to vt url: " . 
                                $this->_vtresturl . "  " .
                                json_encode(
                                    array(
                                        'sessionName' => $sessionId,
                                        'operation' =>
                                        'relatetroubleticketdocument',
                                        'crmid' => $crmid,
                                        'notesid' => $notesid
                                    )                        
                                ) .                            
                                ")", 
                                CLogger::LEVEL_TRACE
                            );                               

                            //Relate Document with Trouble Ticket
                            $rest = new RESTClient();
                            $rest->format('json');
                            $response = $rest->post(
                                $this->_vtresturl, array(
                                    'sessionName' => $sessionId,
                                    'operation' =>
                                    'relatetroubleticketdocument',
                                    'crmid' => $crmid,
                                    'notesid' => $notesid
                                )
                            );
                            
                            //Log
                            Yii::log(
                                " TRACE(" . $this->_trace_id . "); " . 
                                " FUNCTION(" . __FUNCTION__ . "); " . 
                                " PROCESSING REQUEST (response received: " . 
                                $response .                          
                                ")", 
                                CLogger::LEVEL_TRACE
                            );                            
                            
                            $response = json_decode($response);
                            if ($response->success) {
                                $globalresponse->result->documents[]
                                    = $document->result;
                            } else {
                                $globalresponse->result->documents[]
                                    = 'not uploaded - relating document failed:' . $file['name'];
                            }
                        } else {
                            $globalresponse->result->documents[]
                                = 'not uploaded - creating document failed:' . $file['name'];
                        }
                    } else {
                        $globalresponse->result->documents[]
                            = 'not uploaded - upload to storage service failed:' . $file['name'];
                    }
                }
            }

                $globalresponse = json_encode($globalresponse);
                $globalresponse = json_decode($globalresponse, true);

                $custom_fields = Yii::app()->params->custom_fields['HelpDesk'];


                unset($globalresponse['result']['update_log']);
                unset($globalresponse['result']['hours']);
                unset($globalresponse['result']['days']);
                unset($globalresponse['result']['modifiedtime']);
                unset($globalresponse['result']['from_portal']);
            foreach ($globalresponse['result'] as $fieldname => $value) {
                $key_to_replace = array_search($fieldname, $custom_fields);
                if ($key_to_replace) {
                    unset($globalresponse['result'][$fieldname]);
                    $globalresponse['result'][$key_to_replace] = $value;
                    //unset($custom_fields[$key_to_replace]);                                
                }
            }

            if ($post['ticketstatus'] != 'Closed') {
                $email = new AmazonSES();
                //$email->set_region(constant("AmazonSES::" . Yii::app()->params->awsSESRegion));

                $SESresponse = $email->send_email(
                    Yii::app()->params->awsSESFromEmailAddress, // Source (aka From)
                    array(
                        'ToAddresses' => array(// Destination (aka To)
                            $_SERVER['HTTP_X_USERNAME'],
                            Yii::app()->params->awsSESClientEmailAddress
                        )
                    ), 
                    array(// Message (short form)
                        'Subject.Data' => date("F j, Y") . ': Besiktningsprotokoll för  ' . $globalresponse['result']['ticket_no'],
                        'Body.Text.Data' => 'Hej ' . $this->_session->contactname . ', ' .
                        PHP_EOL .
                        PHP_EOL .
                        'Ett besiktningsprotokoll har skapats.' .
                        PHP_EOL .
                        PHP_EOL .                        
                        'Trailer ID: ' .
                        $globalresponse['result']['trailerid'] .
                        PHP_EOL . 
                        'Plats: ' .
                        $globalresponse['result']['damagereportlocation'] .
                        PHP_EOL .
                        'Plomerad: ' .
                        $globalresponse['result']['sealed'] .
                        PHP_EOL .
                        'Skivor: ' .
                        $globalresponse['result']['straps'] .
                        PHP_EOL .
                        'Spännband: ' .
                        $globalresponse['result']['plates'] .
                        PHP_EOL .
                        'Typ: ' .
                        $globalresponse['result']['damagetype'] .
                        PHP_EOL .
                        'Position: ' .
                        $globalresponse['result']['damagereportlocation'] .
                        PHP_EOL .
                        'Caused by: ' .
                        $globalresponse['result']['drivercauseddamage'] .
                        PHP_EOL .
                        'Ticket ID: ' .                      
                        $globalresponse['result']['ticket_no'] .
                        PHP_EOL .
                        PHP_EOL .
                        PHP_EOL .
                        '--' .
                        PHP_EOL .
                        'Gizur Admin'
                    )
                );
            }
            $this->_sendResponse(200, json_encode($globalresponse));
            break;

            default :
                $response = new stdClass();
                $response->success = false;
                $response->error->code = $this->_errors[1004];
                $response->error->message = "Not a valid method" .
                        " for model " . $_GET['model'];
                $response->error->trace_id = $this->_trace_id;
                $this->_sendResponse(405, json_encode($response));
                break;
            }
        } catch (Exception $e) {
            $response = new stdClass();
            $response->success = false;
            $response->error->code = $this->_errors[$e->getCode()];
            $response->error->message = $e->getMessage();
            $response->error->trace_id = $this->_trace_id;
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
        
        Yii::log("TRACE(" . $this->_trace_id . "); FUNCTION(" . __FUNCTION__ . "); ERROR IN REQUEST ", CLogger::LEVEL_TRACE);
        
        $response = new stdClass();
        $response->success = false;
        if (isset($this->_valid_models[$_GET['model']])) {
            $response->error->code = $this->_errors[1004];
            $response->error->message = "Not a valid method" .
                    " for model " . $_GET['model'];
            $response->error->trace_id = $this->_trace_id;
            $this->_sendResponse(405, json_encode($response));
        } else {
            $response->error->code = "NOT_FOUND";
            $response->error->message = "Such a service is not provided by" .
                    " this REST service";
            $response->error->trace_id = $this->_trace_id;
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
            
            Yii::log("TRACE(" . $this->_trace_id . "); FUNCTION(" . __FUNCTION__ . "); PROCESSING REQUEST ", CLogger::LEVEL_TRACE);
            
            switch ($_GET['model']) {
                /*
                 * ******************************************************************
                 * ******************************************************************
                 * * Cron MODEL
                 * * Accepts as action mailscan
                 * ******************************************************************
                 * ******************************************************************
                 */
            case 'Cron':
                
                if ($_GET['action'] == 'mailscan') {
                    
                    $filename = Yii::app()->params->vtCronPath . 'MailScannerCron.sh';
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
                
                break;
                /*
                 * ******************************************************************
                 * ******************************************************************
                 * * Authenticate MODEL
                 * * Accepts reset / changepw
                 * ******************************************************************
                 * ******************************************************************
                 */
            case 'Authenticate':
                if ($_GET['action'] == 'reset') {

                    $email = new AmazonSES();
                    //$email->set_region(constant("AmazonSES::" . Yii::app()->params->awsSESRegion));
                    $response = $email->list_verified_email_addresses();

                    if ($response->isOK()) {
                        $verifiedEmailAddresses = (Array) $response->body->ListVerifiedEmailAddressesResult->VerifiedEmailAddresses;
                        $verifiedEmailAddresses = $verifiedEmailAddresses['member'];
                        
                        //Log
                        Yii::log(
                            " TRACE(" . $this->_trace_id . "); " . 
                            " FUNCTION(" . __FUNCTION__ . "); " . 
                            " PROCESSING REQUEST (List of Verified Email Addresses: " . 
                            json_encode($verifiedEmailAddresses) . "  From Email Address" .
                            json_encode(Yii::app()->params->awsSESFromEmailAddress) .                            
                            ")", 
                            CLogger::LEVEL_TRACE
                        );        
                        
                        if (!is_array($verifiedEmailAddresses)) {
                            $verifiedEmailAddresses = (array)$verifiedEmailAddresses;
                        }
                        
                        if (in_array(Yii::app()->params->awsSESFromEmailAddress, $verifiedEmailAddresses) == false) {
                            $email->verify_email_address(Yii::app()->params->awsSESFromEmailAddress);
                            throw new Exception('From Email Address not verified. Contact Gizur Admin.');
                        }
                    }

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending POST request to vt url: " . 
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
                        " TRACE(" . $this->_trace_id . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $response .                          
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    $response = json_decode($response);

                    if ($response->success == false)
                        throw new Exception("Unable to reset password");

                    $SESresponse = $email->send_email(
                        Yii::app()->params->awsSESFromEmailAddress, // Source (aka From)
                        array(
                            'ToAddresses' => array(// Destination (aka To)
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
                    if ($SESresponse->isOK()) {
                        $this->_sendResponse(200, json_encode($response));
                    } else {
                        throw new Exception('Password has been reset but unable to send email.');
                    }
                }

                if ($_GET['action'] == 'changepw') {
                    $_PUT = Array();
                    parse_str(file_get_contents('php://input'), $_PUT);
                    if (!isset($_PUT['newpassword']))
                        throw new Exception('New Password not provided.');
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending POST request to vt url: " . 
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
                        " TRACE(" . $this->_trace_id . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $response .                          
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    $response = json_decode($response);
                    if ($response->success == false)
                        throw new Exception($response->error->message);
                    
                    Yii::app()->cache->delete($this->_cache_key);
                    
                    $this->_sendResponse(200, json_encode($response));
                }
                /*
                 * ******************************************************************
                 * ******************************************************************
                 * * User MODEL
                 * * Accepts id
                 * ******************************************************************
                 * ******************************************************************
                 */
            case 'User':
                if (isset($_GET['field'])) {
                    $keyid = str_replace('keypair', '', $_GET['field']);

                    // Instantiate the class
                    $dynamodb = new AmazonDynamoDB();
                    $dynamodb->set_region(constant("AmazonDynamoDB::" . Yii::app()->params->awsDynamoDBRegion));

                    // Get an item
                    $ddb_response = $dynamodb->get_item(
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

                    foreach ($ddb_response->body->Item->children()
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

                    Yii::app()->cache->set($result['apikey_' . $keyid], $result['secretkey_' . $keyid]);

                    $ddb_response = $dynamodb->put_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'Item' => $dynamodb->attributes($result)
                        )
                    );


                    if ($response->success = $ddb_response->isOK())
                        $response->result = $result;

                    $this->_sendResponse(200, json_encode($response));
                } else {
                    $post = json_decode(file_get_contents('php://input'), true);
                    // Instantiate the class
                    $dynamodb = new AmazonDynamoDB();
                    $dynamodb->set_region(constant("AmazonDynamoDB::" . Yii::app()->params->awsDynamoDBRegion));
                    $ddb_response = $dynamodb->put_item(
                        array(
                            'TableName' => Yii::app()->params->awsDynamoDBTableName,
                            'Item' => $dynamodb->attributes($post)
                        )
                    );
                    $response = new stdClass();
                    $response->success = $ddb_response->isOK();
                    $this->_sendResponse(200, json_encode($response));
                }
                break;
                /*
                 * ******************************************************************
                 * ******************************************************************
                 * * HelpDesk MODEL
                 * * Accepts id
                 * ******************************************************************
                 * ******************************************************************
                 */
            case 'HelpDesk':
                    $sessionId = $this->_session->sessionName;

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending GET request to vt url: " . 
                        $this->_vtresturl . "  " .
                        json_encode(
                            array(
                                'sessionName' => $sessionId,
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
                            'sessionName' => $sessionId,
                            'operation' => 'retrieve',
                            'id' => $_GET['id']
                        )
                    );

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $response .                          
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    $response = json_decode($response, true);

                    //get data json 
                    $retrivedObject = $response['result'];
                    $retrivedObject['ticketstatus'] = 'Closed';
                    
                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (sending POST request to vt url: " . 
                        $this->_vtresturl . "  " .
                        json_encode(
                            array(
                                'sessionName' => $sessionId,
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
                            'sessionName' => $sessionId,
                            'operation' => 'update',
                            'element' => json_encode($retrivedObject)
                        )
                    );

                    //Log
                    Yii::log(
                        " TRACE(" . $this->_trace_id . "); " . 
                        " FUNCTION(" . __FUNCTION__ . "); " . 
                        " PROCESSING REQUEST (response received: " . 
                        $response .                          
                        ")", 
                        CLogger::LEVEL_TRACE
                    );                    
                    
                    $response = json_decode($response, true);

                    $custom_fields = Yii::app()->params->custom_fields['HelpDesk'];


                    unset($response['result']['update_log']);
                    unset($response['result']['hours']);
                    unset($response['result']['days']);
                    unset($response['result']['modifiedtime']);
                    unset($response['result']['from_portal']);
                foreach ($response['result'] as $fieldname => $value) {
                    $key_to_replace = array_search($fieldname, $custom_fields);
                    if ($key_to_replace) {
                        unset($response['result'][$fieldname]);
                        $response['result'][$key_to_replace] = $value;
                        //unset($custom_fields[$key_to_replace]);                                
                    }
                }

                    $this->_sendResponse(200, json_encode($response));

                break;
                /*
                 * ******************************************************************
                 * ******************************************************************
                 * * HelpDesk MODEL
                 * * Accepts id
                 * ******************************************************************
                 * ******************************************************************
                 */
            case 'Assets':
                
                //Log
                Yii::log(
                    " TRACE(" . $this->_trace_id . "); " . 
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
                    " TRACE(" . $this->_trace_id . "); " . 
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
                    " TRACE(" . $this->_trace_id . "); " . 
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
                    " TRACE(" . $this->_trace_id . "); " . 
                    " FUNCTION(" . __FUNCTION__ . "); " . 
                    " PROCESSING REQUEST (response received: " . 
                    $response .                          
                    ")", 
                    CLogger::LEVEL_TRACE
                );                
                
                $response = json_decode($response, true);

                if ($response['success'] == false)
                throw new Exception($response['error']['message']);

                $custom_fields = Yii::app()->params->custom_fields['Assets'];

                unset($response['result']['update_log']);
                unset($response['result']['hours']);
                unset($response['result']['days']);
                unset($response['result']['modifiedtime']);
                unset($response['result']['from_portal']);
            foreach ($response['result'] as $fieldname => $value) {
                $key_to_replace = array_search($fieldname, $custom_fields);
                if ($key_to_replace) {
                    unset($response['result'][$fieldname]);
                    $response['result'][$key_to_replace] = $value;
                }
            }

                $this->_sendResponse(200, json_encode($response));
                break;

            default :
                $response = new stdClass();
                $response->success = false;
                $response->error->code = $this->_errors[1004];
                $response->error->message = "Not a valid method" .
                        " for model ";
                $response->error->trace_id = $this->_trace_id;
                $this->_sendResponse(405, json_encode($response));
                break;
            }
        } catch (Exception $e) {
            $response = new stdClass();
            $response->success = false;
            $response->error->code = "ERROR";
            $response->error->message = $e->getMessage();
            $response->error->trace_id = $this->_trace_id;
            $this->_sendResponse(400, json_encode($response));
        }
    }

}
