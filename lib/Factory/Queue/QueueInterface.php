<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author prabhat
 */

interface QueueInterface
{
    public function publishMessage($queueName, $message);
    public function getAllMessages($queueName);
}

?>
