<?php

/**
 * @version 0.2
 * @package gizur
 * @copyright &copy; gizur
 * @author Anshuk Kumar <anshuk-kumar@essindia.co.in>
 */

/**
 * Unit Test class for Testing the Gizur REST API ( wrapper over 
 * vtiger REST API )
 * Contains methods which test  
 * Login / authentication, view details of an asset, list category based
 * trouble tickets and create a trouble ticket
 * 
 * Testing method:
 * > phpunit --verbrose Gizur_REST_API_Test
 */
require_once 'PHPUnit/Autoload.php';
require_once 'lib/RESTClient.php';
require_once('../lib/aws-php-sdk/sdk.class.php');

class Girur_REST_API_Test extends PHPUnit_Framework_TestCase
{
    //Gizur Cloud 1
    //Const GIZURCLOUD_SECRET_KEY  = "50694086b18cd0.9497426050694086b18fa8.66729980";
    //Const GIZURCLOUD_API_KEY = "GZCLD50694086B196F50694086B19E7";

    //Gizur Cloud 2
    //Const GIZURCLOUD_SECRET_KEY = "50826a54755009.5822592450826a54755292.56509362";
    //Const GIZURCLOUD_API_KEY = "GZCLD50826A54755AB50826A5475624";

    //Gizur Cloud 3
    Const GIZURCLOUD_SECRET_KEY = "9b45e67513cb3377b0b18958c4de55be";
    Const GIZURCLOUD_API_KEY = "GZCLDFC4B35B";

    Const API_VERSION = "0.1";

    protected $credentials = Array(
            //Gizur Cloud 3
            'portal_user@gizur.com' => 'skcx0r0i',
            //'mobile_user@gizur.com' => 'ivry34aq',
            //Change Password User 
            //'anshuk.kumar@essindia.co.in' => 'ipjibl0f',
            //'anshuk.kumar@essindia.co.in' => 'dddddd',
          
            //Gizur Cloud 2 
            //'portal_user@gizur.com' => '2hxrftmd',
            //'mobile_app@gizur.com' => 'ivry34aq',
            
            //Gizur Cloud 1
            //'mobile_app@gizur.com' => 'cwvvzvb0',
    );

    //Cloud 1 
    //protected $url = "https://api.gizur.com/api/index.php/api/";

    //Cloud 2
    //protected $url = "http://phpapplications3-env-tk3itzr6av.elasticbeanstalk.com/api/index.php/api/";
    //protected $url = "https://c2.gizur.com/api/index.php/api/";
    
    //Cloud 3
    protected $url = "http://phpapplications-env-sixmtjkbzs.elasticbeanstalk.com/api/index.php/api/";
    //protected $url = "http://gizurtrailerapp-env.elasticbeanstalk.com/api/index.php/api/";
    
    //Dev
    //protected $url = "http://localhost/gizurcloud/api/index.php/api/";
 

    public function testLogin()
    {
        $model = 'Authenticate';
        $action = 'login';
        $delta = 0;
        echo " Authenticating Login " . PHP_EOL;        
  
        //set credentials
        $this->credentials += Array(
            'user1' => 'password1',
            'user2' => 'password2',
            'user3' => 'password3',
            'user4' => 'password4',
            'test@test.com' => '123456'
        );
        
        $valid_credentials = Array(
            'user1' => false,
            'user2' => false,
            'user3' => false,
            'user4' => false,
            'cloud3@gizur.com' => true,
            'test@test.com' => false,
            'anil-singh@essindia.co.in' => true,
            'mobile_app@gizur.com' => true,
            'mobile_user@gizur.com' => true,
            'portal_user@gizur.com' => true
        );        
        generateSignature:
        $params = array(
                    'Verb'          => 'POST',
                    'Model'         => $model,
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => date("c", strtotime("+2 minutes") + $delta),
                    'KeyID'         => self::GIZURCLOUD_API_KEY,
        );

        //login using each credentials
        foreach($this->credentials as $username => $password){            
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->ssl(false);
            $rest->language(array('en-us;q=0.5','sv'));
            //if (!isset($params['UniqueSalt'])) 
                $params['UniqueSalt'] = uniqid();
            // Sorg arguments
            ksort($params);

            // Generate string for sign
            $string_to_sign = "";
            foreach ($params as $k => $v)
                $string_to_sign .= "{$k}{$v}";

            // Generate signature
            $signature = base64_encode(hash_hmac('SHA256', 
                        $string_to_sign, self::GIZURCLOUD_SECRET_KEY, 1));
            echo PHP_EOL . $string_to_sign;
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
            echo PHP_EOL . $response = $rest->post($this->url.$model."/".$action);
            $response = json_decode($response);
            if ($response->success == false) {
                if ($delta == 0) {
                    if ($response->error->code == 'TIME_NOT_IN_SYNC') {
                        $delta = $response->error->time_difference;
                        goto generateSignature;
                    } 
                } else {
                    echo PHP_EOL . " Delta Used " . $delta;
                }
            }

            //check if response is valid
            if (isset($response->success)){
                //echo json_encode($response) . PHP_EOL;
                $this->assertEquals($response->success,$valid_credentials[$username], " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            unset($rest);
       }
       echo PHP_EOL . PHP_EOL;
    }

    public function testLogout()
    {
        $model = 'Authenticate';
        $action = 'logout';
           
        echo " Authenticating Logout " . PHP_EOL;        

        $params = array(
                    'Verb'          => 'POST',
                    'Model'         => $model,
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => date("c"),
                    'KeyID'         => self::GIZURCLOUD_API_KEY,
                    'UniqueSalt'    => uniqid()
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
        //login using each credentials
        foreach($this->credentials as $username => $password){            
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
            echo PHP_EOL . "  " . $response = $rest->post($this->url.$model."/".$action);
            $response = json_decode($response);
            //check if response is valid
            if (isset($response->success)){
                //echo json_encode($response) . PHP_EOL;
                $this->assertEquals($response->success, true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            unset($rest);
       }
       echo PHP_EOL . PHP_EOL;
    }

    public function testAbout()
    {
        $model = 'About';
           
        echo " Fetching About " . PHP_EOL;        

        $params = array(
                    'Verb'          => 'GET',
                    'Model'         => $model,
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => date("c"),
                    'KeyID'         => self::GIZURCLOUD_API_KEY,
                    'UniqueSalt'    => uniqid()
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
        //login using each credentials
        $rest = new RESTClient();
        $rest->format('html'); 
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_SIGNATURE', $signature);                   
        $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        
        echo PHP_EOL . " Response :  " . $response = $rest->get($this->url.$model);
        
       unset($rest);
       echo PHP_EOL . PHP_EOL;
    }

    public function testChangePassword()
    {
        $this->markTestSkipped('');        
        $model = 'Authenticate';
        $action = 'changepw';
        //$newpassword = 'dddddd';
        //$newpassword = 'ipjibl0f';
           
        echo "Change Password " . PHP_EOL;        

        $params = array(
                    'Verb'          => 'PUT',
                    'Model'         => $model,
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => date("c"),
                    'KeyID'         => self::GIZURCLOUD_API_KEY,
                    'UniqueSalt'    => uniqid(),
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

        //login using each credentials
        foreach($this->credentials as $username => $password){            
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                  
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            echo PHP_EOL . "  " . $response = $rest->put($this->url.$model."/".$action, array('newpassword' => $newpassword));
            $response = json_decode($response);
            //check if response is valid
            if (isset($response->success)){
                //echo json_encode($response) . PHP_EOL;
                $this->assertEquals($response->success, true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            unset($rest);
       }
       echo PHP_EOL . PHP_EOL;
    }

    public function testChangeAssetStatus()
    {
        $model = 'Assets';
        $id = '28x8';
           
        echo "Changing Asset Status" . PHP_EOL;        


        //login using each credentials
        foreach($this->credentials as $username => $password){            
            $params = array(
                        'Verb'          => 'PUT',
                        'Model'         => $model,
                        'Version'       => self::API_VERSION,
                        'Timestamp'     => date("c"),
                        'KeyID'         => self::GIZURCLOUD_API_KEY,
                        'UniqueSalt'    => uniqid(),
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
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                  
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            echo PHP_EOL . "  " . $response = $rest->put($this->url.$model."/".$id, array('assetstatus' => 'In Service'));
            $response = json_decode($response);
            //check if response is valid
            if (isset($response->success)){
                //echo json_encode($response) . PHP_EOL;
                $this->assertEquals($response->result->assetstatus, 'In Service', " Checking validity of response");
                $this->assertEquals($response->success, true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            
            unset($rest);
            
            $params = array(
                        'Verb'          => 'PUT',
                        'Model'         => $model,
                        'Version'       => self::API_VERSION,
                        'Timestamp'     => date("c"),
                        'KeyID'         => self::GIZURCLOUD_API_KEY,
                        'UniqueSalt'    => uniqid(),
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
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                  
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            echo PHP_EOL . "  " . $response = $rest->put($this->url.$model."/".$id, array('assetstatus' => 'Out-of-service'));
            $response = json_decode($response);
            //check if response is valid
            if (isset($response->success)){
                //echo json_encode($response) . PHP_EOL;
                $this->assertEquals($response->result->assetstatus, 'Out-of-service', " Checking validity of response");
                $this->assertEquals($response->success, true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            unset($rest);
        }
        echo PHP_EOL . PHP_EOL;
    }


    public function testResetPassword()
    {
        $model = 'Authenticate';
        $action = 'reset';
            
        $this->markTestSkipped('');        

        echo " Resetting password " . PHP_EOL;        

        $params = array(
                    'Verb'          => 'PUT',
                    'Model'         => $model,
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => date("c"),
                    'KeyID'         => self::GIZURCLOUD_API_KEY,
                    'UniqueSalt'    => uniqid(),
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

        $this->credentials = array('anshuk-kumar@essindia.co.in' => 'ik13qfek');

        //login using each credentials
        foreach($this->credentials as $username => $password){            
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                  
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            echo PHP_EOL . "  " . $response = $rest->put($this->url.$model."/".$action);
            $response = json_decode($response);
            //check if response is valid
            if (isset($response->success)){
                //echo json_encode($response) . PHP_EOL;
                $this->assertEquals($response->success, true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            unset($rest);
       }
       echo PHP_EOL . PHP_EOL;
    }


    public function testGetAssetList(){
        $model = 'Assets';

        echo " Getting Asset List " . PHP_EOL;        

        $params = array(
                    'Verb'          => 'GET',
                    'Model'	    => $model,
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => date("c"),
                    'KeyID'         => self::GIZURCLOUD_API_KEY,
                    'UniqueSalt'    => uniqid()
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
        //login using each credentials
        foreach($this->credentials as $username => $password){            
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            echo PHP_EOL . "  " . $response = $rest->get($this->url.$model);
            $response = json_decode($response);
            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            unset($rest);
        } 
    }
 
    public function testGetTroubleTicketInoperationList(){
        $model = 'HelpDesk';
        $category = 'inoperation';

        echo " Getting Ticket Inoperation " . PHP_EOL;        

        $params = array(
                    'Verb'          => 'GET',
                    'Model'	    => $model,
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => date("c"),
                    'KeyID'         => self::GIZURCLOUD_API_KEY,
                    'UniqueSalt'    => uniqid()
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
        //login using each credentials
        foreach($this->credentials as $username => $password){            
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            echo $response = $rest->get($this->url.$model."/$category");
            $response = json_decode($response);
            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            unset($rest);
        } 
    }

    public function testGetTroubleTicketInoperationListWithFilter(){
        $model = 'HelpDesk';
        $category = 'all';
        $filter = Array(
            'year' => '0000',
            'month' => '00',
            'trailerid' => '0'
        );
        echo " Getting Ticket Inoperation With Filter" . PHP_EOL;        

        $params = array(
                    'Verb'          => 'GET',
                    'Model'         => $model,
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => date("c"),
                    'KeyID'         => self::GIZURCLOUD_API_KEY,
                    'UniqueSalt'    => uniqid()
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
        //login using each credentials
        foreach($this->credentials as $username => $password){            
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);

            echo $this->url.$model."/$category"."/".
                                                  $filter['year']."/".
                                                  $filter['month']."/".
                                                  $filter['trailerid'];
            echo $response = $rest->get($this->url.$model."/$category"."/".
                                                  $filter['year']."/".
                                                  $filter['month']."/".
                                                  $filter['trailerid']);
            $response = json_decode($response);
            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            unset($rest);
        } 
    }
  
   public function testGetTroubleTicketDamagedList(){
        $model = 'HelpDesk';
        $category = 'damaged';

        echo " Getting Ticket Damaged " . PHP_EOL;        

        $params = array(
                    'Verb'          => 'GET',
                    'Model'	    => $model,
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => date("c"),
                    'KeyID'         => self::GIZURCLOUD_API_KEY,
                    'UniqueSalt'    => uniqid(),
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
        //login using each credentials
        foreach($this->credentials as $username => $password){            
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            echo $response = $rest->get($this->url.$model."/$category");
            $response = json_decode($response);
            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            unset($rest);
        } 
    }

     public function testGetTroubleTicketFromId(){
        $model = 'HelpDesk';
        $id = '17x219';

        echo " Getting Ticket From ID $id" . PHP_EOL;        

        $params = array(
                    'Verb'          => 'GET',
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
        //login using each credentials
        foreach($this->credentials as $username => $password){            
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            $response = $rest->get($this->url.$model."/$id");
            $response = json_decode($response);
            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            unset($rest);
        } 
    }

   
    
    public function testCreateTroubleTicketWithOutDocument(){
        $model = 'HelpDesk';

        echo " Creating Trouble Ticket With Out Document" . PHP_EOL;        

        //set fields to to posted
	    $fields = array(
		    'ticket_title'=> 'Testing Using PHPUnit',
                    'drivercauseddamage'=>'No',
                    'sealed'=>'Yes',
                    'plates'=>'3',
                    'straps'=>'2',
                    'damagetype'=> 'Aggregatkåpa',
                    'damageposition' => 'Vänster sida (Left side)',
                    'ticketstatus' => 'Open',      
                    'reportdamage' => 'Yes',
                    'trailerid'=>'ASVVSD001'              
		);

        $params = array(
                    'Verb'          => 'POST',
                    'Model'         => $model,
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => date("c"),
                    'KeyID'         => self::GIZURCLOUD_API_KEY,
                    'UniqueSalt'    => uniqid()

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
        //login using each credentials
        foreach($this->credentials as $username => $password){            
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
            echo $response = $rest->post($this->url.$model, $fields);
            $response = json_decode($response);
            //check if response is valid
            if (isset($response->success)){
                $message = '';
                if (isset($response->error->message)) $message = $response->error->message;
                $this->assertEquals($response->success,true, $message);
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            unset($rest);
        } 
    }
    
    public function testCreateTroubleTicketWithDocument(){
        $model = 'HelpDesk';
        set_time_limit(0);
        echo " Creating Trouble Ticket with Document " . PHP_EOL;        

        //set fields to to posted
	    $fields = array(
		    'ticket_title'=>'Testing Using PHPUnit with Image Upload',
                    'filename'=>'@'.getcwd().'/image-to-upload.png',
                    'filename-1'=>'@'.getcwd().'/image-to-upload-1.png',
                    //'filename-2'=>'@'.getcwd().'/image-to-upload-2.png',
                    //'filename-3'=>'@'.getcwd().'/image-to-upload-3.png',
                    //'filename-4'=>'@'.getcwd().'/image-to-upload-4.png',
                    //'filename-5'=>'@'.getcwd().'/image-to-upload-5.png',
		            'ticket_title'=> 'Testing Using PHPUnit',
                    'drivercauseddamage'=>'No',
                    'sealed'=>'Yes',
                    'plates'=>'3',
                    'straps'=>'2',
                    'damagetype'=> 'Aggregatkåpa',
                    'damageposition' => 'Vänster sida (Left side)',
                    'ticketstatus' => 'Open',      
                    'reportdamage' => 'Yes',
                    'trailerid'=>'ASVVSD001'              
		);

    
        $params = array(
                    'Verb'          => 'POST',
                    'Model'	    => $model,
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => date("c"),
                    'KeyID'         => self::GIZURCLOUD_API_KEY,
                    'UniqueSalt'    => uniqid()
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
        //login using each credentials
        foreach($this->credentials as $username => $password){            
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
            echo PHP_EOL . $response = $rest->post($this->url.$model, $fields);
            $response = json_decode($response);
            //check if response is valid
            if (isset($response->success)){
                echo " Generated Ticket ID " . $response->result->id . PHP_EOL;
                $message = '';
                if (isset($response->error->message)) $message = $response->error->message;
                $this->assertEquals($response->success,true, $message);
                $this->assertNotEmpty($response->result->documents);
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            unset($rest);
        }  
        echo PHP_EOL . PHP_EOL;
    }

    public function testSignatureHash() {
        echo " Matching Signature Hash " . PHP_EOL;
        $this->markTestSkipped('');        

        $params = array(
                    'Verb'          => 'PUT',
                    'Model'         => 'Authenticate',
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => '20121003T18:12:36+0530',
                    'KeyID'         => self::GIZURCLOUD_API_KEY,
                    'UniqueSalt'    => 877421141
        );

        // Sorg arguments
        ksort($params);

        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";

        // Generate signature
        echo PHP_EOL.$string_to_sign;
        echo PHP_EOL . $signature = base64_encode(hash_hmac('SHA256', 
                    $string_to_sign, self::GIZURCLOUD_SECRET_KEY, 1));


        $signature_generated = '9+WNcE0LK1ObHJDZAhU2o7nmWC0JzKRbHb/WvSq/Sy0=';
        $this->assertEquals($signature, $signature_generated);
    }

    public function testUploadToAmazonS3() {
        echo " Uploading File To Amazons3" . PHP_EOL;
        //$this->markTestSkipped('');
                        $s3 = new AmazonS3();
                        
                        $file = Array(
                            'name' => getcwd().'/image-to-upload.jpg'
                        );

                        $response = $s3->create_object(
                                'gizurcloud-gc', 
                                'image-to-upload.jpg', 
                                array(
                            //'acl' => AmazonS3::ACL_PUBLIC,
                            'fileUpload' => $file['name'],
                            'contentType' => 'image/jpeg',
                            //'storage' => AmazonS3::STORAGE_REDUCED,
                            'headers' => array(
                                'Cache-Control'    => 'max-age',
                                //'Content-Encoding' => 'gzip',
                                'Content-Language' => 'en-US',
                                'Expires'          => 
                                'Thu, 01 Dec 1994 16:00:00 GMT',
                            )
                        ));                        
                        $this->assertEquals($response->isOK(), true);
    }
    
    public function testGetDocumentAttachment(){
        $model = 'DocumentAttachments';
        $notesid = '15x13';

        echo " Downloading Ticket Attachement " . PHP_EOL;        
    
        //login using each credentials
        foreach($this->credentials as $username => $password){            
            $params = array(
                        'Verb'          => 'GET',
                        'Model'	    => $model,
                        'Version'       => self::API_VERSION,
                        'Timestamp'     => date("c"),
                        'KeyID'         => self::GIZURCLOUD_API_KEY,
                        'UniqueSalt'    => uniqid()
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
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);

            echo $response = $rest->get($this->url.$model."/".$notesid);
            $response = json_decode($response);
            //print_r($response);
            //check if response is valid
            if (isset($response->success)){
                $message = '';
                if (isset($response->error->message)) $message = $response->error->message;
                $this->assertEquals($response->success,true, $message);
                $this->assertNotEmpty($response->result->filecontent);
                $fp = fopen('downloaded_'.$response->result->filename, 'w');
                fwrite($fp, base64_decode($response->result->filecontent));
                fclose($fp);
                $this->assertFileEquals('downloaded_'.$response->result->filename,$response->result->filename);
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            unset($rest);
        } 
 

    }

    public function testGetPicklist(){
        $model = 'HelpDesk';
        //$fieldname[0] = 'ticketstatus';
        $fieldname[1] = 'sealed';
        $fieldname[3] = 'reportdamage';
        $fieldname[0] = 'straps';
        //$fieldname[1] = 'plates';
        $fieldname[3] = 'damagereportlocation';

        echo " Getting Picklist" . PHP_EOL;        

        $params = array(
                    'Verb'          => 'GET',
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
        //login using each credentials
        foreach($this->credentials as $username => $password){            
            $rest = new RESTClient();
            $rest->format('json'); 
            $params['UniqueSalt'] = uniqid();
            // Sorg arguments
            ksort($params);

            // Generate string for sign
            $string_to_sign = "";
            foreach ($params as $k => $v)
                $string_to_sign .= "{$k}{$v}";

            // Generate signature
            $signature = base64_encode(hash_hmac('SHA256', 
                        $string_to_sign, self::GIZURCLOUD_SECRET_KEY, 1));
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);

            echo PHP_EOL . $response = $rest->get($this->url.$model."/".$fieldname[0]);
            $params['UniqueSalt'] = uniqid();
            // Sorg arguments
            ksort($params);

            // Generate string for sign
            $string_to_sign = "";
            foreach ($params as $k => $v)
                $string_to_sign .= "{$k}{$v}";

            // Generate signature
            $signature = base64_encode(hash_hmac('SHA256', 
                        $string_to_sign, self::GIZURCLOUD_SECRET_KEY, 1));
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
            echo PHP_EOL . $response = $rest->get($this->url.$model."/".$fieldname[1]);
            $params['UniqueSalt'] = uniqid();
            // Sorg arguments
            ksort($params);

            // Generate string for sign
            $string_to_sign = "";
            foreach ($params as $k => $v)
                $string_to_sign .= "{$k}{$v}";

            // Generate signature
            $signature = base64_encode(hash_hmac('SHA256', 
                        $string_to_sign, self::GIZURCLOUD_SECRET_KEY, 1));
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
            echo PHP_EOL . $response = $rest->get($this->url.$model."/".$fieldname[3]);

            $response = json_decode($response);
            //check if response is valid
            if (isset($response->success)){
                $message = '';
                if (isset($response->error->message)) $message = $response->error->message;
                $this->assertEquals($response->success,true, $message);
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            unset($rest);
        } 
    }
    
}
