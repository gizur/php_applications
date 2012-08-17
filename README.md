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
|
+- errors

```


URL Routing
-----------

index.php will perform url routing using klein.php. The destination URL will be chosen based on both API Key and the in input URL. The API_KEY header and signature should always be sent to the destination php script as headers.

1. gizur.com/api/<sub_url> is always routed to /api/<sub_url> without any modifications and will therefore be handled by the REST PHP application (built in Yii)

2. gizur.com/vtiger/index.php<parmas> is routed to /lib/vtwrapper-index.php<params> 

3. gizur.com/clab/trailer_app/<sub_url> is route to /applications/clab/trailer_app/<sub_url> amd will therefore be handeled by the Yii Portal

4. Everything else is routed to /errors/404.html


Examples

API Key: Clab_trailer_App, URL: GET gizur.com/api/tt/2234
-> API Key: Clab_trailer_App, URL: GET /api/tt/2234

URL: GET gizur.com/vtiger/index.php?module='trouble tickets'
-> URL: GET /lib/vtwrapper-index.php?module='trouble tickets'

URL: GET gizur.com/clab/trailerapp
-> URL: GET /applications/clients/clab/trailerapp

URL: GET gizur.com/whatever is routed to GET /errors/404.html

The API Key Clab_trailer_App will tyically be a numerical sequence and not text as in this example
