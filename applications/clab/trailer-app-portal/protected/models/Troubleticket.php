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
	  "Damagereport" => 'cf_634',
	  "Sealed" => 'cf_637' ,
	  "Plates" => 'cf_631',
	  "Straps" => 'cf_632',
	  "TroubleTicketType"  => 'cf_633',
	  "Typeofdamage" => 'cf_635',
	  "Damageposition" => 'cf_636'
  );
class Troubleticket extends CFormModel
{
	

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
	$rest = new RESTClient();
    $rest->format('json');
    $rest->set_header('X_USERNAME',Yii::app()->session['username']);
	$rest->set_header('X_PASSWORD',Yii::app()->session['password']); 
    $response = $rest->get(VT_REST_URL."HelpDesk/".$fieldname);
	$result= json_decode($response,true);
	
	$picklistarr=array();
	foreach($result['result'] as $val)
	{
		$picklistarr[$val['value']]=$val['label'];
	 }
	 return $picklistarr;
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
	 $rest = new RESTClient();
    $rest->format('json');
    $rest->set_header('X_USERNAME',Yii::app()->session['username']);
	$rest->set_header('X_PASSWORD',Yii::app()->session['password']); 
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

	
}
