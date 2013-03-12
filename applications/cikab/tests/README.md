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

Usages
### Things to do before running test script

* Run node init.js to check the current status
* Set expected results in each test case otherwise it may fail.
* Run node make.js to set the environment.
* Update local FTP details in config.inc.php at
  cikab/php_batches/php-interfaces.
* Check local FTP folder location which is being used for 
  SET files in config.js.

>>>
### Run FTP
node ftp.js
### To set the environment (It will copy all the config files)
node make.js gc1-ireland / gc2-ireland / gc3-ireland
### To run the tests
nodeunit test_cronjob1.js / test_cronjob2.js / test_cronjob2.js
>>>

>>
### Node FTP Credentials
Clone it in node_modules
git clone git@github.com:alanszlosek/nodeftpd.git

**Please note, this FTP is working in active mode only with PHP.**

**FTP configuration**

Username: user (Anyname but folder with same name must be present in ./files)
Password: 123456 (Anything but not blank)
Host: 127.0.0.1
serverpath: /

>>

## License
Copyright (c) 2012 Jonas Colmsjö  
Licensed under the MIT license.
