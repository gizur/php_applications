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

    Const GIZURCLOUD_SECRET_KEY  = "9b45e67513cb3377b0b18958c4de55be";
    Const GIZURCLOUD_API_KEY = "GZCLDFC4B35B";
    Const API_VERSION = "0.1";

    protected $credentials = Array(
            'cloud3@gizur.com' => 'rksh2jjf',
    );

    protected $url = "http://gizurtrailerapp-env.elasticbeanstalk.com/api/index.php/api/";
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
            'test@test.com' => false
        );        
        generateSignature:
        $params = array(
                    'Verb'          => 'POST',
                    'Model'	    => $model,
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => date("c", strtotime("+2 minutes") + $delta),
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
            echo PHP_EOL . "  " . $response = $rest->post($this->url.$model."/".$action);
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



    public function testGetAssetList(){
        $model = 'Assets';

        echo " Getting Asset List " . PHP_EOL;        

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
            $response = $rest->get($this->url.$model."/$category");
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
        $category = 'inoperation';
        $filter = Array(
            'year' => '2012',
            'month' => '08',
            'trailerid' => 'AS0001'
        );
        echo " Getting Ticket Inoperation With Filter" . PHP_EOL;        

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
            $response = $rest->get($this->url.$model."/$category"."/".
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
            $response = $rest->get($this->url.$model."/$category");
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

   
    
    public function testCreateTroubleTicket(){
        $model = 'HelpDesk';

        echo " Creating Trouble Ticket " . PHP_EOL;        

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
            $response = $rest->post($this->url.$model, $fields);
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
        //$this->markTestSkipped('');        

        $server = json_decode('{"forwarded":"1","HTTP_HOST":"gizurtrailerapp-env.elasticbeanstalk.com","HTTP_ACCEPT":"text\/json","HTTP_ACCEPT_LANGUAGE":"sv,en-us,en;q=0.5","HTTP_USER_AGENT":"Apache-HttpClient\/UNAVAILABLE (java 1.4)","HTTP_X_GIZURCLOUD_API_KEY":"GZCLDFC4B35B","HTTP_X_PASSWORD":"rksh2jjf","HTTP_X_SIGNATURE":"cs+PLjjmeSKCAD6qHgcHg3BYBlqt8j5Kx1EWioSivRo=","HTTP_X_TIMESTAMP":"20120919T12:18:34+0530","HTTP_X_USERNAME":"cloud3@gizur.com","HTTP_X_FORWARDED_FOR":"182.72.78.122","HTTP_X_FORWARDED_PORT":"80","HTTP_X_FORWARDED_PROTO":"http","HTTP_CONNECTION":"keep-alive","PATH":"\/sbin:\/usr\/sbin:\/bin:\/usr\/bin","SERVER_SIGNATURE":"<address>Apache\/2.2.22 (Amazon) Server at gizurtrailerapp-env.elasticbeanstalk.com Port 80<\/address>\n","SERVER_SOFTWARE":"Apache\/2.2.22 (Amazon)","SERVER_NAME":"gizurtrailerapp-env.elasticbeanstalk.com","SERVER_ADDR":"10.58.190.74","SERVER_PORT":"80","REMOTE_ADDR":"10.58.133.126","DOCUMENT_ROOT":"\/var\/www\/html","SERVER_ADMIN":"root@localhost","SCRIPT_FILENAME":"\/var\/www\/html\/api\/index.php","REMOTE_PORT":"12674","GATEWAY_INTERFACE":"CGI\/1.1","SERVER_PROTOCOL":"HTTP\/1.1","REQUEST_METHOD":"GET","QUERY_STRING":"","REQUEST_URI":"\/api\/index.php\/api\/Assets\/trailerid","SCRIPT_NAME":"\/api\/index.php","PATH_INFO":"\/api\/Assets\/trailerid","PATH_TRANSLATED":"redirect:\/index.php\/trailerid","PHP_SELF":"\/api\/index.php\/api\/Assets\/trailerid","REQUEST_TIME":1348037321}', true);
        $params = array(
                    'Verb'          => $server['REQUEST_METHOD'],
                    'Model'         => 'Assets',
                    'Version'       => self::API_VERSION,
                    'Timestamp'     => $server['HTTP_X_TIMESTAMP'],
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


        //$signature = hash_hmac('SHA256','KeyIDGZCLDFC4B35BModelAuthenticateTimestamp2012-08-29 07:46:54 +0000VerbPOSTVersion0.1',
        //self::GIZURCLOUD_SECRET_KEY, 1);
        //$signature = base64_encode($signature);
        $signature_generated = '1206f25c0554ff8313ef681fb990217b';
        $this->assertEquals($signature, $server['HTTP_X_SIGNATURE']);
    }

    public function testUploadToAmazonS3() {
        echo " Uploading File To Amazons3" . PHP_EOL;
        $this->markTestSkipped('');
                        $s3 = new AmazonS3();
                        
                        $file = Array(
                            'name' => getcwd().'/image-to-upload.jpg'
                        );

                        $response = $s3->create_object(
                                'gizurcloud', 
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
        $notesid = '15x501';

        echo " Downloading Ticket Attachement " . PHP_EOL;        
    
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
            $response = $rest->get($this->url.$model."/".$notesid);
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
        $fieldname[0] = 'ticketstatus';
        $fieldname[1] = 'sealed';
        $fieldname[3] = 'reportdamage';
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
            $rest->set_header('X_USERNAME', $username);
            $rest->set_header('X_PASSWORD', $password);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);

            echo PHP_EOL . $response = $rest->get($this->url.$model."/".$fieldname[0]);
            echo PHP_EOL . $response = $rest->get($this->url.$model."/".$fieldname[1]);
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
