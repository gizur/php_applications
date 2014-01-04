<?php

require_once __DIR__ . '/NoSQLInterface.php';
require_once __DIR__ . '/../../aws-php-sdk/sdk.class.php';

class AmazonDynamoDBClass implements NoSQLInterface
{

    private $dynamodb = null;
    private $region = 'REGION_EU_W1';

    function __construct()
    {       
        
    }

    public function scan($table, $attributesToGet, $clientId)
    {
        $this->dynamodb = new AmazonDynamoDB();
        $this->dynamodb->set_region(constant("AmazonDynamoDB::".$this->region));
        $response = $this->dynamodb->scan(array(
            'TableName'       => $table,
            'AttributesToGet' => $attributesToGet,
            'ScanFilter'      => array(
                'clientid' => array(
                    'ComparisonOperator' => AmazonDynamoDB::CONDITION_EQUAL,
                    'AttributeValueList' => array(
                        array( AmazonDynamoDB::TYPE_STRING => $clientId )
                    )
                ),
            )
        ));
        
        $result = array();
        
        if ($response->body->Count > 0) {  
            $arr = get_object_vars($response->body->Items);
            
            foreach($attributesToGet as $key) {
                $ar = get_object_vars($arr[$key]);
                $result[$key] = $ar[AmazonDynamoDB::TYPE_STRING];
            }
        }
        return $result;
    }

}

?>