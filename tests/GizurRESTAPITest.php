<?php

/**
 * @version 0.1
 * @package gizur
 * @copyright &copy; gizur
 * @author Anshuk Kumar <anshuk-kumar@essindia.co.in>
 */

/**
 * Unit Test class for Testing the Gizur REST API ( wrapper over 
 * vtiger REST API )
 * Contains methods which test  
 * Login / authentication, view details of an asset, list category based
 * trouble tickets (Damage Report, Survey) and create a trouble ticket
 * 
 * Testing method:
 * > phpunit --verbrose Gizur_REST_API_Test
 */
require_once 'PHPUnit/Autoload.php';
require_once 'lib/RESTClient.php';

class Girur_REST_API_Test extends PHPUnit_Framework_TestCase
{

    Const GIZURCLOUD_SECRET_KEY  = "9b45e67513cb3377b0b18958c4de55be";
    Const GIZURCLOUD_API_KEY = "GZCLDFC4B35B";
    Const API_VERSION = "0.1";

    protected $url = "http://gizurtrailerapp-env.elasticbeanstalk.com/api/index.php/api/";
   
    public function testLogin()
    {
        $model = 'Authenticate';
        $action = 'login';
        
        //set credentials
        $credentials = Array(
            'user1' => 'password1',
            'user2' => 'password2',
            'user3' => 'password3',
            'user4' => 'password4',
            'cloud3@gizur.com' => 'rksh2jjf',
            'test@test.com' => '123456'
        );
        
        $valid_credentials = Array(
            'user1' => 'false',
            'user2' => 'false',
            'user3' => 'false',
            'user4' => 'false',
            'cloud3@gizur.com' => 'true',
            'test@test.com' => 'false'
        );        

        $params = array(
                    'Verb'          => 'POST',
                    'Model'	    => $model,
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => date("c"),
                    'KeyID'         => self::GIZURCLOUD_API_KEY
        );

        // Sorg arguments
        ksort($params);

        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";

        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', 
                    $string_to_sign, self::GIZURCLOUD_SECRET_KEY, 1));
        //echo json_encode(get_class_methods('PHPUnit_Framework_TestCase'));
        //login using each credentials
        foreach($credentials as $username => $password){
            
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            $response = $rest->post($this->url.$model."/".$action);
            $response = json_decode($response);
            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,$valid_credentials[$username], " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            unset($rest);
       }
    }
/*    
    public function testGetAssetList(){
        $model = 'Assets';

        //set credentials
        $credentials = Array(
            'anil-singh@essindia.co.in' => 'anil'
        );

        //login using each credentials
        foreach($credentials as $username => $password){
            
            //prepare request to be sent
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $this->url.$model."/");
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "X_USERNAME: $username",
                "X_PASSWORD: $password"
            ));
  
            //send request
            $response_json = curl_exec($ch);
            $response = json_decode($response_json);

            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,true);
            } else {
                 $this->assertObjectHasAttribute('success', $response);
            }
        }

	//close connection
	curl_close($ch);
    }
    /*
    public function testGetSurveyList(){
        $model = 'helpdesk';
        $category = 'survey';
        //set credentials
        $credentials = Array(
            'user1' => 'password1',
        );

        //login using each credentials
        foreach($credentials as $username => $password){
            
            //prepare request to be sent
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $this->url.$model."?category=$category");
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "X_USERNAME: $username",
                "X_PASSWORD: $password"
            ));
  
            //send request
            $response_json = curl_exec($ch);
            $response = json_decode($response_json);

            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,"true");
            } else {
                $this->assertFalse(TRUE);
            }
        }

	//close connection
	curl_close($ch);
    }
    
    public function testGetDamageReportList(){
        $model = 'helpdesk';
        $category = 'damagereport';
        //set credentials
        $credentials = Array(
            'user1' => 'password1',
        );

        //login using each credentials
        foreach($credentials as $username => $password){
            
            //prepare request to be sent
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $this->url.$model."?category=$category");
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "X_USERNAME: $username",
                "X_PASSWORD: $password"
            ));
  
            //send request
            $response_json = curl_exec($ch);
            $response = json_decode($response_json);

            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,"true");
            } else {
                $this->assertFalse(TRUE);
            }
        }

	//close connection
	curl_close($ch);
    }
    
    public function testCreateTroubleTicket(){
        $model = 'helpdesk';
        $category = 'damagereport';
        $fields_string = '';

        //set credentials
        $credentials = Array(
            'user1' => 'password1',
        );

        //set fields to to posted
	$fields = array(
		    'lname'=>urlencode('test'),
		    'fname'=>urlencode('test'),
		    'title'=>urlencode('test'),
		    'company'=>urlencode('test'),
		    'age'=>urlencode('test'),
		    'email'=>urlencode('test'),
		    'phone'=>urlencode('test')
		);

	//url-ify the data for the POST
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string,'&');

        //login using each credentials
        foreach($credentials as $username => $password){
            
            //prepare request to be sent
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $this->url.$model."?category=$category");
	    curl_setopt($ch, CURLOPT_POST, count($fields));
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "X_USERNAME: $username",
                "X_PASSWORD: $password"
            ));
  
            //send request
            $response_json = curl_exec($ch);
            $response = json_decode($response_json);

            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,"true");
            } else {
                $this->assertFalse(TRUE);
            }
        }

	//close connection
	curl_close($ch);
    }
    
    public function testCreateAndListTroubleTicketDamageReport(){
        $model = 'helpdesk';
        $category = 'damagereport';
        $fields_string = '';

        //set credentials
        $credentials = Array(
            'user1' => 'password1',
        );

        //set fields to to posted
	$fields = array('ticket_title'=>'Battery backup low4', 
            'cf_641'=>$category, //fieldname for Trouble Ticket Type
            'ticketstatus' => 'Open');

	//url-ify the data for the POST
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string,'&');

        //login using each credentials
        foreach($credentials as $username => $password){
            
            //prepare request to be sent
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $this->url.$model);
	    curl_setopt($ch, CURLOPT_POST, count($fields));
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "X_USERNAME: $username",
                "X_PASSWORD: $password"
            ));
  
            //send request
            $response_json = curl_exec($ch);
            $response = json_decode($response_json);

            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,"true");
                //check if newly created exist in list
                //prepare request to be sent
                $ch2 = curl_init(); 
                curl_setopt($ch2, CURLOPT_URL, $this->url.$model."/".$response->id);
                curl_setopt($ch2, CURLOPT_HTTPGET, 1);
                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
                    "X_USERNAME: $username",
                    "X_PASSWORD: $password"
                ));

                //send request
                $response_json_list = curl_exec($ch2);
                $response_list = json_decode($response_json);                
                if (isset($response_list->id)){
                    $this->assertEquals($response->id, $response_list->id);
                }else{
                    $this->assertFalse(TRUE);
                }
            } else {
                $this->assertFalse(TRUE);
            }

        }

	//close connection
	curl_close($ch);
    }
    
    public function testCreateAndListTroubleTicketSurvey(){
        $model = 'helpdesk';
        $category = 'damagereport';
        $fields_string = '';

        //set credentials
        $credentials = Array(
            'user1' => 'password1',
        );

        //set fields to to posted
	$fields = array('ticket_title'=>'Battery backup low Survey', 
            'cf_641'=>$category, //fieldname for Trouble Ticket Type
            'ticketstatus' => 'Open');

	//url-ify the data for the POST
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string,'&');

        //login using each credentials
        foreach($credentials as $username => $password){
            
            //prepare request to be sent
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $this->url.$model);
	    curl_setopt($ch, CURLOPT_POST, count($fields));
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "X_USERNAME: $username",
                "X_PASSWORD: $password"
            ));
  
            //send request
            $response_json = curl_exec($ch);
            $response = json_decode($response_json);

            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,"true");
                //check if newly created exist in list
                //prepare request to be sent
                $ch2 = curl_init(); 
                curl_setopt($ch2, CURLOPT_URL, $this->url.$model."/".$response->id);
                curl_setopt($ch2, CURLOPT_HTTPGET, 1);
                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
                    "X_USERNAME: $username",
                    "X_PASSWORD: $password"
                ));

                //send request
                $response_json_list = curl_exec($ch2);
                $response_list = json_decode($response_json);                
                if (isset($response_list->id)){
                    $this->assertEquals($response->id, $response_list->id);
                }else{
                    $this->assertFalse(TRUE);
                }
            } else {
                $this->assertFalse(TRUE);
            }

        }

	//close connection
	curl_close($ch);
    }   
    
    public function testGetPicklist(){
        $model = 'picklist';
        $module = 'helpdesk';
        $fieldname = 'ticketpriorities';
        
        //set credentials
        $credentials = Array(
            'user1' => 'password1',
        );

        //login using each credentials
        foreach($credentials as $username => $password){
            
            //prepare request to be sent
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $this->url.$model."?fieldname=$fieldname&module=$module");
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "X_USERNAME: $username",
                "X_PASSWORD: $password"
            ));
  
            //send request
            $response_json = curl_exec($ch);
            $response = json_decode($response_json, true);

            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,"true");
            } else {
                $this->assertFalse(TRUE);
            }
        }

	//close connection
	curl_close($ch);
    }    */
}
?>

