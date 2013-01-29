<?php

/**
 * PHP Unit Test cases class. Testin Gizur SOAP API
 * 
 * PHP version 5
 * 
 * @category   Test
 * @package    Gizur
 * @subpackage Test
 * @author     Prabhat Khera <prabhat.khera@essindia.co.in>
 * @copyright  2012 &copy; Gizur AB
 * @license    Gizur Private Licence
 * @version    0.2
 * @link       http://www.gizur.com
 */
/**
 * Unit Test class for Testing the Gizur SOAP API
 * Contains methods which test  
 * Login / authentication, view details of an asset, list category based
 * trouble tickets and create a trouble ticket
 * 
 * Testing method:
 * > phpunit --verbrose Gizur_SOAP_API_Test
 */
require_once 'config.inc.php';
require_once 'lib/nusoap.php';
global $_session_id;
global $_customer_id;
global $_customer_name;
global $_customer_account_id;

/**
 * Gizur Test inherist PHP Unit Tests Framework
 * 
 * @category  Test
 * @package   Gizur 
 * @author    Prabhat Khera <prabhat.khera@essindia.co.in>
 * @copyright 2012 &copy; Gizur AB
 * @license   Gizur Private Licence
 * @version   0.2
 * @link      http://www.gizur.com
 */
class Girur_SOAP_API_Test extends PHPUnit_Framework_TestCase
{

    private $_credentials = Array();
    private $_url = "";
    private $_config = Array();
    private $_version = '5.4.0';
    private $_client = null;
    private $_default_charset = 'UTF-8';

    public function Girur_SOAP_API_Test()
    {
        
    }

    /**
     * Executed before every Test case
     * 
     * @return void
     */      
    
    protected function setUp()
    {
        $config = new configuration();
        $this->_config = $config->getBikerPortalCredentials();
        $this->_url = $this->_config['url'];
        $this->_credentials = $this->_config['credentials'];
        $this->_version = $this->_config['version'];
        $this->_client = new soapclient2($this->_url . "/vtigerservice.php?service=customerportal", false);
        $this->_client->soap_defencoding = $this->_default_charset;
        $this->_login();
    }
    /**
     * Tests the Login
     * 
     * @return void
     */
    
    public function testLogin(){
        
    }
    /**
     * Private funtion to login Login
     * 
     * @return void
     */
    private function _login()
    {

        global $_session_id;
        global $_customer_id;
        global $_customer_name;
        global $_customer_account_id;

        //Request parameters
        $action = 'authenticate_user';

        $invalid_credentials = Array(
            'cloud3@gizur.com' => false,
            'test@test.com' => false
        );

        $params = array('user_name' => $this->_credentials[0]['user_name'],
            'user_password' => $this->_credentials[0]['user_password'],
            'version' => "$this->_version", 'true');

        echo " calling authenticate_user " . PHP_EOL;
        $result = $this->_client->call('authenticate_user', $params, $this->_url, $this->_url);
        echo " end calling authenticate_user " . PHP_EOL;
        
        $this->assertEquals(count($result), 1, " Eaither no or more than one contacts have been sent.");
        $this->assertEquals($result[0]['id'], $this->_credentials[0]['id'], " User is invalid.");

        if (!empty($result)) {
            $_customer_id = $result[0]['id'];
            $_customer_name = $result[0]['user_name'];
            $_session_id = $result[0]['sessionid'];

            $params2 = Array('id' => $_customer_id);
            $_customer_account_id = $this->_client->call('get_check_account_id', 
                $params2, $this->_url, $this->_url);

            $params1 = Array(Array('id' => "$_customer_id",
                    'sessionid' => "$_session_id", 'flag' => "login"));

            $result2 = $this->_client->call('update_login_details', 
                $params1, $this->_url, $this->_url);
        }
        echo PHP_EOL . PHP_EOL;
    }

    /**
     * Tests the SalesOrderList
     * 
     */
    public function testSalesOrderList()
    {

        global $_session_id;
        global $_customer_id;
        global $_customer_name;
        global $_customer_account_id;

        $valid_product_ids = array(94);
        $valid_quote_ids = array(265);

        $onlymine = true;
        $action = 'get_list_preorder';

        $module = 'CikabTroubleTicket';
        $params = Array('id' => $_customer_id, 'module' => $module,
            'sessionid' => $_session_id, 'onlymine' => $onlymine);

        echo " calling $action " . PHP_EOL;
        $result = $this->_client->call($action, $params);
        echo " end calling $action " . PHP_EOL;

        $this->assertNotEmpty($result);
        $this->assertContains($result[0]['quoteid'], $valid_quote_ids);
        $this->assertContains($result[0]['productid'], $valid_product_ids);

        echo PHP_EOL . PHP_EOL;
    }

    /**
     * Tests the SalesOrderCallOffs
     * 
     */
    public function testSalesOrderCallOffs()
    {

        global $_session_id;
        global $_customer_id;
        global $_customer_name;
        global $_customer_account_id;

        $onlymine = true;
        $action = 'create_salesorder';

        $module = 'CikabTroubleTicket';

        $valid_products = array(
            array('id' => 5, 'product_name' => 'PRODUCT-1',
                'product_no' => 'PRO1', 'product_quantity' => 10)
        );
        
        foreach ($valid_products as $product) {
            $params = Array(Array(
                'id' => $_customer_id,
                'module' => $module,
                'sessionid' => $_session_id,
                'title' => 'Call off',
                'parent_id' => $_customer_id,
                'product_id' => $product['id'],
                'customer_account_id' => $_customer_account_id,
                'product_name' => $product['product_name'],
                'product_quantity' => $product['product_quantity'],
                'product_no' => $product['product_no'],
                'user_name' => $this->_credentials[0]['user_name']));
            
            echo " calling $action for product : " . json_encode($product) . PHP_EOL;
            $result = $this->_client->call($action, $params);
            echo " end calling $action " . PHP_EOL;

            $this->assertNotEmpty($result);
            $this->assertNotEmpty($result[0]['salesorder_no']);
        }
        echo PHP_EOL . PHP_EOL;
    }
    
    /**
     * Tests the SalesOrderDecrease
     * 
     */
    public function testSalesOrderDecrease()
    {

        global $_session_id;
        global $_customer_id;
        global $_customer_name;
        global $_customer_account_id;

        $onlymine = true;
        $action = 'create_custom_ticket';

        $module = 'CikabTroubleTicket';

        $valid_products = array(
            array('id' => 94, 'product_name' => '202035',
                'product_no' => 'PRO1', 'product_quantity' => 20)
        );
        
        foreach ($valid_products as $product) {
            $params = Array(Array(
                'id' => $_customer_id,
                'module' => $module,
                'sessionid' => $_session_id,
                'title' => 'Release',
                'parent_id' => $_customer_id,
                'product_id' => $product['id'],
                'customer_account_id' => $_customer_account_id,
                'product_name' => $product['product_name'],
                'product_quantity' => $product['product_quantity'],
                'product_no' => $product['product_no'],
                'user_name' => $this->_credentials[0]['user_name']));
            
            echo " calling $action for product : " . json_encode($product) . PHP_EOL;
            $result = $this->_client->call($action, $params);
            echo " end calling $action " . PHP_EOL;

            $this->assertNotEmpty($result);
            $this->assertNotEmpty($result[0]['new_ticket']['ticketid']);
        }
        echo PHP_EOL . PHP_EOL;
    }
    
    /**
     * Tests the SalesOrderIncrease
     * 
     */
    public function testSalesOrderIncrease()
    {

        global $_session_id;
        global $_customer_id;
        global $_customer_name;
        global $_customer_account_id;

        $onlymine = true;
        $action = 'create_custom_ticket';

        $module = 'CikabTroubleTicket';

        $valid_products = array(
            array('id' => 94, 'product_name' => '202035',
                'product_no' => 'PRO1', 'product_quantity' => 10)
        );
        
        foreach ($valid_products as $product) {
            $params = Array(Array(
                'id' => $_customer_id,
                'module' => $module,
                'sessionid' => $_session_id,
                'title' => 'Increase',
                'parent_id' => $_customer_id,
                'product_id' => $product['id'],
                'customer_account_id' => $_customer_account_id,
                'product_name' => $product['product_name'],
                'product_quantity' => $product['product_quantity'],
                'product_no' => $product['product_no'],
                'user_name' => $this->_credentials[0]['user_name']));
            
            echo " calling $action for product : " . json_encode($product) . PHP_EOL;
            $result = $this->_client->call($action, $params);
            echo " end calling $action " . PHP_EOL;

            $this->assertNotEmpty($result);
            $this->assertNotEmpty($result[0]['new_ticket']['ticketid']);
        }
        echo PHP_EOL . PHP_EOL;
    }

}
