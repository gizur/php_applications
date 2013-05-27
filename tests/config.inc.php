<?php

class configuration {

    public function get() {
	    return array(
	       'url' => 'http://phpapplications-env-sixmtjkbzs.elasticbeanstalk.com/api/',
	       'GIZURCLOUD_API_KEY' => 'GZCLD51A309109FD3551A309109FE26',
	       'GIZURCLOUD_SECRET_KEY' => "51a309109fca42.1554128151a309109fcb20.84498075",
	       'API_VERSION' => '0.1',
	       'credentials' => array(
		    //'portal_user@gizur.com' => 'skcx0r0i',
		    //'jonas.colmsjo@gizur.com' => '0a34c625',
               'portal_user@gizur.com' => '2hxrftmd'
	       )
	    );
    }
    
    public function getBikerPortalCredentials()
    {
        return array(
            'url' => 'http://phpapplications-env-sixmtjkbzs.elasticbeanstalk.com/applications/cikab',
            'credentials' => array(
                array('user_name' => 'prabhat.khera@essindia.co.in',
                    'user_password' => 'essindia', 'id' => 2),
            ),
            'version' => '5.4.0'
        );
    }
}
