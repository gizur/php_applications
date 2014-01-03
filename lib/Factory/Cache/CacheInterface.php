<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author prabhat
 */
interface CacheInterface
{
    public function set($key, $value);
    public function get($key);
    public function isExist($key);
}

?>
