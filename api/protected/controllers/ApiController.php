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
        'DocumentAttachements',
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
     * Aliasing custom fields
     */

    private $custom_fields = Array(
        'HelpDesk' => Array(
            'tickettype' => 'cf_641',
            'trailerid' => 'cf_642',
            'damagereportlocation' => 'cf_643',
            'sealed' => 'cf_644',
            'plates' => 'cf_645',
            'straps'  => 'cf_646',
            'reportdamage' => 'cf_647',
            'damagetype' => 'cf_648',
            'damageposition' => 'cf_649',
            'drivercauseddamage' => 'cf_650'
        ),
        'Assets' => Array(
            'trailertype' => 'cf_640'
        )
    );    

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
            if ($_GET['model'] == 'About' || $_GET['model'] == 'User')
                return true;
            
            //check if public key exists
            if (!isset($_SERVER['HTTP_X_GIZURCLOUD_API_KEY']))
                throw new Exception('Public Key Not Found in request');
            
            // Retreive Key pair from Amazon Dynamodb
            $GIZURCLOUD_SECRET_KEY  = "9b45e67513cb3377b0b18958c4de55be";
            $GIZURCLOUD_API_KEY = "GZCLDFC4B35B";            
            
            if ($_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] 
                                                     != $GIZURCLOUD_API_KEY) 
                throw new Exception('Could not identify public key');
            
            if (!isset($_SERVER['HTTP_X_TIMESTAMP']))
                throw new Exception('Timestamp not found in request');
            else
                $timestamp = $_SERVER['HTTP_X_TIMESTAMP'];
            
            if ( $_SERVER["REQUEST_TIME"] - Yii::app()->params->acceptableTimestampError > 
                    strtotime($_SERVER['HTTP_X_TIMESTAMP']))
		    throw new Exception('Stale request', 1003);
            
            if ($_SERVER["REQUEST_TIME"] + Yii::app()->params->acceptableTimestampError <
                    strtotime($_SERVER['HTTP_X_TIMESTAMP']))
		    throw new Exception('Oh, Oh, Oh, request from the FUTURE! ' . $_SERVER['REQUEST_TIME'] . ' ' . strtotime($_SERVER['HTTP_X_TIMESTAMP']), 1003);
            
            if (!isset($_SERVER['HTTP_X_SIGNATURE']))
                throw new Exception('Signature not found');
            else
                $signature = $_SERVER['HTTP_X_SIGNATURE'];

            // Build query arguments list
            $params = array(
                    'Verb'          => Yii::App()->request->getRequestType(),
                    'Model'         => $_GET['model'],
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => $timestamp,
                    'KeyID'         => $GIZURCLOUD_API_KEY
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
            
            if($signature!=$verify_signature) 
                throw new Exception('Could not verify signature');
            
            if(!isset($_SERVER['HTTP_X_USERNAME']) 
                    || !isset($_SERVER['HTTP_X_PASSWORD'])) 
                throw new Exception('Could not find enough credentials');

            //Get $customerportal_username and $customerportal_password 
            //from header
            $customerportal_username = $_SERVER['HTTP_X_USERNAME'];
            $customerportal_password = $_SERVER['HTTP_X_PASSWORD'];            
            
            $cache_key = json_encode(array(
                'username'=>$customerportal_username,
                'password'=>$customerportal_password
            ));            
            
            $cache_value = Yii::app()->cache->get($cache_key);            
            if ($cache_value===false) {
                //Get the Access Key and the Username from vtiger REST 
                //service of the customer portal user's vtiger account
                $rest = new RESTClient();
                $rest->format('json');

                $rest->set_header('Content-Type', 
                        'application/x-www-form-urlencoded');
                $response = $rest->post(Yii::app()->params->vtRestUrl.
                        "?operation=logincustomer", 
                        "username=$customerportal_username" . 
                        "&password=$customerportal_password");
                
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
                if (!$contact['success'])
                    throw new Exception($contact['error']['message']);
                $response->result->contactname = 
                         $contact['result'][0]['firstname'] . 
                         " " . $contact['result'][0]['lastname'];

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
                $response->result->accountname = 
                                         $account['result'][0]['accountname'];

                $cache_value = json_encode($response->result);

                //Save userid and session id against customerportal 
                //credentials
                Yii::app()->cache->set($cache_key, $cache_value);
                $valueFrom = 'database';            
            } 
            
            $this->session = json_decode($cache_value);
            $this->session->challengeToken = $challengeToken;
            return true;
        } catch (Exception $e){
            $response = new stdClass();
            $response->success = false;
            $response->error->code = $this->errors[$e->getCode()];
            $response->error->message = $e->getMessage();
            if ($e->getCode() == 1003) {
		    if (isset($_SERVER['HTTP_X_TIMESTAMP']))
		        $response->error->time_difference = $_SERVER['REQUEST_TIME'] - 
		                              strtotime($_SERVER['HTTP_X_TIMESTAMP']);
                    $response->error->time_request_arrived = date("c",$_SERVER['REQUEST_TIME']);
		    $response->error->time_request_sent = date("c",strtotime($_SERVER['HTTP_X_TIMESTAMP']));
                    $response->error->time_server = date("c");
            }
		    $this->_sendResponse(403, json_encode($response));
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
                echo 'In case of invalid API Key and signature,';
                echo ' an account needs to setup in order to use ';
                echo 'this service. Please contact';
                echo '<a href="mailto://sales@gizur.com">sales@gizur.com</a>';
                echo 'in order to setup an account.';
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
                    $sessionId = $this->session->sessionName; 
                    $flipped_custom_fields = 
                                  array_flip($this->custom_fields['HelpDesk']);
                    if (in_array($_GET['fieldname'], 
                                                    $flipped_custom_fields)){
                        $fieldname = 
                    $this->custom_fields[$_GET['model']][$_GET['fieldname']];
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
                                            $this->custom_fields['HelpDesk'])){
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
                                $this->_sendResponse(200, json_encode(array(
                                    'success' => true, 
                                    //'challengeToken' => $this->session->challengeToken,
                                    'result' => 
                                               $field['type']['picklistValues']
                                    )));
                                break 2;
                            }
                            throw new Exception("Not an picklist field");
                        }
                    }
                    throw new Exception("Fieldname not found"); 
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

                    $where_clause[] = "parent_id = " . $contactId;
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
                            $where_clause[] = $this->custom_fields
                                                    ['HelpDesk']['trailerid'] . 
                                             " = '" . $_GET['trailerid'] . "'";
                    }
                       
                    $query = $query . " where " . 
                            implode(" and ", $where_clause) . ";";
                
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
                        throw new Exception('Fetching details failed');

                    $custom_fields = $this->custom_fields['HelpDesk'];
                    
                    foreach($response['result'] as &$troubleticket){
                        unset($troubleticket['update_log']);
                        unset($troubleticket['hours']);
                        unset($troubleticket['days']);
                        unset($troubleticket['modifiedtime']);
                        unset($troubleticket['from_portal']);
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
                $query = "select * from " . $_GET['model'] . 
                        " where account = " . $accountId . ";";

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
                $custom_fields = $this->custom_fields['Assets'];

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

                $response->error->code = "METHOD_NOT_ALLOWED";
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
		$dynamodb->set_region(AmazonDynamoDB::REGION_EU_W1); 
		$table_name = 'GIZUR_ACCOUNTS';

		// Get an item
		$ddb_response = $dynamodb->get_item(array(
		    'TableName' => $table_name,
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
                               
                $custom_fields = $this->custom_fields['HelpDesk'];

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

                $custom_fields = $this->custom_fields['Assets'];

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
                $bucket = Yii::app()->params->awsS3Bucket;
 
                $file_resource = fopen('protected/data/'. 
                        $response->result->filename,'x');
                $s3response = $s3->get_object($bucket, 
                        $response->result->filename, 
                        array(
                    'fileDownload' => $file_resource
                ));
               
                $response->result->filecontent = 
                        base64_encode(
                                file_get_contents('protected/data/' . 
                                        $response->result->filename));
                unlink('protected/data/' . $response->result->filename); 
 
                $filename_sanitizer = explode("_",
                                                 $response->result->filename);               
                unset($filename_sanitizer[0]);               
                $response->result->filename = implode('_', 
                                                        $filename_sanitizer); 
                $this->_sendResponse(200, json_encode($response)); 
                break;
            
            default :
                $response = new stdClass();
                $response->success = false;

                $response->error->code = "METHOD_NOT_ALLOWED";
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
                $dynamodb->set_region(AmazonDynamoDB::REGION_EU_W1); 
                $table_name = 'GIZUR_ACCOUNTS';
                $ddb_response = $dynamodb->put_item(array(
                    'TableName' => $table_name,
                    'Item' => $dynamodb->attributes($post)
                ));
                
                // Get an item
                $ddb_response = $dynamodb->get_item(array(
                    'TableName' => $table_name,
                    'Key' => $dynamodb->attributes(array(
                    'HashKeyElement'  => $post['id'],
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
                                             $this->custom_fields['HelpDesk']);
                
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
                Yii::trace("starting file block", "debug"); 
                if (!empty($_FILES) && $globalresponse->success){
                    foreach ($_FILES as $key => $file){
                        //$target_path = YiiBase::getPathOfAlias('application')
                        // . "/data/" . basename($file['name']);
                        //move_uploaded_file($file['tmp_name'], $target_path);
                        //Create document
                        $rest = new RESTClient();
                        $rest->format('json'); 
                        $dataJson['filename'] = $crmid . "_" . $file['name'];
                        $dataJson['filesize'] = $file['size'];
                        $dataJson['filetype'] = $file['type'];
                        $document = $rest->post(Yii::app()->params->vtRestUrl, 
                                array(
                                            'sessionName' => $sessionId,
                                            'operation' => 'create',
                                            'element' => 
                                                        json_encode($dataJson),
                                            'elementType' => 'Documents'
                                        ));
                        Yii::trace($document, "debug"); 
                        $document = json_decode($document);
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
                        
                        //Upload file to Amazon S3
                        $s3 = new AmazonS3();
                        
                        $response = $s3->create_object(
                                Yii::app()->params->awsS3Bucket, 
                                $crmid . '_' . $file['name'], 
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
                            $globalresponse->result->documents[]
                                    = $document->result;
                        } else {
                            $globalresponse->result->documents[]
                                     = 'not uploaded' . $file['name'];
                        }
                        
                    }
                }
                
                Yii::trace("Ending of FIle Upload to S3", "debug"); 

                $globalresponse = json_encode($globalresponse);
                $globalresponse = json_decode($globalresponse, true);
                
                $custom_fields = $this->custom_fields['HelpDesk'];

                
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
                $globalresponse['debug']['request_sent'] = $_SERVER['HTTP_X_TIMESTAMP']; 
                $globalresponse['debug']['request_arrived'] = date("c", $_SERVER['REQUEST_TIME']); 
                $globalresponse['debug']['script_ended'] = date("c"); 
                $globalresponse['debug']['script_started'] = $script_started; 

                $this->_sendResponse(200, json_encode($globalresponse));
                Yii::trace(json_encode($globalresponse), "debug"); 
                break;
            
            default :
                $response = new stdClass();
                $response->success = false;
                $response->error->code = "METHOD_NOT_ALLOWED";
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
            $response->error->code = "METHOD_NOT_ALLOWED";
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

                }

                if ($_GET['action'] == 'changepw') {
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
			$dynamodb->set_region(AmazonDynamoDB::REGION_EU_W1); 
			$table_name = 'GIZUR_ACCOUNTS';

			// Get an item
			$ddb_response = $dynamodb->get_item(array(
			    'TableName' => $table_name,
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


			/* Create the private and public key */
			$result['secretkey_' . $keyid] = uniqid("", true) . 
		                                               uniqid("", true);
			$result['apikey_' . $keyid] = strtoupper(uniqid("GZCLD" 
		                                                  . uniqid()));

		        $ddb_response = $dynamodb->put_item(array(
		            'TableName' => $table_name,
		            'Item' => $dynamodb->attributes($result)
		        ));


		        if ($response->success = $ddb_response->isOK())
		            $response->result = $result;
		        
		        $this->_sendResponse(200, json_encode($response));
		} else {
		        $post = json_decode(file_get_contents('php://input'),
                                                                         true);
		        
			// Instantiate the class
			$dynamodb = new AmazonDynamoDB();
			$dynamodb->set_region(AmazonDynamoDB::REGION_EU_W1); 
			$table_name = 'GIZUR_ACCOUNTS';
		        $ddb_response = $dynamodb->put_item(array(
		            'TableName' => $table_name,
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
                
                $custom_fields = $this->custom_fields['HelpDesk'];

                
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
            
            default :
                $response = new stdClass();
                $response->success = false;
                $response->error->code = "METHOD_NOT_ALLOWED";
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
}
