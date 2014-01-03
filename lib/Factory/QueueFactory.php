<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of QueueFactory
 *
 * @author prabhat
 */
require_once __DIR__ . '/Config.inc.php';
require_once __DIR__ . '/Queue/AmazonSQSClass.php';
require_once __DIR__ . '/Queue/RabbitMQClass.php';

class QueueFactory
{

    function __construct()
    {
        
    }

    public function getInstance()
    {
        switch (FactoryConfig::$driversInUse['Queue']) {
            case 'RabbitMQ':
                return (new RabbitMQClass());
                break;
            case 'AmazonSQS':
                return (new AmazonSQSClass());
                break;
        }
    }

}

?>
