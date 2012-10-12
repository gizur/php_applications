<?php

/**
 * @version 0.2
 * @package gizur
 * @copyright &copy; gizur
 * @author Anil Kumar Singh <anil-singh@essindia.co.in>
 */

/**
 * Unit Test class for Testing the Gizur REST API ( wrapper over 
 * vtiger Portal functional testing )
 * Contains methods which test  
 * Login / authentication, view details of an asset, list category based
 * trouble tickets and create a trouble ticket
 * 
 * Testing method:
 * > phpunit --verbrose PortalTest
 */
require_once 'PHPUnit/Autoload.php';
class PortalTest extends PHPUnit_Framework_TestCase
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
            $response = $rest->post($this->url.$model."/".$action);
            $response = json_decode($response);
            //check if response is valid
            if (isset($response->success)){
                //echo json_encode($response) . PHP_EOL;
                $this->assertEquals($response->success,$valid_credentials[$username], " Checking validity of response");
            } else {
                $this->assertInstanceOf('stdClass', $response);
            }
            unset($rest);
       }
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
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            echo $response = $rest->post($this->url.$model."/".$action);
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
            $response = $rest->get($this->url.$model);
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
		    'ticket_title'=> 'Functional Testing for Portal Using PHPUnit',
		    'ticketcategories' => 'Small Problem',
		    'trailerid'=>'VVS10001',  
		    'damagereportlocation' => 'Noida-Ghaziabad Road',
            'sealed'=>'Yes',
            'plates'=>'5',
            'straps'=>'3',
            'damagetype'=> 'Trailersidor',
            'damageposition' => 'Bakre (Back)',
            'drivercauseddamage'=>'No',
            'reportdamage' => 'Yes',
            'ticketstatus' => 'Open'     
           
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
            $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
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

        echo " Creating Trouble Ticket with Document "; // . PHP_EOL;        

        //set fields to to posted
	    $fields = array(
		    'ticket_title'=>'Portal Testing Using PHPUnit with Image Upload',
                    'filename'=>'@'.getcwd().'/img1.jpeg',
                    'drivercauseddamage' => 'Yes',
                    
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
            $response = $rest->post($this->url.$model, $fields);
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

    }

    public function testSignatureHash() {
        echo " Matching Signature Hash " . PHP_EOL;
               
        $signature = hash_hmac('SHA256','KeyIDGZCLDFC4B35BModelAuthenticateTimestamp2012-08-29 07:46:54 +0000VerbPOSTVersion0.1',
        self::GIZURCLOUD_SECRET_KEY, 1);
        $signature = base64_encode($signature);
        $signature_generated = '1206f25c0554ff8313ef681fb990217b';
        $this->assertEquals($signature, $signature_generated);
    }

    public function testUploadToAmazonS3() {
        echo " Uploading File To Amazons3" . PHP_EOL;
        $this->markTestSkipped('');
                        $s3 = new AmazonS3();
                        
                        $file = Array(
                            'name' => getcwd().'/img1.jpeg'
                        );

                        $response = $s3->create_object(
                                'gizurcloud', 
                                'img1.jpeg', 
                                array(
                            //'acl' => AmazonS3::ACL_PUBLIC,
                            'fileUpload' => $file['name'],
                            'contentType' => 'image/jpeg',
                            //'storage' => AmazonS3::STORAGE_REDUCED,
                            'headers' => array(
                                'Cache-Control'    => 'max-age',
                                //'Content-Encoding' => 'gzip',
                                'Content-language' => 'en-US',
                                'Expires'          => 
                                'Thu, 01 Dec 1994 16:00:00 GMT',
                            )
                        ));                        
                        $this->assertEquals($response->isOK(), true);
    }
    
    public function testGetDocumentAttachment(){
        $model = 'DocumentAttachments';
        $notesid = '15x268';

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
        $fieldname = 'damagetype';
        //$fieldname = 'damageposition';
        //$fieldname = 'damagereportlocation';
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
            $response = $rest->get($this->url.$model."/".$fieldname);
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
