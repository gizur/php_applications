<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class ApiController extends Controller {
    // Members
    /**
     * Key which has to be in HTTP USERNAME and PASSWORD headers 
     */

    Const VT_REST_URL = "http://localhost/vtigercrm/webservice.php";

    /**
     * Default response format
     * either 'json' or 'xml'
     */
    private $format = 'json';



    /**
     * @return array action filters
     */
    public function filters() {
        return array();
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
                    //Get $customerportal_username and $customerportal_password 
                    //from header
                    $customerportal_username = $_SERVER['HTTP_X_USERNAME'];
                    $customerportal_password = $_SERVER['HTTP_X_PASSWORD'];

                    //Get the Access Key and the Username from vtiger REST 
                    //service of the customer portal user's vtiger account
                    $rest = new RESTClient();
                    $rest->format('json');
                    
                    $rest->set_header('Content-Type', 
                            'application/x-www-form-urlencoded');
                    $response = $rest->post(self::VT_REST_URL.
                            "?operation=logincustomer", array(
                        'username'=>$customerportal_username,
                        'password'=>$customerportal_password
                    ));
                    $response = json_decode($response);
                    if ($response->success==false)
                        throw new Exception("Invalid Username and Password");
                    $username = $response->result->user_name;
                    $userAccessKey = $response->result->accesskey;
                    $accountId = $response->result->accountId;
                    
                    //Login using $username and $userAccessKey
                    $response = $rest->get(self::VT_REST_URL.
                            "?operation=getchallenge&username=$username");
                    $response = json_decode($response);
                    if ($response->success==false)
                        throw new Exception("Unable to get challenge token");                    
                    $challengeToken = $response->result->token;
                    $generatedKey = md5($challengeToken.$userAccessKey);
                    
                    $response = $rest->post(self::VT_REST_URL."?operation=login", 
                            array(
                                'username'=> $username, 
                                'accessKey' => $generatedKey
                                ));
                    $response = json_decode($response); 
                    if ($response->success==false)
                        throw new Exception("Invalid generated key");                    
                    $cache_key = json_encode(array(
                        'username'=>$customerportal_username,
                        'password'=>$customerportal_password
                    ));
                    $response->result->accountId = $accountId;
                    $cache_value = json_encode($response->result);

                    //Save userid and session id against customerportal 
                    //credentials
                    Yii::app()->cache->set($cache_key, $cache_value);
                    
                    //Return response to client
                    $response = new stdClass();
                    $response->success = "true";
                    echo json_encode($response);
                }
                
                if ($_GET['action'] == 'logout') {
                    //Get $customerportal_username and $customerportal_password 
                    //from header
                    $customerportal_username = $_SERVER['HTTP_X_USERNAME'];
                    $customerportal_password = $_SERVER['HTTP_X_PASSWORD']; 

                    //Get the Session ID from cache
                    $cache_key = json_encode(array(
                        'username'=>$customerportal_username,
                        'password'=>$customerportal_password
                    ));                    
                    $sessioninfo = json_decode(Yii::app()->cache->get($cache_key));
                    $sessionId = $sessioninfo->sessionName;
                    
                    //Logout using $sessionId
                    $rest = new RESTClient();
                    $rest->format('json');                    
                    $response = $rest->get(self::VT_REST_URL.
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
             ** Accepts fieldnames and categories (survey|damagereport)
             *******************************************************************
             *******************************************************************
             */                
            case 'HelpDesk':
                //Is this a request for picklist?
                if (isset($_GET['fieldname'])){
                    //Get $customerportal_username and $customerportal_password 
                    //from header
                    $customerportal_username = $_SERVER['HTTP_X_USERNAME'];
                    $customerportal_password = $_SERVER['HTTP_X_PASSWORD'];  
                    
                    //Get the Session ID from cache
                    $cache_key = json_encode(array(
                        'username'=>$customerportal_username,
                        'password'=>$customerportal_password
                    ));                     
                    $sessioninfo = json_decode(Yii::app()->cache->get($cache_key));
                    $sessionId = $sessioninfo->sessionName; 
                    
                    //Receive response from vtiger REST service
                    //Return response to client 
                    
                    $params = "sessionName=$sessionId&operation=describe&elementType=" . $_GET['model'];                    
                    
                    $rest = new RESTClient();
                    $rest->format('json');                    
                    $response = $rest->get(self::VT_REST_URL."?$params"); 
                    
                    $response = json_decode($response, true);
                    //print_r($response);die;
                    foreach ($response['result']['fields'] as $field){
                        if ($_GET['fieldname'] == $field['name']) {
                            if ($field['type']['name'] == 'picklist'){
                                echo json_encode(array('success' => true, 'result' => $field['type']['picklistValues']));
                                break 2;
                            }
                            throw new Exception("Not an picklist field");
                        }
                    }
                    throw new Exception("Fieldname not found"); 
                } 
                
                //Is this a request for listing categories
                if (isset($_GET['category'])) {
                    //Get $customerportal_username and $customerportal_password 
                    //from header
                    $customerportal_username = $_SERVER['HTTP_X_USERNAME'];
                    $customerportal_password = $_SERVER['HTTP_X_PASSWORD']; 

                    //Get the Session ID from cache
                    $cache_key = json_encode(array(
                        'username'=>$customerportal_username,
                        'password'=>$customerportal_password
                    ));                     
                    $sessioninfo = json_decode(Yii::app()->cache->get($cache_key));
                    $sessionId = $sessioninfo->sessionName;
                    $accountId = $sessioninfo->accountId;

                    //Send request to vtiger REST service
                    //cf_633 => Trouble Ticket Type
                    $query = "select * from " . $_GET['model'] . 
                            " where cf_633 = '" . $_GET['category'] . "'" .
                            " and parent_id = " . $accountId . ";";
                    
                    //urlencode to as its sent over http.
                    $queryParam = urlencode($query);
                    
                    //creating query string
                    $params = "sessionName=$sessionId&operation=query&query=$queryParam";

                    //Receive response from vtiger REST service
                    //Return response to client  
                    $rest = new RESTClient();
                    $rest->format('json');                    
                    echo $response = $rest->get(self::VT_REST_URL."?$params");
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
                //Get $customerportal_username and $customerportal_password 
                //from header
                $customerportal_username = $_SERVER['HTTP_X_USERNAME'];
                $customerportal_password = $_SERVER['HTTP_X_PASSWORD']; 

                //Get the Session ID from cache
                $cache_key = json_encode(array(
                    'username'=>$customerportal_username,
                    'password'=>$customerportal_password
                ));                     
                $sessioninfo = json_decode(Yii::app()->cache->get($cache_key));
                $sessionId = $sessioninfo->sessionName;
                $accountId = $sessioninfo->accountId;
                
                //Send request to vtiger REST service
                //cf_633 => Trouble Ticket Type
                $query = "select * from " . $_GET['model'] . 
                        " where account = " . $accountId . ";";

                //urlencode to as its sent over http.
                $queryParam = urlencode($query);

                //creating query string
                $params = "sessionName=$sessionId&operation=query&query=$queryParam";

                //Receive response from vtiger REST service
                //Return response to client  
                $rest = new RESTClient();
                $rest->format('json');                    
                echo $response = $rest->get(self::VT_REST_URL."?$params");               
                break;      
            
            default :
                $response = new stdClass();
                $response->success = "false";
                $response->error->code = "ACCESS_DENIED";
                $response->error->message = "Permission to perform the operation is denied for " . $_GET['model'];
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
            /*
             *******************************************************************
             *******************************************************************
             ** Assets MODEL
             ** Accepts id
             *******************************************************************
             *******************************************************************
             */             
            case 'Assets':
                //Get $customerportal_username and $customerportal_password 
                //from header
                $customerportal_username = $_SERVER['HTTP_X_USERNAME'];
                $customerportal_password = $_SERVER['HTTP_X_PASSWORD']; 

                //Get the Session ID from cache
                $cache_key = json_encode(array(
                    'username'=>$customerportal_username,
                    'password'=>$customerportal_password
                ));                     
                $sessioninfo = json_decode(Yii::app()->cache->get($cache_key));
                $sessionId = $sessioninfo->sessionName;
                
                //Send request to vtiger REST service
                //cf_633 => Trouble Ticket Type
                $query = "select * from " . $_GET['model'] . 
                        " where id = " . $_GET['id'] . ";";

                //urlencode to as its sent over http.
                $queryParam = urlencode($query);

                //creating query string
                $params = "sessionName=$sessionId&operation=query&query=$queryParam";

                //Receive response from vtiger REST service
                //Return response to client  
                $rest = new RESTClient();
                $rest->format('json');                    
                echo $response = $rest->get(self::VT_REST_URL."?$params");               
                break;
            
            default :
                $response = new stdClass();
                $response->success = "false";
                $response->error->code = "ACCESS_DENIED";
                $response->error->message = "Permission to perform the operation is denied for " . $_GET['model'];
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
                //Get $customerportal_username and $customerportal_password 
                //from header
                $customerportal_username = $_SERVER['HTTP_X_USERNAME'];
                $customerportal_password = $_SERVER['HTTP_X_PASSWORD']; 

                //Get the Session ID from cache
                $cache_key = json_encode(array(
                    'username'=>$customerportal_username,
                    'password'=>$customerportal_password
                ));                     
                $sessioninfo = json_decode(Yii::app()->cache->get($cache_key));
                $sessionId = $sessioninfo->sessionName;
                
                //get data json 
                $dataJson = json_encode($_POST+array('assigned_user_id' => $sessioninfo->userId, 'ticketstatus' => 'Open'));

                //creating query string
                $params = "sessionName=$sessionId&operation=create&element=$dataJson&elementType=" . $_GET['model'];

                //Receive response from vtiger REST service
                //Return response to client  
                $rest = new RESTClient();
                $rest->format('json');                    
                echo $response = $rest->post(self::VT_REST_URL, array(
                    'sessionName' => $sessionId,
                    'operation' => 'create',
                    'element' => $dataJson,
                    'elementType' => $_GET['model']
                ));               
                break;
            
            default :
                $response = new stdClass();
                $response->success = "false";
                $response->error->code = "ACCESS_DENIED";
                $response->error->message = "Permission to perform the operation is denied for " . $_GET['model'];
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
        $response->error->message = "Permission to perform the operation is denied for " . $_GET['model'];
        echo json_encode($response);
    }  
    
    public function actionUpdate() {
        $response = new stdClass();
        $response->success = "false";
        $response->error->code = "ACCESS_DENIED";
        $response->error->message = "Permission to perform the operation is denied for " . $_GET['model'];
        echo json_encode($response);
    }     
}