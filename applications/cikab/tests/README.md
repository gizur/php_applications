# gizur-cron-tests

## Folder Struncture

```
tests
    |
    -- _secure
            |
            -- config.js        : Test Configuration File
            -- credentials.json : AWS credential file
    -- files                    : Root directory for node FTP server
    -- ftp.js                   : Node FTP server - node ftp.js
    -- grunt.js
    -- init.js                  : Initial test file
    -- package.json
    -- make.js                  : To configure the test environment
    -- test_cronjob1.js         : Test file for phpcronjob1
    -- test_cronjob2.js         : Test file for phpcronjob2
    -- test_cronjob3.js         : Test file for phpcronjob3

```


##Usages

```
### Steps
1. Make sure you have clonned FTP module in node_modules
git clone git@github.com:alanszlosek/nodeftpd.git
Please note, this FTP is working in active mode only with PHP.

1. Configure the environment
node make.js gc1-ireland / gc2-ireland / gc3-ireland

1. Run FTP
node ftp.js
FTP configuration
Username: user (Anyname but folder with same name must be present in ./files)
Password: 123456 (Anything but not blank)
Host: 127.0.0.1
serverpath: /

1. Modify ../php_batches/php-interfaces/config.inc.php
Update FTP details
Commentout line 53 in applications/cikab/php_batches/php-interfaces/ftp_connection.php
to disable passive mode. (Node FTP is not working other wise)

1. Run nodeunit init.js
It will give you the current status

1. Modify test_cronjob1.js / test_cronjob2.js / test_cronjob2.js
Update expected test results (Line 67, 68 and 42 respectively)

1. Run nodeunit test_cronjob1.js / test_cronjob2.js / test_cronjob2.js
```
## License
Copyright (c) 2012 Jonas Colmsjö  
Licensed under the MIT license.
