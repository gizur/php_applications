gizurcloud
==========

See the Wiki for more information: https://github.com/gizur/gizurcloud/wiki


Outline of repository
---------------------


``` text

/
+- index.php					URL Router based on klein.php
|
+- api						GizurCloud REST API. Built in Yii.
| 
+- instance-configuration
|  +- gc3-<ec2 region>
|  +- gc4-ireland
|  +- gc1-ireland
|  +- gc1-<ec2 region>
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
|  +- sample-client
|     +- sample-app
|
+- tests
|
+- errors


```

Files that have configuration specific for the EC2 instance reside in the directory 

* instance-configuration/gc<gizur cloud number>-<ec2 region>


Each instance configuration consists of the following folders and files:


``` text

...
|
+- gc3-ireland
|     +- vtiger
|        +- config.inc.php


```



URL Routing
-----------

index.php will perform url routing using klein.php. The destination URL will be chosen based on both API Key and the in input URL. The API_KEY header and signature should always be sent to the destination php script as headers.

1. gizur.com/api/<sub_url> is always routed to /api/<sub_url> without any modifications and will therefore be handled by the REST PHP application (built in Yii)

2. gizur.com/clab/vtiger/index.php<parmas> is routed to /lib/vtwrapper-index.php<params> 

3. gizur.com/clab/trailer_app/<sub_url> is route to /applications/clab/trailer_app/<sub_url> amd will therefore be handeled by the Yii Portal

4. Everything else is routed to /errors/404.html


Examples

API Key: Clab_trailer_App, URL: GET gizur.com/api/tt/2234
-> API Key: Clab_trailer_App, URL: GET /api/tt/2234

URL: GET gizur.com/clab/vtiger/index.php?module='trouble tickets'
-> URL: GET /lib/vtwrapper-index.php?module='trouble tickets' Setting a HTTP header to clab (or passing clab as GET parameter)

URL: GET gizur.com/clab/trailerapp
-> URL: GET /applications/clients/clab/trailerapp

URL: GET gizur.com/whatever is routed to GET /errors/404.html

The API Key Clab_trailer_App will tyically be a numerical sequence and not text as in this example


Accounts, API Keys, API secrets and database credentials
----------------------------------------------

Each gizur.com account will have the following:

* Email adress
* Password (for future account admin console)
* Name
* Phone number
* Postal adress (street, zip, city, country ect.)
* Client id - unique client identifier. All applications for the client are located at gizur.com/<clientid>/<application id>
* API Key 1 - radnom string of 20 characters, digits, upper case only, exmaple AKIAJVRED4VYJS43ELWQ
* API Secret 1  - random string of 40 characters, digits, upper and lower case
* API Key 2 - radnom string of 20 characters, digits, upper case only, exmaple AKIAJVRED4VYJS43ELWQ
* API Secret 2  - random string of 40 characters, digits, upper and lower case
* MySQL database credentials - server, port, username, password, database name

Having two API Key/Secret pairs makes it possible to create a new API Key and secret for a account without making the old obsolete at once. It should at all times be possible to sign a request with either one of the two keys

These credentials should be stored in a AWS DynamoDB database, see http://aws.amazon.com/dynamodb/

