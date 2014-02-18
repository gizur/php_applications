<?php

class FactoryConfig
{

    public static $driversInUse = array(
        'Cache' => "Redis", //Redis / Memcache
        'NoSQL' => "AmazonDynamoDB", // Redis / AmazonDynamoDB
        'Queue' => "RabbitMQ", // RabbitMQ / AmazonSQS
        'MySQL' => "MySQL", // MySQL / RDS
    );
    
    public static $params = array(
        'Redis' => array(
            'host' => 'redis.gizur.local',
            'port' => 6379
        ),
        'Memcache' => array(
            'host' => 'memcache.gizur.local',
            'port' => 11211
        )
    );

}

?>