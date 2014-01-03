<?php

require_once __DIR__ . '/NoSQLInterface.php';
require_once __DIR__ . '/../../aws-php-sdk/sdk.class.php';

class AmazonDynamoDBClass implements NoSQLInterface
{

    private $dynamodb = null;

    function __construct($region = 'REGION_EU_W1')
    {
        $this->dynamodb = new AmazonDynamoDB();
        $this->dynamodb->set_region(constant("AmazonDynamoDB::".$region));
        
    }

    public function scan($table, $attributesToGet, $clientId)
    {
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
        return $response;
    }

}

?>