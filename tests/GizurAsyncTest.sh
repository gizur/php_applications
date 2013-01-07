#!/bin/bash
echo "Running parallel unit tests..."
for i in {1..20}
do
   echo "Test run $i"
   phpunit --filter GetAssetList GizurRESTAPITest.php &
done
