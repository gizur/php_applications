<?php
/**
 * Yii Controller to handel REST queries
 *
 * Works with remote vtiger REST service
 *
 * @package        	GizurCloud
 * @subpackage    	Controller
 * @category    	Controller
 * @author        	Anshuk Kumar
 **/

/*
 * Including Amazon classes
 */

spl_autoload_unregister(array('YiiBase','autoload'));
Yii::import('application.vendors.*');
require_once('aws-php-sdk/sdk.class.php');
spl_autoload_register(array('YiiBase','autoload'));

class ApiController extends Controller {
    // Members

    /**
     * Version of API
     */
    
    const API_VERSION = "0.1";


    /**
     * Default response format
     * either 'json' or 'xml'
     */
    
    private $format = 'json';

    /**
     * Session between Gizur REST API and vTiger REST API
     */
    
    private $session;    

     /**
     * The Error Codes
     */
    
    private $errors = Array(
        0 => "ERROR",
        1001 => "MANDATORY_FIELDS_MISSING",
        1002 => "INVALID_FIELD_VALUE",
        1003 => "TIME_NOT_IN_SYNC",
        1004 => "METHOD_NOT_ALLOWED",
    );    

     /**
     * The vTiger REST Web Services Entities
     */
    
    private $ws_entities = Array(
        'Documents' => 15,
        'Contacts' => 12
    );    

    /**
     * List of valid models
     */

    private $valid_models = Array(
        'User',
        'HelpDesk',
        'Assets',
        'About',
        'DocumentAttachments',
        'Authenticate'
    );

     /**
     * Status Codes
     */

    private $codes = Array(
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

    private $cache_key = "";

    /**
     * @return array action filters
     */
    
    public function filters() {
        return array();
    }

    private function _sendResponse($status = 200, $body = '', 
                                                  $content_type = 'text/json')
    {
        // set the status
        $status_header = 'HTTP/1.1 ' . $status . ' ' 
                 . ((isset($this->codes[$status])) ? $codes[$status] : '');
        header($status_header);

        // and the content type
        header('Content-type: ' . $content_type);
     
        // pages with body are easy
        if($body != '') {
            // send the body
            echo $body;
        } else {
            $message = '';
            switch($status) {
                case 401:
                $message = 'You must be authorized to view this page.';
                break;
                case 404:
                $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] 
                                                            . ' was not found.';
                break;
                case 500:
                $message = 
                     'The server encountered an error processing your request.';
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
                <title>' . $status . ' ' . ((isset($this->codes[$status])) ? 
                                            $codes[$status] : '')  . '</title>
            </head>
            <body>
                <h1>' . ((isset($this->codes[$status])) ? $codes[$status] : '') . '</h1>
                <p>' . $message . '</p>
                <hr />
                <address>' . $signature . '</address>
            </body>
            </html>';
         
            echo $body;
        }
        Yii::app()->end();
    }

    /**
     * @returns wether any action should run
     */
    
    public function beforeAction() {
        
        try {
            //First we validate the requests using logic do not consume
            //resources 
            if ($_GET['model'] == 'User')
                return true;
                         
            if (!isset($_SERVER['HTTP_X_TIMESTAMP']))
                throw new Exception('Timestamp not found in request');
            
            if (!isset($_SERVER['HTTP_X_SIGNATURE']))
                throw new Exception('Signature not found');

            if (!isset($_SERVER['HTTP_X_UNIQUE_SALT']))
                throw new Exception('Unique Salt not found');
            
            //check if public key exists
            if (!isset($_SERVER['HTTP_X_GIZURCLOUD_API_KEY']))
                throw new Exception('Public Key Not Found in request');
            
            if ( $_SERVER["REQUEST_TIME"] - Yii::app()->params->acceptableTimestampError > 
                    strtotime($_SERVER['HTTP_X_TIMESTAMP']))
		        throw new Exception('Stale request', 1003);
            
            if ($_SERVER["REQUEST_TIME"] + Yii::app()->params->acceptableTimestampError <
                    strtotime($_SERVER['HTTP_X_TIMESTAMP']))
		        throw new Exception('Oh, Oh, Oh, request from the FUTURE! ', 1003);

            //Fetch API Key details from Dynamodb, resource  intensive validation
            if (($GIZURCLOUD_SECRET_KEY = Yii::app()->cache->get($_SERVER['HTTP_X_GIZURCLOUD_API_KEY']))===false) {
                // Retreive Key pair from Amazon Dynamodb
                $dynamodb = new AmazonDynamoDB();
                $dynamodb->set_region(constant("AmazonDynamoDB::" . Yii::app()->params->awsDynamoDBRegion)); 
                
                //Scan for API KEYS
                $ddb_response = $dynamodb->scan(array(
                    'TableName'       => Yii::app()->params->awsDynamoDBTableName,
                        'AttributesToGet' => array('id', 'apikey_1','secretkey_1'),
                        'ScanFilter'      => array(
                            'apikey_1' => array(
                                'ComparisonOperator' => AmazonDynamoDB::CONDITION_EQUAL,
                                'AttributeValueList' => array(
                                    array( AmazonDynamoDB::TYPE_STRING => $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] )
                                )
                            )
                        )
                ));
                if ($publicKeyNotFound = ($ddb_response->body->Count==0)) {
                    //Scan for API KEYS
                    $ddb_response = $dynamodb->scan(array(
                        'TableName'       => Yii::app()->params->awsDynamoDBTableName,
                            'AttributesToGet' => array('id', 'apikey_2','secretkey_2'),
                            'ScanFilter'      => array(
                                'apikey_2' => array(
                                    'ComparisonOperator' => AmazonDynamoDB::CONDITION_EQUAL,
                                    'AttributeValueList' => array(
                                        array( AmazonDynamoDB::TYPE_STRING => $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] )
                                    )
                                )
                            )
                    ));
                    if (!($publicKeyNotFound = ($ddb_response->body->Count==0)))  
                        $GIZURCLOUD_SECRET_KEY = (string)$ddb_response->body->Items->secretkey_2->{AmazonDynamoDB::TYPE_STRING};
                } else {
                    $GIZURCLOUD_SECRET_KEY = (string)$ddb_response->body->Items->secretkey_1->{AmazonDynamoDB::TYPE_STRING};
                }

                if ($publicKeyNotFound) 
                    throw new Exception('Could not identify public key');        

                Yii::app()->cache->set($_SERVER['HTTP_X_GIZURCLOUD_API_KEY'], $GIZURCLOUD_SECRET_KEY);
            }

            // Build query arguments list
            $params = array(
                    'Verb'          => Yii::App()->request->getRequestType(),
                    'Model'         => $_GET['model'],
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => $_SERVER['HTTP_X_TIMESTAMP'],
                    'KeyID'         => $_SERVER['HTTP_X_GIZURCLOUD_API_KEY'],
                    'UniqueSalt'    => $_SERVER['HTTP_X_UNIQUE_SALT']
            );
            
            // Sorg arguments
            ksort($params);

            // Generate string for sign
            $string_to_sign = "";
            foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";

            // Generate signature
            $verify_signature = base64_encode(hash_hmac('SHA256', 
                    $string_to_sign, $GIZURCLOUD_SECRET_KEY, 1));
            
            if($_SERVER['HTTP_X_SIGNATURE']!=$verify_signature) 
                throw new Exception('Could not verify signature');
            
            if(Yii::app()->cache->get($_SERVER['HTTP_X_SIGNATURE'])!==false)
                throw new Exception('Used signature');

            Yii::app()->cache->set($_SERVER['HTTP_X_SIGNATURE'], 1, 600);
            
            if ($_GET['model'] == 'About')
                return true;
           
            if(!isset($_SERVER['HTTP_X_USERNAME'])) 
                throw new Exception('Could not find enough credentials');

            if ($_GET['model'] == 'Authenticate' && $_GET['action'] == 'reset')
                return true;

            if(!isset($_SERVER['HTTP_X_PASSWORD'])) 
                throw new Exception('Could not find enough credentials');         
            
             $this->cache_key = json_encode(array(
                'username'=>$_SERVER['HTTP_X_USERNAME'],
                'password'=>$_SERVER['HTTP_X_PASSWORD']
            ));            
            
            $cache_value = false;
            $last_used = Yii::app()->cache->get("last_used_".$this->cache_key);

            if ($last_used!==false) {
                if ($last_used < (time() - 1790)) {
                    Yii::app()->cache->delete($this->cache_key, time());
                } else {
                    $cache_value = Yii::app()->cache->get($this->cache_key); 
                    Yii::app()->cache->set("last_used_".$this->cache_key, time());
                }
            }

            if ($cache_value===false) {
                //Get the Access Key and the Username from vtiger REST 
                //service of the customer portal user's vtiger account
                $rest = new RESTClient();
                $rest->format('json');

                $rest->set_header('Content-Type', 
                        'application/x-www-form-urlencoded');
                $response = $rest->post(Yii::app()->params->vtRestUrl.
                        "?operation=logincustomer", 
                        "username=" . $_SERVER['HTTP_X_USERNAME'] .
                        "&password=" . $_SERVER['HTTP_X_PASSWORD']);
                
                $response = json_decode($response);
                if ($response->success==false)
                    throw new Exception("Invalid Username and Password");

                $username = $response->result->user_name;
                $userAccessKey = $response->result->accesskey;
                $accountId = $response->result->accountId;
                $contactId = $response->result->contactId;

                //Login using $username and $userAccessKey
                $response = $rest->get(Yii::app()->params->vtRestUrl.
                        "?operation=getchallenge&username=$username");
                $response = json_decode($response);
                if ($response->success==false)
                    throw new Exception("Unable to get challenge token");                    
                $challengeToken = $response->result->token;
                $generatedKey = md5($challengeToken.$userAccessKey);
		
                $response = $rest->post(Yii::app()->params->vtRestUrl . 
                        "?operation=login", 
                        "username=$username&accessKey=$generatedKey");
                $response = json_decode($response); 
                if ($response->success==false)
                    throw new Exception("Invalid generated key ");                    
                $sessionId = $response->result->sessionName;
                $response->result->accountId = $accountId;
                $response->result->contactId = $contactId;

                /* Get Contact and Account Name */

                $query = "select * from Contacts" . 
                            " where id = " . $contactId . ";";

                //urlencode to as its sent over http.
                $queryParam = urlencode($query);

                //creating query string
                $params = "sessionName=$sessionId" . 
                            "&operation=query&query=$queryParam";

                //sending request to vtiger REST Service 
                $rest = new RESTClient();
                $rest->format('json');                    
                $contact = $rest->get(Yii::app()->params->vtRestUrl . 
                            "?$params");
                $contact = json_decode($contact, true);
                if (!$contact['success']) {
                    if ($contact['error']['code'] = 'INVALID_SESSIONID')
                        Yii::app()->cache->delete($this->cache_key); 
                    throw new Exception($contact['error']['message']);
                }
                $response->result->contactname = 
                         $contact['result'][0]['firstname'] . 
                         " " . $contact['result'][0]['lastname'];

                $query = "select accountname, account_no from Accounts" . 
                          " where id = " . 
                          $contact['result'][0]['account_id'] . ";";

                //urlencode to as its sent over http.
                $queryParam = urlencode($query);

                //creating query string
                $params = "sessionName=$sessionId" . 
                          "&operation=query&query=$queryParam";

                //sending request to vtiger REST Service 
                $rest = new RESTClient();
                $rest->format('json');                    
                $account = $rest->get(Yii::app()->params->vtRestUrl . 
                            "?$params");
                $account = json_decode($account, true);
                if (!$account['success']){
                        throw new Exception($account['error']['message']);
                }
                $response->result->accountname = 
                                         $account['result'][0]['accountname'];
                $response->result->account_no = 
                                         $account['result'][0]['account_no'];
                $cache_value = json_encode($response->result);

                //Save userid and session id against customerportal 
                //credentials
                Yii::app()->cache->set($this->cache_key, $cache_value, 86000);
                Yii::app()->cache->set("last_used_".$this->cache_key, time());
                $valueFrom = 'database';            
            } 
            
            $this->session = json_decode($cache_value);
            $this->session->challengeToken = $challengeToken;
            return true;
        } catch (Exception $e){
            if ($_GET['model'] != 'About') {
                $response = new stdClass();
                $response->success = false;
                $response->error->code = $this->errors[$e->getCode()];
                $response->error->message = $e->getMessage();

                if ($e->getCode() == 1003) {
		            if (isset($_SERVER['HTTP_X_TIMESTAMP']))
		                $response->error->time_difference = 
                    $_SERVER['REQUEST_TIME'] - strtotime($_SERVER['HTTP_X_TIMESTAMP']);
                    $response->error->time_request_arrived = 
                                           date("c",$_SERVER['REQUEST_TIME']);
                    $response->error->time_request_sent = 
                            date("c",strtotime($_SERVER['HTTP_X_TIMESTAMP']));
                    $response->error->time_server = date("c");
                }

                $this->_sendResponse(403, json_encode($response));
            } else {
                $this->_sendResponse(403, 
                    'An account needs to setup in order to use ' .
                    'this service. Please contact' .
                    '<a href="mailto://sales@gizur.com">sales@gizur.com</a>' .
                    'in order to setup an account.' );
            }
            return false;
        }
    }    
    
    // Actions
    public function actionList() {
        //Tasks include Listing of Troubleticket, Picklists, Assets
        try {
        switch($_GET['model']) {
            case 'About':
                echo 'This mobile app was built using';
                echo ' <a href="gizur.com">gizur.com</a> services.<br><br>';
                break;
            /*
             *******************************************************************
             *******************************************************************
             ** AUTHENTICATE MODEL
             ** Accepts two actions login and logout
             *******************************************************************
             *******************************************************************
             */
            case 'Authenticate':
                if ($_GET['action'] == 'login') {

                    //Return response to client
                    $response = new stdClass();
                    $response->success = true;
                    $response->contactname = $this->session->contactname;
                    $response->accountname = $this->session->accountname;
                    $response->account_no = $this->session->account_no;
                    //$response->valueFrom = $this->session->valueFrom;
                    $this->_sendResponse(200, json_encode($response));
                }
                
                if ($_GET['action'] == 'logout') {

                    $sessionId = $this->session->sessionName;
                    
                    //Logout using $sessionId
                    $rest = new RESTClient();
                    $rest->format('json');                    
                    $response = $rest->get(Yii::app()->params->vtRestUrl.
                            "?operation=logout&sessionName=$sessionId");
                    $response = json_decode($response); 
                    if ($response->success==false)
                        throw new Exception("Unable to Logout");                    
                    
                    //send response to client
                    $response = new stdClass();
                    $response->success = true;
                    $this->_sendResponse(200, json_encode($response));
                }
                break;
            /*
             *******************************************************************
             *******************************************************************
             ** HelpDesk MODEL
             ** Accepts fieldnames and categories (inoperation|damaged)
             *******************************************************************
             *******************************************************************
             */                
            case 'HelpDesk':
                //Is this a request for picklist?
                if (isset($_GET['fieldname'])){

                    $cached_value = Yii::app()->cache->get('picklist_' 
                                                         . $_GET['model'] . '_' 
                                                         . $_GET['fieldname']);

                    if ($cached_value === false) {
                        $sessionId = $this->session->sessionName; 
                        $flipped_custom_fields = 
                                  array_flip(Yii::app()->params->custom_fields['HelpDesk']);
                        if (in_array($_GET['fieldname'], 
                                                    $flipped_custom_fields)){
                            $fieldname = 
                    Yii::app()->params->custom_fields[$_GET['model']][$_GET['fieldname']];
                        } else {
                            $fieldname = $_GET['fieldname'];
                        }
                        //Receive response from vtiger REST service
                        //Return response to client 
                        
                        $params = "sessionName=$sessionId" . 
                                "&operation=describe". 
                                "&elementType=" . $_GET['model'];                    
                        
                        $rest = new RESTClient();
                        $rest->format('json');                    
                        $response = $rest->get(Yii::app()->params->vtRestUrl . 
                                "?$params"); 
                        
                        $response = json_decode($response, true);

                        if ($response['success']==false)
                            throw new Exception('Fetching details failed');
                        
                        foreach ($response['result']['fields'] as $field){
                            if ($fieldname == $field['name']) {
                                if ($field['type']['name'] == 'picklist'){
                                    foreach ($field['type']['picklistValues'] 
                                                          as $key => &$option)
                                    if (isset($option['dependency'])) {
                                        foreach ($option['dependency'] 
                                         as $dep_fieldname => $dependency) {
                                            if(in_array($dep_fieldname, 
                                            Yii::app()->params->custom_fields['HelpDesk'])){
                                                $new_fieldname = 
                                                       $flipped_custom_fields
                                                             [$dep_fieldname];
                                                $option['dependency']
                                                            [$new_fieldname] = 
                                                          $option['dependency']
                                                             [$dep_fieldname];
                                               unset($option
                                              ['dependency'][$dep_fieldname]);
                                           } 
                                        }
                                    }
                                    $content = json_encode(array(
                                        'success' => true, 
                                        'result' => 
                                              $field['type']['picklistValues']
                                        ));
                                    Yii::app()->cache->set('picklist_' 
                                                               . $_GET['model'] 
                                                                           . '_' 
                                                          . $_GET['fieldname'], 
                                                               $content, 3600);
                                    $this->_sendResponse(200, $content);
                                    break 2;
                                }
                                throw new Exception("Not an picklist field");
                            }
                        }
                        throw new Exception("Fieldname not found"); 
                    } else {
                        $this->_sendResponse(200, $cached_value);
                    }
                } 
                
                //Is this a request for listing categories
                if (isset($_GET['category'])) {
                    $sessionId = $this->session->sessionName;
                    $accountId = $this->session->accountId;
                    $contactId = $this->session->contactId;

                    //Send request to vtiger REST service
                    $query = "select * from " . $_GET['model']; 

                    //creating where clause based on parameters
                    $where_clause = Array();
                    if ($_GET['category']=='inoperation') {
                        $where_clause[] = "ticketstatus = 'Closed'";
                    }
                    if ($_GET['category']=='damaged') {
                        $where_clause[] = "ticketstatus = 'Open'";
                    }

                    //$where_clause[] = "parent_id = " . $contactId;
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
                                    "Invalid month specified in list criteria");
                            $where_clause[] = "createdtime >= '" . 
                                $_GET['year'] . "-" . $startmonth . "-01'";
                            $where_clause[] = "createdtime <= '" . 
                                $_GET['year'] . "-" . $endmonth . "-31'";
                        }
                    }

                    if (isset($_GET['trailerid'])){
                        if ($_GET['trailerid']!='0')
                            $where_clause[] = Yii::app()->params->custom_fields
                                                    ['HelpDesk']['trailerid'] . 
                                             " = '" . $_GET['trailerid'] . "'";
                    }
                      
                    if (count($where_clause)!=0) 
                        $query = $query . " where " . 
                            implode(" and ", $where_clause);
                    $query = $query . ";"; 
                    
                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);
                    
                    //creating query string
                    $params = "sessionName=$sessionId" . 
                            "&operation=query&query=$queryParam";

                    //Receive response from vtiger REST service
                    //Return response to client  
                    $rest = new RESTClient();
                    $rest->format('json');                    
                    $response = $rest->get(Yii::app()->params->vtRestUrl . 
                            "?$params");
                    $response = json_decode($response, true);

                    if ($response['success']==false)
                        throw new Exception('Fetching details failed ' . $query);


                    //Get Accounts List
                    $query = "select * from Accounts;";
                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);
                    
                    //creating query string
                    $params = "sessionName=$sessionId" . 
                            "&operation=query&query=$queryParam";

                    //Receive response from vtiger REST service
                    //Return response to client  
                    $rest = new RESTClient();
                    $rest->format('json');                    
                    $accounts = $rest->get(Yii::app()->params->vtRestUrl . 
                            "?$params");
                    $accounts = json_decode($accounts, true);
                    if ($accounts['success']==true) {
                        $tmp_accounts = array();
                        if (isset($accounts['result']))
                            foreach($accounts['result'] as $account) 
                                $tmp_accounts[$account['id']] = $account['accountname'];
                    }


                    //Get Contact List
                    $query = "select * from Contacts;";
                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);
                    
                    //creating query string
                    $params = "sessionName=$sessionId" . 
                            "&operation=query&query=$queryParam";

                    //Receive response from vtiger REST service
                    //Return response to client  
                    $rest = new RESTClient();
                    $rest->format('json');                    
                    $contacts = $rest->get(Yii::app()->params->vtRestUrl . 
                            "?$params");
                    $contacts = json_decode($contacts, true);
                    if ($contacts['success']==true) {
                        $tmp_contacts = array();
                        if (isset($contacts['result']))
                            foreach($contacts['result'] as $contact) {
                                $tmp_contacts[$contact['id']]['contactname'] = $contact['firstname'] . ' ' . $contact['lastname'];
                                $tmp_contacts[$contact['id']]['accountname'] = $tmp_accounts[$contact['account_id']];
                            }
                    }

                    $custom_fields = Yii::app()->params->custom_fields['HelpDesk'];
                    
                    foreach($response['result'] as &$troubleticket){
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
                        foreach($troubleticket as $fieldname => $value){
                            $key_to_replace = array_search($fieldname, 
                                                              $custom_fields);
                            if ($key_to_replace) {
                               unset($troubleticket[$fieldname]);
                               $troubleticket[$key_to_replace] = $value;
                               //unset($custom_fields[$key_to_replace]);                                
                            }
                        }
                    }
                    
                    $this->_sendResponse(200, json_encode($response));
                }
                break;
            /*
             *******************************************************************
             *******************************************************************
             ** Assets MODEL
             ** Accepts fieldnames 
             *******************************************************************
             *******************************************************************
             */                 
            case 'Assets':
                $sessionId = $this->session->sessionName;
                $accountId = $this->session->accountId;
                
                //Send request to vtiger REST service
                $query = "select * from " . $_GET['model'] . ";"; 

                //urlencode to as its sent over http.
                $queryParam = urlencode($query);

                //creating query string
                $params = "sessionName=$sessionId" . 
                        "&operation=query&query=$queryParam";

                //Receive response from vtiger REST service
                //Return response to client  
                $rest = new RESTClient();
                $rest->format('json');                    
                $response = $rest->get(Yii::app()->params->vtRestUrl . 
                        "?$params");               
                $response = json_decode($response, true);
                if ($response['success']==false)
                    throw new Exception('Unable to fetch details');
                $custom_fields = Yii::app()->params->custom_fields['Assets'];

                foreach($response['result'] as &$asset){
                    unset($asset['update_log']);
                    unset($asset['hours']);
                    unset($asset['days']);
                    unset($asset['modifiedtime']);
                    unset($asset['from_portal']);
                    foreach($asset as $fieldname => $value){
                        $key_to_replace = array_search($fieldname, 
                                                              $custom_fields);
                        if ($key_to_replace) {
                            unset($asset[$fieldname]);
                            $asset[$key_to_replace] = $value;
                            //unset($custom_fields[$key_to_replace]);                                
                        }
                    }
                }
                $this->_sendResponse(200, json_encode($response));
                break;                  
            
            default :
                $response = new stdClass();
                $response->success = false;

                $response->error->code = $this->errors[1004];
                $response->error->message = "Not a valid method" . 
                        " for model "  . $_GET['model'];
                $this->_sendResponse(405, json_encode($response));

                break;
        }
        } catch (Exception $e) {
                $response = new stdClass();
                $response->success = false;
                $response->error->code = "ERROR";
                $response->error->message = $e->getMessage();
                $this->_sendResponse(400, json_encode($response));
        }
    }

    public function actionView() {
        //Tasks include detail view of a specific Troubleticket and Assets
        try {
        switch($_GET['model']) {
            /*
             *******************************************************************
             *******************************************************************
             ** User MODEL
             ** Accepts id
             *******************************************************************
             *******************************************************************
             */                
            case 'User':
                // Instantiate the class
                $dynamodb = new AmazonDynamoDB();
                $dynamodb->set_region(constant("AmazonDynamoDB::".Yii::app()->params->awsDynamoDBRegion)); 

                // Get an item
                $ddb_response = $dynamodb->get_item(array(
                    'TableName' => Yii::app()->params->awsDynamoDBTableName,
                    'Key' => $dynamodb->attributes(array(
                    'HashKeyElement'  => $_GET['email'],
                    )),
                    'ConsistentRead' => 'true'
                ));
                    
                if (isset($ddb_response->body->Item)) {
                    foreach($ddb_response->body->Item->children() 
                                                       as $key => $item) {
                       $result->{$key} = 
                              (string)$item->{AmazonDynamoDB::TYPE_STRING};
                    }
                    $response->success = true;
                    $response->result = $result;
                    $this->_sendResponse(200, json_encode($response));
                } else {
                    $response->success = false;
                    $response->error->code = "NOT_FOUND";
                    $response->error->message = $_GET['email'] . " was " .
                                                                       " not found";
                    $this->_sendResponse(404, json_encode($response));                        
                }
                break;
            /*
             *******************************************************************
             *******************************************************************
             ** HelpDesk MODEL
             ** Accepts id
             *******************************************************************
             *******************************************************************
             */                
            case 'HelpDesk':
                $sessionId = $this->session->sessionName;
               
                /*Get HelpDesk details*/                
 
                //Creating vTiger Query
                $query = "select * from " . $_GET['model'] . 
                        " where id = " . $_GET['id'] . ";";

                //urlencode to as its sent over http.
                $queryParam = urlencode($query);

                //creating query string
                $params = "sessionName=$sessionId" . 
                        "&operation=query&query=$queryParam";

                //sending Request vtiger REST service
                $rest = new RESTClient();
                $rest->format('json');                    
                $response = $rest->get(Yii::app()->params->vtRestUrl . 
                        "?$params"); 
                $response = json_decode($response, true);
                $response['result'] = $response['result'][0]; 
 
                if (!$response['success']) 
                    throw new Exception($response['error']['message']);

                /*Get Documents Ids*/

                //urlencode to as its sent over http.
                $queryParam = urlencode($query);

                //creating query string
                $params = "sessionName=$sessionId" . 
                        "&operation=getrelatedtroubleticketdocument" . 
                        "&crmid=" . $_GET['id'];

                //sending request vtiger REST service
                $rest = new RESTClient();
                $rest->format('json');                    
                $documentids = $rest->get(Yii::app()->params->vtRestUrl . 
                        "?$params"); 
                $documentids = json_decode($documentids, true);
                $documentids = $documentids['result'];
                
                /*Get Document Details*/
                if (count($documentids)!=0) {
                    $query = "select * from Documents" . 
                            " where id in (" . $this->ws_entities['Documents'] 
                                                                         . "x" . 
                                 implode(", " . $this->ws_entities['Documents']
                                                  . "x", $documentids) . ");";

                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);

                    //creating query string
                    $params = "sessionName=$sessionId" . 
                            "&operation=query&query=$queryParam";

                    //sending request to vtiger REST Service 
                    $rest = new RESTClient();
                    $rest->format('json');                    
                    $documents = $rest->get(Yii::app()->params->vtRestUrl . 
                            "?$params");
                    $documents = json_decode($documents, true);
                    if (!$documents['success'])
                        throw new Exception($documents['error']['message']);
                    $response['result']['documents'] = $documents['result'];
                }
                
                /*Get Contact's Name*/ 
                 if ($response['result']['parent_id']!='') {
                    $query = "select * from Contacts" . 
                            " where id = " . 
                                       $response['result']['parent_id'] . ";";

                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);

                    //creating query string
                    $params = "sessionName=$sessionId" . 
                            "&operation=query&query=$queryParam";

                    //sending request to vtiger REST Service 
                    $rest = new RESTClient();
                    $rest->format('json');                    
                    $contact = $rest->get(Yii::app()->params->vtRestUrl . 
                            "?$params");
                    $contact = json_decode($contact, true);
                    if (!$contact['success'])
                        throw new Exception($contact['error']['message']);
                    $response['result']['contactname'] = 
                                                        $contact['result'][0];

                    $query = "select accountname from Accounts" . 
                            " where id = " . 
                                    $contact['result'][0]['account_id'] . ";";

                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);

                    //creating query string
                    $params = "sessionName=$sessionId" . 
                            "&operation=query&query=$queryParam";

                    //sending request to vtiger REST Service 
                    $rest = new RESTClient();
                    $rest->format('json');                    
                    $account = $rest->get(Yii::app()->params->vtRestUrl . 
                            "?$params");
                    $account = json_decode($account, true);
                    if (!$account['success'])
                        throw new Exception($account['error']['message']);
                    $response['result']['accountname'] = 
                                         $account['result'][0]['accountname'];

                }
                               
                $custom_fields = Yii::app()->params->custom_fields['HelpDesk'];

                unset($response['result']['update_log']);
                unset($response['result']['hours']);
                unset($response['result']['days']);
                unset($response['result']['modifiedtime']);
                unset($response['result']['from_portal']);
                foreach($response['result'] as $fieldname => $value){
                    $key_to_replace = array_search($fieldname, 
                                                              $custom_fields);
                    if ($key_to_replace) {
                        unset($response['result'][$fieldname]);
                        $response['result'][$key_to_replace] = $value;
                    }
                }                                
                
                $this->_sendResponse(200, json_encode($response));
                break;
            
            /*
             *******************************************************************
             *******************************************************************
             ** Assets MODEL
             ** Accepts id
             *******************************************************************
             *******************************************************************
             */             
            case 'Assets': 
                $sessionId = $this->session->sessionName;
                
                //Send request to vtiger REST service
                $query = "select * from " . $_GET['model'] . 
                        " where id = " . $_GET['id'] . ";";

                //urlencode to as its sent over http.
                $queryParam = urlencode($query);

                //creating query string
                $params = "sessionName=$sessionId" . 
                        "&operation=query&query=$queryParam";

                //Receive response from vtiger REST service
                //Return response to client  
                $rest = new RESTClient();
                $rest->format('json');                    
                $response = $rest->get(Yii::app()->params->vtRestUrl . 
                        "?$params");  
                
                $response = json_decode($response, true);
                $response['result'] = $response['result'][0]; 

                $custom_fields = Yii::app()->params->custom_fields['Assets'];

                foreach($response['result'] as $fieldname => $value){
                    $key_to_replace = array_search($fieldname, 
                                                              $custom_fields);
                    if ($key_to_replace) {
                        unset($response['result'][$fieldname]);
                        $response['result'][$key_to_replace] = $value;
                        //unset($custom_fields[$key_to_replace]);                                
                    }
                }                                
                
                $this->_sendResponse(200, json_encode($response));
                break;
            
            /*
             *******************************************************************
             *******************************************************************
             ** DocumentAttachments MODEL
             ** Accepts notesid
             *******************************************************************
             *******************************************************************
             */             
            case 'DocumentAttachments':
                $sessionId = $this->session->sessionName;

                //urlencode to as its sent over http.
                $queryParam = urlencode($query);

                //creating query string
                $params = "sessionName=$sessionId" . 
                        "&operation=gettroubleticketdocumentfile" . 
                        "&notesid=".$_GET['id'];
    
                //Receive response from vtiger REST service
                //Return response to client  
                $rest = new RESTClient();
                $rest->format('json');                    
                $response = $rest->get(Yii::app()->params->vtRestUrl . 
                        "?$params");              
                $response = json_decode($response);
                      
                $s3 = new AmazonS3();
                $s3->set_region(constant("AmazonS3::".Yii::app()->params->awsS3Region)); 

                $unique_id = uniqid();
                 
                $file_resource = fopen('protected/data/'. $unique_id .
                        $response->result->filename,'x');
                $s3response = $s3->get_object(Yii::app()->params->awsS3Bucket, 
                        $response->result->filename, 
                        array(
                    'fileDownload' => $file_resource
                ));
              
                if (!$s3response->isOK())
                   throw new Exception("File not found.");
               
                $response->result->filecontent = 
                        base64_encode(
                                file_get_contents('protected/data/' . $unique_id .
                                        $response->result->filename));
                unlink('protected/data/' . $unique_id .  $response->result->filename); 
 
                $filename_sanitizer = explode("_",
                                                 $response->result->filename);               
                unset($filename_sanitizer[0]);               
                unset($filename_sanitizer[1]);
                $response->result->filename = implode('_', 
                                                        $filename_sanitizer); 
                $this->_sendResponse(200, json_encode($response)); 
                break;
            
            default :
                $response = new stdClass();
                $response->success = false;
                $response->error->code = $this->errors[1004];
                $response->error->message = "Not a valid method" . 
                        " for model "  . $_GET['model'];
                $this->_sendResponse(405, json_encode($response));
                break;            
        }
        } catch (Exception $e) {
                $response = new stdClass();
                $response->success = false;
                $response->error->code = "ERROR";
                $response->error->message = $e->getMessage();
                $this->_sendResponse(400, json_encode($response));            
        }
    }

    public function actionCreate() {
        //Tasks include detail view of a specific Troubleticket and Assets
        try {
        switch($_GET['model']) {
            /*
             *******************************************************************
             *******************************************************************
             ** User MODEL
             ** Accepts id
             *******************************************************************
             *******************************************************************
             */                
            case 'User':
                $sessionId = $this->session->sessionName;
                $post = json_decode(file_get_contents('php://input'), true);
                
                $post['secretkey_1'] = uniqid("", true) . uniqid("", true);
                $post['apikey_1'] = strtoupper(uniqid("GZCLD" . uniqid()));

                $post['secretkey_2'] = uniqid("", true) . uniqid("", true);
                $post['apikey_2'] = strtoupper(uniqid("GZCLD" . uniqid()));


                // Instantiate the class
                $dynamodb = new AmazonDynamoDB();
                $dynamodb->set_region(constant("AmazonDynamoDB::".Yii::app()->params->awsDynamoDBRegion)); 
                $ddb_response = $dynamodb->put_item(array(
                    'TableName' => Yii::app()->params->awsDynamoDBTableName,
                    'Item' => $dynamodb->attributes($post)
                ));
                
                // Get an item
                $ddb_response = $dynamodb->get_item(array(
                    'TableName' => Yii::app()->params->awsDynamoDBTableName,
                    'Key' => $dynamodb->attributes(array(
                    'HashKeyElement'  => $post['id'],
                    )),
                    'ConsistentRead' => 'true'
                ));
                
                if (isset($ddb_response->body->Item)) {
                    Yii::app()->cache->set($post['apikey_1'], $post['secretkey_1']);
                    Yii::app()->cache->set($post['apikey_2'], $post['secretkey_2']);
                    foreach($ddb_response->body->Item->children() 
                                                       as $key => $item) {
                       $result->{$key} = 
                              (string)$item->{AmazonDynamoDB::TYPE_STRING};
                    }

                    $response->success = true;
                    $response->result = $result;
                    $this->_sendResponse(200, json_encode($response));
                } else {
                    $response->success = false;
                    $response->error->code = "NOT_CREATED";
                    $response->error->message = $_GET['email'] . " could "
                                                                . " not be created";
                    $this->_sendResponse(400, json_encode($response));                        
                }
            break; 
            /*
             *******************************************************************
             *******************************************************************
             ** HelpDesk MODEL
             ** Accepts id
             *******************************************************************
             *******************************************************************
             */                
            case 'HelpDesk': 
                $script_started = date("c"); 
                if (!isset($_POST['ticketstatus']) || 
                                               empty($_POST['ticketstatus']))
                    throw new Exception("ticketstatus does not have a value"
                                                                         ,1001);

                if (!isset($_POST['reportdamage']) || 
                                              empty($_POST['reportdamage']))
                    throw new Exception("reportdamage does not have a value"
                                                                         ,1001);

                if (!isset($_POST['trailerid']) || 
                                              empty($_POST['trailerid']))
                    throw new Exception("trailerid does not have a value"
                                                                         ,1001);

                if (!isset($_POST['ticket_title']) || 
                                              empty($_POST['ticket_title']))
                    throw new Exception("ticket_title does not have a value"
                                                                         ,1001);

                if ($_POST['ticketstatus']=='Open' &&
                                                  $_POST['reportdamage']=='No')
                    throw new Exception("Ticket can be opened for damaged trailers only"
                                                                         ,1002);

                $sessionId = $this->session->sessionName;
                $userId = $this->session->userId;
                           
                /** Creating Touble Ticket**/
                $post = $_POST;
                $custom_fields = array_flip(
                                             Yii::app()->params->custom_fields['HelpDesk']);
                
                foreach ($post as $k => $v) {
                    $key_to_replace = array_search($k, $custom_fields);
                    if ($key_to_replace){
                        unset ($post[$k]);
                        $post[$key_to_replace] = $v;
                    }
                }
                //get data json 
                $dataJson = json_encode(array_merge(
                        $post,
                        array(
                            'parent_id' => $this->session->contactId,
                            'assigned_user_id' => $this->session->userId,
                            'ticketstatus' => (isset($post['ticketstatus']) 
             && !empty($post['ticketstatus']))?$post['ticketstatus']:'Closed',
                        )));
                
                //Receive response from vtiger REST service
                //Return response to client  
                $rest = new RESTClient();
                $rest->format('json');                    
                $response = $rest->post(Yii::app()->params->vtRestUrl, array(
                    'sessionName' => $sessionId,
                    'operation' => 'create',
                    'element' => $dataJson,
                    'elementType' => $_GET['model']
                ));  
                
                $globalresponse = json_decode($response);
                /**Creating Document**/
                
                if ($globalresponse->success == false)
                    throw new Exception($globalresponse->error->message);
                 
                //Create Documents if any is attached
                $crmid = $globalresponse->result->id;
                $globalresponse->result->documents = Array();
                $dataJson = array(
                    'notes_title'=>'Attachement', 
                    'assigned_user_id'=>$userId,
                    'notecontent' => 'Attachement',
                    'filelocationtype' => 'I',
                    'filedownloadcount' => null,
                    'filestatus' => 1,
                    'fileversion' => '',
                    );
                if (!empty($_FILES) && $globalresponse->success){
                    foreach ($_FILES as $key => $file){
                        $uniqueid = uniqid();
                        
                        $dataJson['filename'] = $crmid . "_" . $uniqueid . "_" . $file['name'];
                        $dataJson['filesize'] = $file['size'];
                        $dataJson['filetype'] = $file['type'];
                        
                        //Upload file to Amazon S3
                        $s3 = new AmazonS3();
                        $s3->set_region(constant("AmazonS3::".Yii::app()->params->awsS3Region)); 

                        $response = $s3->create_object(
                                Yii::app()->params->awsS3Bucket, 
                                $crmid . '_' . $uniqueid . '_' . $file['name'], 
                                array(
                            'fileUpload' => $file['tmp_name'],
                            'contentType' => $file['type'],
                            'headers' => array(
                                'Cache-Control'    => 'max-age',
                                'Content-Language' => 'en-US',
                                'Expires'          => 
                                'Thu, 01 Dec 1994 16:00:00 GMT',
                            )
                        ));                        
                        
                        if ($response->isOK()) {
                            //Create document
                            $rest = new RESTClient();
                            $rest->format('json'); 
                            $document = $rest->post(Yii::app()->params->vtRestUrl, 
                                    array(
                                                'sessionName' => $sessionId,
                                                'operation' => 'create',
                                                'element' => 
                                                            json_encode($dataJson),
                                                'elementType' => 'Documents'
                                            ));
                            $document = json_decode($document);
                            if ($document->success) {
                                $notesid = $document->result->id;
                                        
                                //Relate Document with Trouble Ticket
                                $rest = new RESTClient();
                                $rest->format('json'); 
                                $response = $rest->post(Yii::app()->params->vtRestUrl, 
                                        array(
                                                    'sessionName' => $sessionId,
                                                    'operation' => 
                                            'relatetroubleticketdocument',
                                                    'crmid' => $crmid,
                                                    'notesid' => $notesid
                                                ));
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
                foreach($globalresponse['result'] as $fieldname => 
                                                                       $value){
                    $key_to_replace = array_search($fieldname, 
                                                              $custom_fields);
                    if ($key_to_replace) {
                        unset($globalresponse['result'][$fieldname]);
                        $globalresponse['result'][$key_to_replace] = 
                                                                        $value;
                        //unset($custom_fields[$key_to_replace]);                                
                    }
                }
                   
                if ($post['ticketstatus']!='Closed') { 
                    $email = new AmazonSES();
                    //$email->set_region(constant("AmazonSES::" . Yii::app()->params->awsSESRegion));
                    
                    $SESresponse = $email->send_email(
                         Yii::app()->params->awsSESFromEmailAddress, // Source (aka From)
                         array('ToAddresses' => array( // Destination (aka To)
                                 $_SERVER['HTTP_X_USERNAME'],
                                 Yii::app()->params->awsSESClientEmailAddress
                         )),
                         array( // Message (short form)
                             'Subject.Data' => 'New Damaged Ticket Created',
                             'Body.Text.Data' => 'Dear Gizur Account Holder, ' . 
                             PHP_EOL . 
                             PHP_EOL . 
                             'A new damaged ticket has been created, with Ticket No.: ' .
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
                $response->error->code = $this->errors[1004];
                $response->error->message = "Not a valid method" . 
                        " for model "  . $_GET['model'];
                $this->_sendResponse(405, json_encode($response));
                break;            
        }
        } catch (Exception $e) {
                $response = new stdClass();
                $response->success = false;
                $response->error->code = $this->errors[$e->getCode()];
                $response->error->message = $e->getMessage();
                $this->_sendResponse(400, json_encode($response));            
        }
    }

    public function actionError() {
        $response = new stdClass();
        $response->success = false;
        if (isset($this->valid_models[$_GET['model']])) {
            $response->error->code = $this->errors[1004];
            $response->error->message = "Not a valid method" . 
                        " for model "  . $_GET['model'];
            $this->_sendResponse(405, json_encode($response));
        } else {
            $response->error->code = "NOT_FOUND";
            $response->error->message = "Such a service is not provided by" . 
                " this REST service";
            $this->_sendResponse(404, json_encode($response));
        }
    }  
 
    
    public function actionUpdate() {
        //Tasks include detail updating Troubleticket
        try {
        switch($_GET['model']) {

            /*
             *******************************************************************
             *******************************************************************
             ** Authenticate MODEL
             ** Accepts reset / changepw
             *******************************************************************
             *******************************************************************
             */                
            case 'Authenticate':
                if ($_GET['action'] == 'reset') {

                    $email = new AmazonSES();
                    //$email->set_region(constant("AmazonSES::" . Yii::app()->params->awsSESRegion));
                    $response = $email->list_verified_email_addresses();

                    if ($response->isOK()) {
                        $verifiedEmailAddresses = (Array)$response->body->ListVerifiedEmailAddressesResult->VerifiedEmailAddresses;
                        $verifiedEmailAddresses = $verifiedEmailAddresses['member']; 
                        if (in_array(Yii::app()->params->awsSESFromEmailAddress, $verifiedEmailAddresses) == false) {
                            $email->verify_email_address(Yii::app()->params->awsSESFromEmailAddress);
                            throw new Exception('From Email Address not verified. Contact Gizur Admin.');
                        }
                    }

                    //Receive response from vtiger REST service
                    //Return response to client  
                    $rest = new RESTClient();
                    $rest->format('json');                    
                    $response = $rest->post(Yii::app()->params->vtRestUrl, array(
                        'operation' => 'resetpassword',
                        'username' => $_SERVER['HTTP_X_USERNAME'],
                    )); 
                    $response = json_decode($response); 
                    
                    if ($response->success==false)
                        throw new Exception("Unable to reset password"); 

                    $SESresponse = $email->send_email(
                         Yii::app()->params->awsSESFromEmailAddress, // Source (aka From)
                         array('ToAddresses' => array( // Destination (aka To)
                                 $_SERVER['HTTP_X_USERNAME']
                         )),
                         array( // Message (short form)
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
                    //Receive response from vtiger REST service
                    //Return response to client  
                    $rest = new RESTClient();
                    $rest->format('json');                    
                    $response = $rest->post(Yii::app()->params->vtRestUrl, array(
                        'sessionName' => $this->session->sessionName,
                        'operation' => 'changepw',
                        'username' => $_SERVER['HTTP_X_USERNAME'],
                        'oldpassword' => $_SERVER['HTTP_X_PASSWORD'],
                        'newpassword' => $_PUT['newpassword']
                    )); 
                    $response = json_decode($response);
                    if ($response->success == false)
                        throw new Exception($response->error->message);
                         
                    $this->_sendResponse(200, json_encode($response));            
                }
            /*
             *******************************************************************
             *******************************************************************
             ** User MODEL
             ** Accepts id
             *******************************************************************
             *******************************************************************
             */                
            case 'User':
                if (isset($_GET['field'])) {
		            $keyid = str_replace('keypair','',$_GET['field']);
                    
                    // Instantiate the class
                    $dynamodb = new AmazonDynamoDB();
                    $dynamodb->set_region(constant("AmazonDynamoDB::".Yii::app()->params->awsDynamoDBRegion)); 

                    // Get an item
                    $ddb_response = $dynamodb->get_item(array(
                        'TableName' => Yii::app()->params->awsDynamoDBTableName,
                        'Key' => $dynamodb->attributes(array(
                        'HashKeyElement'  => $_GET['email'],
                        )),
                        'ConsistentRead' => 'true'
                    ));
             
                    foreach($ddb_response->body->Item->children() 
                                                           as $key => $item) {
                           $result[$key] = 
                                  (string)$item->{AmazonDynamoDB::TYPE_STRING};
                    }


                    Yii::app()->cache->delete($result['apikey_' . $keyid]);

                    /* Create the private and public key */
                    $result['secretkey_' . $keyid] = uniqid("", true) . 
                                                               uniqid("", true);
                    $result['apikey_' . $keyid] = strtoupper(uniqid("GZCLD" 
                                                                  . uniqid()));
                    
                    Yii::app()->cache->set($result['apikey_' . $keyid], $result['secretkey_' . $keyid]);
                     
                    $ddb_response = $dynamodb->put_item(array(
                        'TableName' => Yii::app()->params->awsDynamoDBTableName,
                        'Item' => $dynamodb->attributes($result)
                    ));


                    if ($response->success = $ddb_response->isOK())
                        $response->result = $result;
                    
                    $this->_sendResponse(200, json_encode($response));
            } else {
                    $post = json_decode(file_get_contents('php://input'), true);
                    // Instantiate the class
                    $dynamodb = new AmazonDynamoDB();
                    $dynamodb->set_region(constant("AmazonDynamoDB::".Yii::app()->params->awsDynamoDBRegion)); 
                    $ddb_response = $dynamodb->put_item(array(
                        'TableName' => Yii::app()->params->awsDynamoDBTableName,
                        'Item' => $dynamodb->attributes($post)
                    ));
                    $response = new stdClass();
                    $response->success = $ddb_response->isOK();
                    $this->_sendResponse(200, json_encode($response));
            }
            break;
            /*
             *******************************************************************
             *******************************************************************
             ** HelpDesk MODEL
             ** Accepts id
             *******************************************************************
             *******************************************************************
             */                
            case 'HelpDesk':
                $sessionId = $this->session->sessionName;
                
                //Receive response from vtiger REST service
                //Return response to client  
                $rest = new RESTClient();
                $rest->format('json');     
                
                
                $response = $rest->get(Yii::app()->params->vtRestUrl, array(
                    'sessionName' => $sessionId,
                    'operation' => 'retrieve',
                    'id' => $_GET['id']
                ));                
                
                $response = json_decode($response, true);
                
                //get data json 
                $retrivedObject = $response['result'];
                $retrivedObject['ticketstatus'] = 'Closed';
                //Receive response from vtiger REST service
                //Return response to client  
                $rest = new RESTClient();
                $rest->format('json');                    
                $response = $rest->post(Yii::app()->params->vtRestUrl, array(
                    'sessionName' => $sessionId,
                    'operation' => 'update',
                    'element' => json_encode($retrivedObject)
                ));  

                $response = json_decode($response, true);
                
                $custom_fields = Yii::app()->params->custom_fields['HelpDesk'];

                
                unset($response['result']['update_log']);
                unset($response['result']['hours']);
                unset($response['result']['days']);
                unset($response['result']['modifiedtime']);
                unset($response['result']['from_portal']);
                foreach($response['result'] as $fieldname => $value){
                    $key_to_replace = array_search($fieldname, 
                                                              $custom_fields);
                    if ($key_to_replace) {
                        unset($response['result'][$fieldname]);
                        $response['result'][$key_to_replace] = $value;
                        //unset($custom_fields[$key_to_replace]);                                
                    }
                }
                
                $this->_sendResponse(200, json_encode($response));                
                
                break;
            /*
             *******************************************************************
             *******************************************************************
             ** HelpDesk MODEL
             ** Accepts id
             *******************************************************************
             *******************************************************************
             */                
            case 'Assets':
                //Receive response from vtiger REST service
                //Return response to client  
                $rest = new RESTClient();
                $rest->format('json');     

                $_PUT = Array();
                parse_str(file_get_contents('php://input'), $_PUT);

                $response = $rest->get(Yii::app()->params->vtRestUrl, array(
                    'sessionName' => $this->session->sessionName,
                    'operation' => 'retrieve',
                    'id' => $_GET['id']
                ));
                
                $response = json_decode($response, true);
                
                //get data json 
                $retrivedObject = $response['result'];
                if ($_PUT['assetstatus']=='In Service')
                    $retrivedObject['assetstatus'] = 'In Service';
                else
                    $retrivedObject['assetstatus'] = 'Out-of-service';

                //Receive response from vtiger REST service
                //Return response to client  
                $rest = new RESTClient();
                $rest->format('json');                    
                $response = $rest->post(Yii::app()->params->vtRestUrl, array(
                    'sessionName' => $this->session->sessionName,
                    'operation' => 'update',
                    'element' => json_encode($retrivedObject)
                ));  
                
                $response = json_decode($response, true);
               
                if ($response['success']==false)
                    throw new Exception($response['error']['message']);
                
                $custom_fields = Yii::app()->params->custom_fields['Assets'];
                
                unset($response['result']['update_log']);
                unset($response['result']['hours']);
                unset($response['result']['days']);
                unset($response['result']['modifiedtime']);
                unset($response['result']['from_portal']);
                foreach($response['result'] as $fieldname => $value){
                    $key_to_replace = array_search($fieldname, 
                                                              $custom_fields);
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
                $response->error->code = $this->errors[1004];
                $response->error->message = "Not a valid method" . 
                        " for model ";
                $this->_sendResponse(405, json_encode($response));
                break;            
        }
        } catch (Exception $e) {
                $response = new stdClass();
                $response->success = false;
                $response->error->code = "ERROR";
                $response->error->message = $e->getMessage();
                $this->_sendResponse(400, json_encode($response));            
        }
    }     
}
