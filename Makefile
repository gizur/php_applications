all:

test:
	phpunit tests

doc:
	phpdoc -t docs/api -d api/
	docco -o docs/gizur-admin             applications/gizur-admin/js/*.js
	docco -o docs/gizur-admin/collections applications/gizur-admin/js/collections/*.js
	docco -o docs/gizur-admin/libs        applications/gizur-admin/js/libs/*.js
	docco -o docs/gizur-admin/models      applications/gizur-admin/js/models/*.js
	docco -o docs/gizur-admin/views       applications/gizur-admin/js/views/*.js



install2:
	curl -sS https://getcomposer.org/installer | php
	mv composer.phar composer
	./composer install

clean2:
	rm -rf build cache

test2:
	./vendor/bin/phpunit tests 
	./vendor/bin/phpcs --standard=Zend api/protected/**/*.php tests/*.php

lint2:
	./vendor/bin/phpcs --standard=Zend api/protected/**/*.php tests/*.php

docs2:
	php ./vendor/bin/sami.php update --force sami-config.php
