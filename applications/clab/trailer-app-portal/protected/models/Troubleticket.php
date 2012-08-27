<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
 global $custom_fields;
 $custom_fields=array(
      "Title" => 'ticket_title',
      "Category" => 'ticketcategories',
	  "TrailerID" => 'cf_628',
	  "Damagereport" => 'cf_647',
	  "Sealed" => 'cf_644' ,
	  "Plates" => 'cf_631',
	  "Straps" => 'cf_632',
	  "TroubleTicketType"  => 'cf_641',
	  "Typeofdamage" => 'cf_648',
	  "Damageposition" => 'cf_649'
  );
class Troubleticket extends CFormModel
{
	Const GIZURCLOUD_SECRET_KEY  = "9b45e67513cb3377b0b18958c4de55be";
    Const GIZURCLOUD_API_KEY = "GZCLDFC4B35B";
    Const API_VERSION = "0.1";
	 protected $credentials = Array(
            'cloud3@gizur.com' => 'rksh2jjf',
    );
    protected $url = "http://gizurtrailerapp-env.elasticbeanstalk.com/api/index.php/api/";

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public $Title; 
	public $Category;
	public $TrailerID;
	public $Damagereport;
	public $Plates;
	public $Straps;
	public $Sealed;
	public $Typeofdamage;
	public $Damageposition;
	public $image;
	public $TroubleTicketType;


public function rules()
	{
		return array(
			// username and password are required
			array('Title,TrailerID, Damagereport,Straps', 'required'),
			
		);
	}
	
public function attributeLabels()
	{
		return array(
			'category'=>'Ticket Category',
		);
	}
	

  public function index($params)
  {
	
	   
	  
  }

  
  public function view($params)
  {
	    
	   
	  
  }
  
  function getpickList($fieldname)
  {
	    $model = 'HelpDesk';
        $fieldname = 'ticketpriorities';

        //echo " Getting Picklist" . PHP_EOL;        

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
        //foreach($this->credentials as $username => $password){            
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', Yii::app()->session['username']);
            $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
            $response = $rest->get($this->url.$model."/".$fieldname);
            $response = json_decode($response);
            //check if response is valid
            	$picklistarr=array();
				$result['result']=array('1','2','3','4');
				foreach($result['result'] as $val)
				{
					$picklistarr[$val['value']]=$val['label'];
				 }
	           return $picklistarr;
            //unset($rest);
        //} 
   
  }
  
  function Save($data)
  {
	   global $custom_fields;
       foreach($data as $key => $val )
       {
		 if(array_key_exists($key,$custom_fields))
		 {
		  $data[$custom_fields[$key]] = $data[$key];
		  unset($data[$key]);
		  }
	    
	    }
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
        //foreach($this->credentials as $username => $password){            
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', Yii::app()->session['username']);
            $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY); 
    $response = $rest->post(VT_REST_URL."HelpDesk",$data);
	$response = json_decode($response);
	if($response->success==true){
		$TicketID=$response->result->ticket_no;
	echo Yii::app()->user->setFlash('success', "Your Ticket ID : ". $TicketID); 
     } else
     {
	  echo Yii::app()->user->setFlash('error', "Some Isssue in process."); 
	  }
	  
  }


function findAll($module,$tickettype)
  {
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
        //foreach($this->credentials as $username => $password){            
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', Yii::app()->session['username']);
            $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
	        $response = $rest->get(VT_REST_URL.$module."/".$tickettype);
	       return $result= json_decode($response,true);
		  
  }

/*
 *  Data Fetch particuller records
 */ 
 
  function findById($module,$ID)
  {
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
        //foreach($this->credentials as $username => $password){            
            $rest = new RESTClient();
            $rest->format('json'); 
            $rest->set_header('X_USERNAME', Yii::app()->session['username']);
            $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
            $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
            $rest->set_header('X_SIGNATURE', $signature);                   
            $rest->set_header('X_GIZURCLOUD_API_KEY', self::GIZURCLOUD_API_KEY);
	$response = $rest->get(VT_REST_URL.$module."/".$ID);
	return $result= json_decode($response,true);  
	  
  }
	
}
