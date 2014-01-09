<?php

class FactoryConfig
{

    public static $driversInUse = array(
        'Cache' => "Redis", //Redis / Memcache
        'NoSQL' => "Redis", // Redis / AmazonDynamoDB
        'Queue' => "RabbitMQ", // RabbitMQ / AmazonSQS
        'MySQL' => "MySQL", // MySQL / RDS
    );
    
    public static $params = array(
        'Redis' => array(
            'host' => 'localhost',
            'port' => 6379
        ),
        'Memcache' => array(
            'host' => 'localhost',
            'port' => 11211
        )
    );

}

?>