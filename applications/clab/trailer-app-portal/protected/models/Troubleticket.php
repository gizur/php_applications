<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
global $custom_fields;
$custom_fields = array(
    "Title" => 'ticket_title',
    "Category" => 'ticketcategories',
    "TrailerID" => 'trailerid',
    "Damagereportlocation" => 'damagereportlocation',
    "Sealed" => 'sealed',
    "Plates" => 'plates',
    "Straps" => 'straps',
    "TroubleTicketType" => 'cf_641',
    "Typeofdamage" => 'damagetype',
    "Damageposition" => 'damageposition',
    "Drivercauseddamage" => 'drivercauseddamage',
);

class Troubleticket extends CFormModel {

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public $id;
    public $Title;
    public $Category;
    public $TrailerID;
    public $Damagereportlocation;
    public $Plates;
    public $Straps;
    public $Sealed;
    public $Typeofdamage;
    public $Damageposition;
    public $image;
    public $TroubleTicketType;
    public $drivercauseddamage;
    public $reportdamage;
    public $ticketstatus;
    public $damagestatus;
    public $notes;

    public function rules() {
        return array(
            // username and password are required
            array('Title,TrailerID, Damagereportlocation,Straps', 'required'),
        );
    }

    public function attributeLabels() {
        return array(
            'category' => 'Ticket Category',
        );
    }

    public function index($params) {
        
    }

    public function view($params) {
        
    }

    function getpickList($fieldname) {
        $model = 'HelpDesk';
        //echo " Getting Picklist" . PHP_EOL;        

        $params = array(
            'Verb' => 'GET',
            'Model' => $model,
            'Version' => Yii::app()->params->API_VERSION,
            'Timestamp' => date("c"),
            'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
            'UniqueSalt' => uniqid()
        );

        // Sorg arguments
        ksort($params);

        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";

        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
        //login using each credentials
        $response['result'] = array();
        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->get(Yii::app()->params->URL . $model . "/" . $fieldname);
        $response = json_decode($response, true);
        //check if response is valid
        $picklistarr = array();
        //$result['result']=array('1','2','3','4');
        foreach ($response['result'] as $val) {
            $picklistarr[$val['value']] = $val['label'];
        }
        return $picklistarr;
        //unset($rest);
        //} 
    }

    function Save($data) {
        global $custom_fields;
        foreach ($data as $key => $val) {
            if (array_key_exists($key, $custom_fields)) {
                $data[$custom_fields[$key]] = $data[$key];
                unset($data[$key]);
            }
        }

        if (!empty($_FILES)) {
            $files = array();
            foreach ($_FILES['Troubleticket']['name'] as $key => $filename) {
                if (!empty($_FILES['Troubleticket']["name"][$key])) {
                    $tmp_name = $_FILES['Troubleticket']["tmp_name"][$key];
                    $name = $_FILES['Troubleticket']["name"][$key];
                    $data[$key] = "@{$tmp_name}";
                }
            }
        }

        $params = array(
            'Verb' => 'POST',
            'Model' => 'HelpDesk',
            'Version' => Yii::app()->params->API_VERSION,
            'Timestamp' => date("c"),
            'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
            'UniqueSalt' => uniqid()
        );

        // Sorg arguments
        ksort($params);

        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";

        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
        //login using each credentials
        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->post(Yii::app()->params->URL . "HelpDesk", $data);
        $response = json_decode($response);

        if ($response->success == true) {
            $TicketID = $response->result->ticket_no;
            echo Yii::app()->user->setFlash('success', "Your Ticket ID : " . $TicketID);
        } else {
            echo Yii::app()->user->setFlash('error', $response->error->message);
        }
    }

    function findAll($module, $tickettype, $year = '0000', $month = '00', $trailer = '0', 
        $reportdamage = 'all', $minLimit, $maxLimit) {
        $params = array(
            'Verb' => 'GET',
            'Model' => $module,
            'Version' => Yii::app()->params->API_VERSION,
            'Timestamp' => date("c"),
            'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
            'UniqueSalt' => uniqid()
        );

        // Sorg arguments
        ksort($params);

        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";

        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
        //login using each credentials
        // Check Filter Parameter
        $FilterParameter = array();
        if ($year != "") {
            $FilterParameter[] = $year;
        }
        if ($month != "") {
            $FilterParameter[] = $month;
        }
        if ($trailer != "") {
            $FilterParameter[] = $trailer;
        }
        if ( $reportdamage != "") {
            $FilterParameter[] = $reportdamage;
        }        
        $extraparameter = implode('/', $FilterParameter);
        if (!empty($extraparameter)) {
            $extraparameter = "/" . $extraparameter;
        }
        //foreach($this->credentials as $username => $password){            
        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
         $response = $rest->get(Yii::app()->params->URL . $module . "/" .$minLimit."/".$maxLimit."/". $tickettype . $extraparameter);

        return $result = json_decode($response, true);
    }

    /*
     *  This actions use for get Assets list
     */

    function findAssets($module) {

        $params = array(
            'Verb' => 'GET',
            'Model' => $module,
            'Version' => Yii::app()->params->API_VERSION,
            'Timestamp' => date("c"),
            'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
            'UniqueSalt' => uniqid()
        );

        // Sorg arguments
        ksort($params);

        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";

        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));

        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->get(Yii::app()->params->URL . $module);
        $response = json_decode($response, true);
        $assetlistarr = array();

        if ($response['success'] == false) {
            return false;
        }

        if (!isset($response['result'])) {
            return false;
        }

        foreach ($response['result'] as $val) {
            $assetlistarr[$val['id']] = $val['assetname'];
        }

        return $assetlistarr;
    }

    /*
     *  Data Fetch particuller records
     */

    function findById($model, $ID) {
        $params = array(
            'Verb' => 'GET',
            'Model' => $model,
            'Version' => Yii::app()->params->API_VERSION,
            'Timestamp' => date("c"),
            'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
            'UniqueSalt' => uniqid()
        );

        // Sorg arguments
        ksort($params);

        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";

        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
        //login using each credentials
        //foreach($this->credentials as $username => $password){            
        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->get(Yii::app()->params->URL . $model . "/" . $ID);
        return $result = json_decode($response, true);
    }

    function getimage($module, $tid) {
        $params = array(
            'Verb' => 'GET',
            'Model' => $module,
            'Version' => Yii::app()->params->API_VERSION,
            'Timestamp' => date("c"),
            'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
            'UniqueSalt' => uniqid()
        );

        // Sorg arguments
        ksort($params);

        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";

        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
        //login using each credentials
        //foreach($this->credentials as $username => $password){            
        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->get(Yii::app()->params->URL . $module . "/" . $tid);
        return $result = json_decode($response, true);
    }

    /*
     *  Change Ticket Status click on mark damage required button
     * 
     */

    function Markdamagerequired($model, $ticketID) {
        $params = array(
            'Verb' => 'PUT',
            'Model' => $model,
            'Version' => Yii::app()->params->API_VERSION,
            'Timestamp' => date("c"),
            'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
            'UniqueSalt' => uniqid()
        );

        // Sorg arguments
        ksort($params);

        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";

        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
        //login using each credentials
        //foreach($this->credentials as $username => $password){            
        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->put(Yii::app()->params->URL . $model . "/" . $ticketID);
        return $result = json_decode($response, true);
    }
    
    /*
     *  Change Ticket Status click on mark damage required button
     * 
     */
    
    function updateDamageStatusAndNotes($ticketID, $data) {
        $params = array(
            'Verb' => 'PUT',
            'Model' => 'HelpDesk',
            'Version' => Yii::app()->params->API_VERSION,
            'Timestamp' => date("c"),
            'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
            'UniqueSalt' => uniqid()
        );

        // Sorg arguments
        ksort($params);

        // Generate string for sign
        $string_to_sign = "";
        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";

        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
        //login using each credentials
        //foreach($this->credentials as $username => $password){            
        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->put(Yii::app()->params->URL . "HelpDesk/updatedamagenotes/" . $ticketID, $data);
        return $result = json_decode($response, true);
    }

    /*
     * Change Asset Status  
     * 
     */

    function ChangeAssetStatus($oprations, $cassets) {
        $params = array(
            'Verb' => 'PUT',
            'Model' => 'Assets',
            'Version' => Yii::app()->params->API_VERSION,
            'Timestamp' => date("c"),
            'KeyID' => Yii::app()->params->GIZURCLOUD_API_KEY,
            'UniqueSalt' => uniqid()
        );

        // Sorg arguments
        ksort($params);
        if ($oprations == 'inoperation') {
            $data = array('assetstatus' => 'In Service');
        } else {
            $data = array('assetstatus' => 'Out-of-service');
        }

        // Generate string for sign
        $string_to_sign = "";

        foreach ($params as $k => $v)
            $string_to_sign .= "{$k}{$v}";

        // Generate signature
        $signature = base64_encode(hash_hmac('SHA256', $string_to_sign, Yii::app()->params->GIZURCLOUD_SECRET_KEY, 1));
        //login using each credentials
        //foreach($this->credentials as $username => $password){            
        $rest = new RESTClient();
        $rest->format('json');
        $rest->set_header('X_USERNAME', Yii::app()->session['username']);
        $rest->set_header('X_PASSWORD', Yii::app()->session['password']);
        $rest->set_header('X_TIMESTAMP', $params['Timestamp']);
        $rest->set_header('X_UNIQUE_SALT', $params['UniqueSalt']);
        $rest->set_header('X_SIGNATURE', $signature);
        $rest->set_header('X_GIZURCLOUD_API_KEY', Yii::app()->params->GIZURCLOUD_API_KEY);
        $response = $rest->put(Yii::app()->params->URL . "Assets/" . $cassets, $data);
        return $result = json_decode($response, true);
    }

}
