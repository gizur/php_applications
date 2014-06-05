#!/bin/bash
echo "Hitting init.js"
nodeunit init.js

echo "Hitting test_cronjob1.js"
nodeunit test_cronjob1.js

echo "Hitting test_cronjob2.js"
nodeunit test_cronjob2.js

echo "Hitting test_cronjob3.js"
nodeunit test_cronjob3.js
