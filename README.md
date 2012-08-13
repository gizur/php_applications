gizurcloud
==========

See the Wiki for more information: https://github.com/gizur/gizurcloud/wiki


Outline of repository
---------------------


```text
/
+ index.php					URL Router basen on klein.php
|
+ api						GizurCloud REST API. Built in Yii.
| 
|
+- lib
|  +- phpMyAdmin-3.5.2-all-languages
|  +- rest-curl
|  +- vtiger-5.4.0
|  +- yii-1.1.10.r3566
|  +- klein.php
|  +- vtwrapper-index-php
|  +- vtwrapper-config.inc.php
|
+- applications 
|  +- clab
|  |  +- trailer-app-portal			Portal for Trailer Claims Management Portal. Built in Yii.
|  |     +- config.inc.php
|  +- sample-client
|     +- sample-app
|        +- config.inc.php 
|
+- tests

```

Routing
------

index.php will perform the first level of routing:

1. It will check the API_KEY and select the appropriate config.inc.php for the application
and client (for instance applications/sample-client/sample-app/config.inc.php). 

2. Check what application to run:
 - REST API
 - Portal
 - Standard vTiger using the wrapper

3. Call the appropriate PHP function
