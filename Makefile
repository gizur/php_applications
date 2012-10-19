all:

test:
	phpunit tests

doc:
	phpdoc -t docs -d api/
