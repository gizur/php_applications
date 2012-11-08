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
    Const GIZURCLOUD_SECRET_KEY = "50826a54755009.5822592450826a54755292.56509362";
    Const GIZURCLOUD_API_KEY = "GZCLD50826A54755AB50826A5475624";

    //Gizur Cloud 3
    //Const GIZURCLOUD_SECRET_KEY = "9b45e67513cb3377b0b18958c4de55be";
    //Const GIZURCLOUD_API_KEY = "GZCLDFC4B35B";

    Const API_VERSION = "0.1";
    
    private $_rest;

    protected $credentials = Array(
            //Gizur Cloud 3
            //'portal_user@gizur.com' => 'skcx0r0i',
            //'mobile_user@gizur.com' => 'ivry34aq',
            //Change Password User 
            //'anshuk.kumar@essindia.co.in' => 'ipjibl0f',
            //'anshuk.kumar@essindia.co.in' => 'dddddd',
          
            //Gizur Cloud 2 
            //'portal_user@gizur.com' => '2hxrftmd',
            'mobile_user@gizur.com' => 'ivry34aq',
            
            //Gizur Cloud 1
            //'mobile_app@gizur.com' => 'cwvvzvb0',
            //'jonas.colmsjo@gizur.com' => '507d136b23699',
    );

    //Cloud 1 
    //protected $url = "https://api.gizur.com/api/index.php/api/";

    //Cloud 2
    //protected $url = "https://phpapplications3-env-tk3itzr6av.elasticbeanstalk.com/api/index.php/api/";
    //protected $url = "https://c2.gizur.com/api/index.php/api/";
    
    //Cloud 3
    //protected $url = "http://phpapplications-env-sixmtjkbzs.elasticbeanstalk.com/api/";
    //protected $url = "http://gizurtrailerapp-env.elasticbeanstalk.com/api/index.php/api/";
    
    //Dev
    protected $url = "http://localhost/gizurcloud/api/index.php/api/";
 
    private function _generateSignature($method, $model, $timestamp, 
        $unique_salt)
    {
        //Build array
        $params = array(
            'Verb'          => $method,
            'Model'         => $model,
            'Version'       => self::API_VERSION,
            'Timestamp'     => $timestamp,
            'KeyID'         => self::GIZURCLOUD_API_KEY,
            'UniqueSalt'    => $unique_salt
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
        
        return array($params, $signature);
    }
    
    private function _setHeader($username, $password, $params, $signature)
    {
        $this->_rest->set_header('X_USERNAME', $username);
        $this->_rest->set_header('X_PASSWORD', $password);
        $this->_rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $this->_rest->set_header('X_SIGNATURE', $signature);                   
        $this->_rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
        $this->_rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);        
    }
    
    protected function setUp(){
        $this->_rest = new RESTClient();
        $this->_rest->format('json'); 
        $this->_rest->ssl(false);
        $this->_rest->language(array('en-us;q=0.5','sv'));        
    }
    
    protected function tearDown(){
        echo PHP_EOL . PHP_EOL;
    }

    public function testStressLogin()
    {
        //Request parameters
        $model = 'Authenticate';
        $action = 'login';
        $method = 'POST';
        $delta = 0;
        $times = 100;
        
        echo "Authenticating Login " . PHP_EOL;        
        ob_flush();
        
        for($i=0;$i<$times;$i++)
        //login using each credentials
        foreach($this->credentials as $username => $password){  
            
            //Create REST handle
            $this->setUp();            

            // Generate signature
            list($params, $signature) = $this->_generateSignature(
                    $method, $model, date("c"), 
                    uniqid()
            );
            
            //Set Header
            $this->_setHeader($username, $password, $params, $signature);  
            
            echo PHP_EOL . " Attempt No: $i Response: " . $response = $this->_rest->post(
                $this->url.$model."/".$action
            );
            
            $response = json_decode($response);
            if ($response->success == false) {
                if ($delta == 0) {
                    if ($response->error->code == 'TIME_NOT_IN_SYNC') {
                        $delta = $response->error->time_difference;
                    } 
                } else {
                    echo PHP_EOL . " Delta Used " . $delta;
                }
            }

            //check if response is valid
            if (isset($response->success)){
                //echo json_encode($response) . PHP_EOL;
                $this->assertEquals($response->success,true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            ob_flush();
       }
       echo PHP_EOL . PHP_EOL;
    }

    public function testLogin()
    {
        //Request parameters
        $model = 'Authenticate';
        $action = 'login';
        $method = 'POST';
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
            'portal_user@gizur.com' => true,
            'jonas.colmsjo@gizur.com' => true
        );        

        Restart:
        
        //login using each credentials
        foreach($this->credentials as $username => $password){  

            //Create REST handle
            $this->setUp();            

            // Generate signature
            list($params, $signature) = $this->_generateSignature(
                    $method, $model, date("c"), 
                    uniqid()
            );
            
            //Set Header
            $this->_setHeader($username, $password, $params, $signature);  
            
            echo PHP_EOL . " Response: " . $response = $this->_rest->post(
                $this->url.$model."/".$action
            );
            
            $response = json_decode($response);
            if ($response->success == false) {
                if ($delta == 0) {
                    if ($response->error->code == 'TIME_NOT_IN_SYNC') {
                        $delta = $response->error->time_difference;
                        goto Restart;
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
       }
       echo PHP_EOL . PHP_EOL;
    }

    public function testLogout()
    {
        //Request parameters
        $model = 'Authenticate';
        $action = 'logout';
        $method = 'POST';
           
        echo " Authenticating Logout " . PHP_EOL;        

        //Generate signature
        list($params, $signature) = $this->_generateSignature(
                $method, $model, date("c"), 
                uniqid()
        );
        
        //login using each credentials
        foreach($this->credentials as $username => $password){    
            
            //Create REST handle
            $this->setUp();
            
            //Set Header
            $this->_setHeader($username, $password, $params, $signature);
            
            //Show the Response
            echo PHP_EOL . " Response: " . $response = $this->_rest->post(
                $this->url.$model."/".$action
            );
            
            $response = json_decode($response);
            
            //check if response is valid
            if (isset($response->success)){
                //echo json_encode($response) . PHP_EOL;
                $this->assertEquals($response->success, true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
       }
       echo PHP_EOL . PHP_EOL;
    }

    public function testCron()
    {
        //Request parameters
        $model = 'Cron';
        $action = 'mailscan';
        $method = 'POST';
           
        echo " Executing Cron Mailscan " . PHP_EOL;        

        // Generate signature
        list($params, $signature) = $this->_generateSignature(
                $method, $model, date("c"), 
                uniqid()
        );
        
        //Set Header
        $this->_setHeader('', '', $params, $signature);
        
        echo PHP_EOL . " Response:  " . $response = $this->_rest->put($this->url.$model."/".$action);
        
        echo PHP_EOL . PHP_EOL;
    }

    public function testAbout()
    {
        //Request parameters
        $model = 'About';
        $method = 'GET';
        
        echo " Fetching About " . PHP_EOL;        

        // Generate signature
        list($params, $signature) = $this->_generateSignature(
                $method, $model, date("c"), 
                uniqid()
        );
         
        //Set Header
        $this->_setHeader('', '', $params, $signature);
        
        echo PHP_EOL . " Response:  " . $response = $this->_rest->get($this->url.$model);
        
        echo PHP_EOL . PHP_EOL;
    }

    public function testChangePassword()
    {        
        //Request parameters
        $model = 'Authenticate';
        $action = 'changepw';
        $method = 'PUT';
        //$newpassword = 'dddddd';
        //$newpassword = 'ipjibl0f';
           
        //Label the Test
        echo " Change Password " . PHP_EOL;        
        $this->markTestSkipped('');
        
        // Generate signature
        list($params, $signature) = $this->_generateSignature(
                $method, $model, date("c"), 
                uniqid()
        );
        

        //login using each credentials
        foreach($this->credentials as $username => $password){            
        
            //Set Header
            $this->_setHeader($username, $password, $params, $signature);
        
            //Show the response
            echo PHP_EOL . " Response:  " . $response = $this->_rest->put($this->url.$model."/".$action, array('newpassword' => $newpassword));
            $response = json_decode($response);
            
            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success, true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            
       }
       
       echo PHP_EOL . PHP_EOL;
    }

    public function testChangeAssetStatus()
    {
        //Request Parameters       
        $model = 'Assets';
        $id = '28x8';
           
        //Label the Test
        echo " Changing Asset Status" . PHP_EOL;        
        $this->markTestSkipped(''); 

        //login using each credentials
        foreach($this->credentials as $username => $password){ 
            
            // Generate signature
            list($params, $signature) = $this->_generateSignature(
                    $method, $model, date("c"), 
                    uniqid()
            );
        
            //Set Header
            $this->_setHeader($username, $password, $params, $signature);
        
            //Show the reponse
            echo PHP_EOL . " Response:  " . $response = $this->_rest->put($this->url.$model."/".$id, array('assetstatus' => 'In Service'));
            $response = json_decode($response);
            
            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->result->assetstatus, 'In Service', " Checking validity of response");
                $this->assertEquals($response->success, true, " Checking validity of response");   
            } else {
                $this->assertInstanceOf('stdClass', $response);     
            }
            
            // Generate signature
            list($params, $signature) = $this->_generateSignature(
                    $method, $model, date("c"), 
                    uniqid()
            );
        
            //Set Header
            $this->_setHeader($username, $password, $params, $signature);
        
            //Show the response
            echo PHP_EOL . " Response: " . $response = $this->_rest->put($this->url.$model."/".$id, array('assetstatus' => 'Out-of-service'));
            $response = json_decode($response);
            
            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->result->assetstatus, 'Out-of-service', " Checking validity of response");
                $this->assertEquals($response->success, true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
        }
        echo PHP_EOL . PHP_EOL;
    }


    public function testResetPassword()
    {
        //Request Parameters     
        $model = 'Authenticate';
        $action = 'reset';
        $method = 'PUT';      

        //Label the test
        echo " Resetting password " . PHP_EOL;  
        
        //Skipping Test
        $this->markTestSkipped('');         

        // Generate signature
        list($params, $signature) = $this->_generateSignature(
                $method, $model, date("c"), 
                uniqid()
        );

        //Set Reset Pasword credentials
        $this->credentials = array('anshuk-kumar@essindia.co.in' => 'ik13qfek');

        //login using each credentials
        foreach($this->credentials as $username => $password){            
        
            //Set Header
            $this->_setHeader($username, $password, $params, $signature);
        
            //Show the response
            echo PHP_EOL . " Response: " . $response = $this->_rest->put(
                $this->url.$model."/".$action
            );
            $response = json_decode($response);
            
            //check if response is valid
            if (isset($response->success)){
                //echo json_encode($response) . PHP_EOL;
                $this->assertEquals(
                    $response->success, true, " Checking validity of response"
                );
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
       }
       echo PHP_EOL . PHP_EOL;
    }


    public function testGetAssetList()
    {
        //Request Parameters
        $model = 'Assets';
        $method = 'GET';

        //Label the test
        echo " Getting Asset List " . PHP_EOL;        

        // Generate signature
        list($params, $signature) = $this->_generateSignature(
                $method, $model, date("c"), 
                uniqid()
        );
            
        //login using each credentials
        foreach($this->credentials as $username => $password){            
        
            //Set Header
            $this->_setHeader($username, $password, $params, $signature);
        
            //Show the response
            echo PHP_EOL . " Response: " . $response = $this->_rest->get(
                $this->url.$model
            );
            $response = json_decode($response);
            
            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
        } 
    }
 
    public function testGetTroubleTicketInoperationList()
    {   
        //Request Parameters
        $model = 'HelpDesk';
        $category = 'inoperation';
        $method = 'GET';

        //Label the test
        echo " Getting Ticket Inoperation " . PHP_EOL;        

        // Generate signature
        list($params, $signature) = $this->_generateSignature(
                $method, $model, date("c"), 
                uniqid()
        );

        //login using each credentials
        foreach($this->credentials as $username => $password){            
        
            //Set Header
            $this->_setHeader($username, $password, $params, $signature);
        
            //Show the response
            echo " Response: " . $response = $this->_rest->get(
                $this->url.$model."/$category"
            );
            $response = json_decode($response);
            
            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
        }
        
        echo PHP_EOL . PHP_EOL;        
        
    }

    public function testGetTroubleTicketInoperationListWithFilter()
    {
        //Request Parameter
        $model = 'HelpDesk';
        $category = 'all';
        $filter = Array(
            'year' => '0000',
            'month' => '00',
            'trailerid' => '0',
            'reportdamage' => 'all'
        );
        $method = 'GET';
        
        //Label the test
        echo " Getting Ticket Inoperation With Filter" . PHP_EOL;        

        // Generate signature
        list($params, $signature) = $this->_generateSignature(
                $method, $model, date("c"), 
                uniqid()
        );

        //login using each credentials
        foreach($this->credentials as $username => $password){            
        
            //Set Header
            $this->_setHeader($username, $password, $params, $signature);

            //Show the URL
            echo " Request URL: " . $this->url.$model."/$category"."/".
                $filter['year']."/".
                $filter['month']."/".
                $filter['trailerid']."/".
                $filter['reportdamage'] . PHP_EOL;
            //Show the response
            echo " Response: " . $response = $this->_rest->get(
                $this->url.$model."/$category"."/".
                $filter['year']."/".
                $filter['month']."/".
                $filter['trailerid']."/".
                $filter['reportdamage']
            );
            $response = json_decode($response);
            
            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
        } 
        
       echo PHP_EOL . PHP_EOL;        
        
    }
  
   public function testGetTroubleTicketDamagedList()
   {
       //Request Parameters
        $model = 'HelpDesk';
        $category = 'damaged';
        $method = 'GET';

        //Label the test
        echo " Getting Ticket Damaged " . PHP_EOL;        

        // Generate signature
        list($params, $signature) = $this->_generateSignature(
                $method, $model, date("c"), 
                uniqid()
        );

        //login using each credentials
        foreach($this->credentials as $username => $password){            
        
            //Set Header
            $this->_setHeader($username, $password, $params, $signature);
        
            echo " Response: " . $response = $this->_rest->get($this->url.$model."/$category");
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

     public function testGetTroubleTicketFromId()
     {
        //Request Parameters
        $model = 'HelpDesk';
        $id = '17x219';
        $method = '';

        //Label the test
        echo " Getting Ticket From ID $id" . PHP_EOL;
        
        //Skip the test 
        $this->markTestSkipped('');
        
        // Generate signature
        list($params, $signature) = $this->_generateSignature(
                $method, $model, date("c"), 
                uniqid()
        );

        //login using each credentials
        foreach($this->credentials as $username => $password){            
        
            //Set Header
            $this->_setHeader($username, $password, $params, $signature);
        
            echo " Response: " . $response = $this->_rest->get($this->url.$model."/$id");
            $response = json_decode($response);
            
            //check if response is valid
            if (isset($response->success)){
                $this->assertEquals($response->success,true, " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
        } 
    }

   
    
    public function testCreateTroubleTicketWithOutDocument()
    {
        //Request Parameters
        $method = 'POST';
        $model = 'HelpDesk';

        //Label the test
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

        // Generate signature
        list($params, $signature) = $this->_generateSignature(
                $method, $model, date("c"), 
                uniqid()
        );

        //login using each credentials
        foreach($this->credentials as $username => $password){            
        
            //Set Header
            $this->_setHeader($username, $password, $params, $signature);
        
            //Show the response
            echo " Response: " . $response = $this->_rest->post(
                $this->url.$model, $fields
            );
            $response = json_decode($response);
            
            //check if response is valid
            if (isset($response->success)){
                $message = '';
                if (isset($response->error->message)) $message = $response->error->message;
                $this->assertEquals($response->success,true, $message);
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
        } 
    }
    
    public function testCreateTroubleTicketWithDocument()
    {
        //Request Parameters 
        $model = 'HelpDesk';
        $method = 'POST';
        
        //Setting infinite execution time for this script
        set_time_limit(0); 
        
        //Label the Test
        echo " Creating Trouble Ticket with Document " . PHP_EOL;        

        //Set fields to to posted
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

        // Generate signature
        list($params, $signature) = $this->_generateSignature(
                $method, $model, date("c"), 
                uniqid()
        );

        //login using each credentials
        foreach($this->credentials as $username => $password){            
        
            //Set Header
            $this->_setHeader($username, $password, $params, $signature);
        
            echo PHP_EOL . " Response: " . $response = $this->_rest->post($this->url.$model, $fields);
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
        
        //Label the test
        echo " Matching Signature Hash " . PHP_EOL;
        
        //Skip the Test
        $this->markTestSkipped('');        

        // Generate signature
        list($params, $signature) = $this->_generateSignature(
                $method, $model, date("c"), 
                uniqid()
        );

        $signature_generated = '9+WNcE0LK1ObHJDZAhU2o7nmWC0JzKRbHb/WvSq/Sy0=';
        $this->assertEquals($signature, $signature_generated);
    }

    public function testUploadToAmazonS3() {
        echo " Uploading File To Amazons3" . PHP_EOL;
        $this->markTestSkipped('');
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
    
    public function testGetDocumentAttachment()
    {
        //Request Parameters
        $model = 'DocumentAttachments';
        $notesid = '15x13';
        $method = 'GET';

        //Label the test
        echo " Downloading Ticket Attachement " . PHP_EOL;   
        
        //Skip the test
        $this->markTestSkipped('');         
    
        //login using each credentials
        foreach($this->credentials as $username => $password){     
            
            // Generate signature
            list($params, $signature) = $this->_generateSignature(
                    $method, $model, date("c"), 
                    uniqid()
            );

            //Set Header
            $this->_setHeader($username, $password, $params, $signature);

            echo " Response: " . $response = $this->_rest->get($this->url.$model."/".$notesid);
            $response = json_decode($response);

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
        }
    }

    public function testGetPicklist()
    {
        //Request Parameters
        $method = 'GET';
        $model = 'HelpDesk';
        
        //$fieldname[0] = 'ticketstatus';
        $fieldnames = array(
            'sealed',
            'reportdamage',
            'plates',
            'damagereportlocation'
        );

        //Label the test
        echo " Getting Picklist" . PHP_EOL;        

        //login using each credentials
        foreach($this->credentials as $username => $password){            
            
            //Loop throug all fieldnames and access them
            foreach($fieldnames as $fieldname) {
                
                //Reset REST Handle
                $this->setUp();
                
                // Generate signature
                list($params, $signature) = $this->_generateSignature(
                        $method, $model, date("c"), 
                        uniqid()
                );

                //Set Header
                $this->_setHeader($username, $password, $params, $signature);

                //Show the response
                echo PHP_EOL . " Response: " .$response = $this->_rest->get(
                    $this->url.$model."/".$fieldname
                );

                $response = json_decode($response);
                
                //check if response is valid
                if (isset($response->success)){
                    $message = '';
                    if (isset($response->error->message)) $message = $response->error->message;
                    $this->assertEquals($response->success,true, $message);
                } else {
                    $this->assertInstanceOf('stdClass', $response);
                }
            }   
        }
        
        echo PHP_EOL . PHP_EOL;        
    }   
}
