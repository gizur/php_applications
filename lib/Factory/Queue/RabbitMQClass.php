<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../Config.inc.php';
require_once __DIR__ . '/QueueInterface.php';

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQClass implements QueueInterface
{

    private $params;
    private $connection;
    private $channel;

    public function __construct()
    {
        $this->params = Config::$Queue['RabbitMQ'];
        $this->connection = new AMQPConnection($this->params['url'], $this->params['port'], $this->params['username'], $this->params['password']);
        $this->channel = $this->connection->channel();
    }

    public function getAllMessages($queueName)
    {
        $messages = array();
        $callback = function($msg) {
            $messages[] = $msg->body;
            echo " [x] Received ", $msg->body, "\n";
            sleep(substr_count($msg->body, '.'));
            echo " [x] Done", "\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($queueName, '', false, false, false, false, $callback);
        
        while(count($this->channel->callbacks)) {
            //$this->channel->wait();
        }
        
        return $messages;
    }

    public function publishMessage($queueName, $message)
    {
        // Declare a Queue 
        // Queue will be created if it does not exists
        $this->channel->queue_declare($queueName, false, true, false, false);

        $msg = new AMQPMessage($message, array('delivery_mode' => 2));
        $this->channel->basic_publish($msg, '', $queueName);
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }

}

?>
