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
