<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author prabhat
 */
interface NoSQLInterface
{
    public function scan($table, $attributesToGet, $clientId);
    public function create($table, $hashkey, $params);
    public function get_item($table, $attributesToGet, $keyId, $keyValue);
}

?>
