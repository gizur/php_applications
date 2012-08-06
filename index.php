<?php

require 'lib/klein.php';
respond(function () {
    echo 'hello world!';
});
dispatch();
