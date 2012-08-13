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

class Girur_REST_API_Test extends PHPUnit_Framework_TestCase
{
    protected $url = "http://localhost/gizurrest/index.php/api/";
   
    public function testLogin()
    {
        $model = 'login';
        
        //set credentials
        $credentials = Array(
            'user1' => 'password1',
            'user2' => 'password2',
            'user3' => 'password3',
            'user4' => 'password4'
        );

        //login using each credentials
        foreach($credentials as $username => $password){
            
            //prepare request to be sent
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $this->url.$model);
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
    
    public function testGetAssetList(){
        $model = 'asset';
        $id = 12;
        //set credentials
        $credentials = Array(
            'user1' => 'password1',
        );

        //login using each credentials
        foreach($credentials as $username => $password){
            
            //prepare request to be sent
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $this->url.$model."/".$id);
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
    }    
}
?>

