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
 * Including Amazon s3 classes
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
     * Default response format
     * either 'json' or 'xml'
     */
    
    private $session;    
    
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
            'damageposition' => 'cf_649'
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

    /**
     * @returs wether any action should run
     */
    
    public function beforeAction() {
        
        try {
            
            //check if public key exists
            if (!isset($_SERVER['HTTP_X_GIZURCLOUD_API_KEY']))
                throw new Exception('Public Key Not Found in request');
            
            // Retreive Key pair from Amazon Dynamodb
            $GIZURCLOUD_SECRET_KEY  = "9b45e67513cb3377b0b18958c4de55be";
            $GIZURCLOUD_API_KEY = "GZCLDFC4B35B";            
            
            if ($_SERVER['HTTP_X_GIZURCLOUD_API_KEY'] != $GIZURCLOUD_API_KEY) 
                throw new Exception('Could not identify public key');
            
            if (!isset($_SERVER['HTTP_X_TIMESTAMP']))
                throw new Exception('Timestamp not found in request');
            else
                $timestamp = $_SERVER['HTTP_X_TIMESTAMP'];
            
            if (!isset($_SERVER['HTTP_X_SIGNATURE']))
                throw new Exception('Signature not found');
            else
                $signature = $_SERVER['HTTP_X_SIGNATURE'];

                // Build query arguments list
            $params = array(
                    'Verb'          => Yii::App()->request->getRequestType(),
                    'Model'	    => $_GET['model'],
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
                    throw new Exception("Invalid generated key");                    

                $response->result->accountId = $accountId;
                $response->result->contactId = $contactId;

                $cache_value = json_encode($response->result);

                //Save userid and session id against customerportal 
                //credentials
                Yii::app()->cache->set($cache_key, $cache_value);            
            } 
            
            $this->session = json_decode($cache_value);
            
            return true;
        } catch (Exception $e){
            $response = new stdClass();
            $response->success = "false";
            $response->error->code = "ERROR";
            $response->error->message = $e->getMessage();
            
            echo json_encode($response);
            
            return false;
        }
    }    
    
    // Actions
    public function actionList() {
        //Tasks include Listing of Troubleticket, Picklists, Assets
        try {
        switch($_GET['model']) {
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
                    $response->success = "true";
                    echo json_encode($response);
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
                    $response->success = "true";
                    echo json_encode($response);                    
                }
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
                    if (in_array($_GET['fieldname'],array_flip($this->custom_fields['HelpDesk']))){
                        $fieldname = $this->custom_fields[$_GET['fieldname']];
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
                    
                    foreach ($response['result']['fields'] as $field){
                        if ($fieldname == $field['name']) {
                            if ($field['type']['name'] == 'picklist'){
                                echo json_encode(array(
                                    'success' => true, 
                                    'result' => $field['type']['picklistValues']
                                    ));
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
                    //cf_633 => Trouble Ticket Type
                    $query = "select * from " . $_GET['model']; 

                    //creating where clause based on parameters
                    $where_clause = Array();
                    if ($_GET['category']=='inoperation') {
                        $where_clause[] = "ticketstatus = 'Close'";
                    }
                    if ($_GET['category']=='damaged') {
                        $where_clause[] = "ticketstatus = 'Open'";
                    }

                    $where_clause[] = "parent_id = " . $contactId;
                    if (isset($_GET['year']) && isset($_GET['month'])) {
                        $where_clause[] = "created >= " . 
                                $_GET['year'] . "-" . $_GET['month'] . "-01";
                        $where_clause[] = "created >= " . 
                                $_GET['year'] . "-" . $_GET['month'] . "-31";
                    }
                    if (isset($_GET['trailerid'])){
                        $where_clause[] = $this->custom_fields['trailerid'] . 
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
                    $custom_fields = $this->custom_fields['HelpDesk'];
                    
                    foreach($response['result'] as &$troubleticket){
                        unset($troubleticket['update_log']);
                        unset($troubleticket['hours']);
                        unset($troubleticket['days']);
                        unset($troubleticket['modifiedtime']);
                        unset($troubleticket['from_portal']);
                        foreach($troubleticket as $fieldname => $value){
                            $key_to_replace = array_search($fieldname, $custom_fields);
                            if ($key_to_replace) {
                               unset($troubleticket[$fieldname]);
                               $troubleticket[$key_to_replace] = $value;
                               //unset($custom_fields[$key_to_replace]);                                
                            }
                        }
                    }
                    echo json_encode($response);
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
                //cf_633 => Trouble Ticket Type
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
                        $key_to_replace = array_search($fieldname, $custom_fields);
                        if ($key_to_replace) {
                            unset($asset[$fieldname]);
                            $asset[$key_to_replace] = $value;
                            //unset($custom_fields[$key_to_replace]);                                
                        }
                    }
                }
                echo json_encode($response);
                break;                  
            
            default :
                $response = new stdClass();
                $response->success = "false";
                $response->error->code = "ACCESS_DENIED";
                $response->error->message = "Permission to perform the" . 
                        " operation is denied for " . $_GET['model'];
                echo json_encode($response);
                break;
        }
        } catch (Exception $e) {
                $response = new stdClass();
                $response->success = "false";
                $response->error->code = "ERROR";
                $response->error->message = $e->getMessage();
                echo json_encode($response);
        }
    }

    public function actionView() {
        //Tasks include detail view of a specific Troubleticket and Assets
        try {
        switch($_GET['model']) {
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
                
                //Send request to vtiger REST service
                //cf_633 => Trouble Ticket Type
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
                
                //Get Documents Ids

                //urlencode to as its sent over http.
                $queryParam = urlencode($query);

                //creating query string
                $params = "sessionName=$sessionId" . 
                        "&operation=getrelatedtroubleticketdocument" . 
                        "&crmid=" . $_GET['id'];

                //Receive response from vtiger REST service
                //Return response to client  
                $rest = new RESTClient();
                $rest->format('json');                    
                $documentids = $rest->get(Yii::app()->params->vtRestUrl . 
                        "?$params"); 
                $documentids = json_decode($documentids, true);
                $documentids = $documentids['result'];
                
                //--to do-- get contact details
                $response = json_decode($response);
                //Get Documents
                if (count($documentids)!=0) {
                    $query = "select * from Documents" . 
                            " where id in (7x" . 
                            implode(", 7x", $documentids) . ");";

                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);

                    //creating query string
                    $params = "sessionName=$sessionId" . 
                            "&operation=query&query=$queryParam";

                    //Receive response from vtiger REST service
                    //Return response to client  
                    $rest = new RESTClient();
                    $rest->format('json');                    
                    $documents = $rest->get(Yii::app()->params->vtRestUrl . 
                            "?$params");
                    $documents = json_decode($documents, true);

                    
                    $response->result[0]->documents = $documents['result'];
                }
                
                $response = json_encode($response);
                $response = json_decode($response, true);
                $response['result'] = $response['result'][0]; 
                
                $custom_fields = $this->custom_fields['HelpDesk'];

                unset($response['result']['update_log']);
                unset($response['result']['hours']);
                unset($response['result']['days']);
                unset($response['result']['modifiedtime']);
                unset($response['result']['from_portal']);
                foreach($response['result'] as $fieldname => $value){
                    $key_to_replace = array_search($fieldname, $custom_fields);
                    if ($key_to_replace) {
                        unset($response['result'][$fieldname]);
                        $response['result'][$key_to_replace] = $value;
                        //unset($custom_fields[$key_to_replace]);                                
                    }
                }                                
                
                echo json_encode($response);
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
                //cf_633 => Trouble Ticket Type
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
                    $key_to_replace = array_search($fieldname, $custom_fields);
                    if ($key_to_replace) {
                        unset($response['result'][$fieldname]);
                        $response['result'][$key_to_replace] = $value;
                        //unset($custom_fields[$key_to_replace]);                                
                    }
                }                                
                
                echo json_encode($response);
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
 
                $filename_sanitizer = explode("_",$response->result->filename);               
                unset($filename_sanitizer[0]);               
                $response->result->filename = implode('_', $filename_sanitizer); 
                echo json_encode($response); 
                break;
            
            default :
                $response = new stdClass();
                $response->success = "false";
                $response->error->code = "ACCESS_DENIED";
                $response->error->message = "Permission to perform the" . 
                        " operation is denied for " . $_GET['model'];
                echo json_encode($response);
                break;            
        }
        } catch (Exception $e) {
                $response = new stdClass();
                $response->success = "false";
                $response->error->code = "ERROR";
                $response->error->message = $e->getMessage();
                echo json_encode($response);            
        }
    }

    public function actionCreate() {
        //Tasks include detail view of a specific Troubleticket and Assets
        try {
        switch($_GET['model']) {
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
                $userId = $this->session->userId;
                           
                /** Creating Touble Ticket**/
                $post = $_POST;
                $custom_fields = array_flip($this->custom_fields['HelpDesk']);
                
                foreach ($post as $k => $v) {
                    $key_to_replace = array_search($k, $custom_fields);
                    if ($key_to_replace){
                        unset ($post[$k]);
                        $post[$custom_fields[$k]] = $v;
                    }
                }
                //get data json 
                $dataJson = json_encode(array_merge(
                        $post,
                        array(
                            'parent_id' => $this->session->contactId,
                            'assigned_user_id' => $this->session->userId,
                            'ticketstatus' => 'Closed'
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
                $globalresponse->result->file = Array();
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
                        //$target_path = YiiBase::getPathOfAlias('application')
                        // . "/data/" . basename($file['name']);
                        //move_uploaded_file($file['tmp_name'], $target_path);
                        
                        //Create document
                        $rest = new RESTClient();
                        $rest->format('json'); 
                        $dataJson['filename'] = $crmid . "_" . $file['name'];
                        $dataJson['filesize'] = $file['size'];
                        $dataJson['filetype'] = $file['type'];
                        $response = $rest->post(Yii::app()->params->vtRestUrl, 
                                array(
                                            'sessionName' => $sessionId,
                                            'operation' => 'create',
                                            'element' => json_encode($dataJson),
                                            'elementType' => 'Documents'
                                        ));
                        
                        $response = json_decode($response);
                        $notesid = $response->result->id;
                        
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
                            'storage' => AmazonS3::STORAGE_REDUCED,
                            'headers' => array(
                                'Cache-Control'    => 'max-age',
                                'Content-Encoding' => 'gzip',
                                'Content-Language' => 'en-US',
                                'Expires'          => 
                                'Thu, 01 Dec 1994 16:00:00 GMT',
                            )
                        ));                        
                        
                        if ($response->isOK()) {
                            $globalresponse->result->file[$file['name']] = 
                                    'uploaded';
                        } else {
                            $globalresponse->result->file[$file['name']] = 
                                    'not uploaded';
                        }
                        
                    }
                }

                $globalresponse = json_encode($globalresponse);
                $globalresponse = json_decode($globalresponse, true);
                
                $custom_fields = $this->custom_fields['HelpDesk'];

                
                unset($globalresponse['result']['update_log']);
                unset($globalresponse['result']['hours']);
                unset($globalresponse['result']['days']);
                unset($globalresponse['result']['modifiedtime']);
                unset($globalresponse['result']['from_portal']);
                foreach($globalresponse['result'] as $fieldname => $value){
                    $key_to_replace = array_search($fieldname, $custom_fields);
                    if ($key_to_replace) {
                        unset($globalresponse['result'][$fieldname]);
                        $globalresponse['result'][$key_to_replace] = $value;
                        //unset($custom_fields[$key_to_replace]);                                
                    }
                }
                
                echo json_encode($globalresponse);
                break;
            
            default :
                $response = new stdClass();
                $response->success = "false";
                $response->error->code = "ACCESS_DENIED";
                $response->error->message = "Permission to perform the" . 
                        "operation is denied for " . $_GET['model'];
                echo json_encode($response);
                break;            
        }
        } catch (Exception $e) {
                $response = new stdClass();
                $response->success = "false";
                $response->error->code = "ERROR";
                $response->error->message = $e->getMessage();
                echo json_encode($response);            
        }
    }
    
    public function actionDelete() {
        $response = new stdClass();
        $response->success = "false";
        $response->error->code = "ACCESS_DENIED";
        $response->error->message = "Permission to perform the" . 
                " operation is denied for " . $_GET['model'];
        echo json_encode($response);
    }  
    
    public function actionUpdate() {
        //Tasks include detail updating Troubleticket
        try {
        switch($_GET['model']) {
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
                    $key_to_replace = array_search($fieldname, $custom_fields);
                    if ($key_to_replace) {
                        unset($response['result'][$fieldname]);
                        $response['result'][$key_to_replace] = $value;
                        //unset($custom_fields[$key_to_replace]);                                
                    }
                }
                
                echo json_encode($response);                
                
                break;
            
            default :
                $response = new stdClass();
                $response->success = "false";
                $response->error->code = "ACCESS_DENIED";
                $response->error->message = "Permission to perform the" . 
                        " operation is denied for " . $_GET['model'];
                echo json_encode($response);
                break;            
        }
        } catch (Exception $e) {
                $response = new stdClass();
                $response->success = "false";
                $response->error->code = "ERROR";
                $response->error->message = $e->getMessage();
                echo json_encode($response);            
        }
    }     
}
