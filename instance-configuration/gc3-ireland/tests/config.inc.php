<?php

class configuration {

    public function get() {
	    return array(
	       'url' => 'http://phpapplications-env-sixmtjkbzs.elasticbeanstalk.com/api/',
	       'GIZURCLOUD_API_KEY' => 'GZCLDFC4B35B',
	       'GIZURCLOUD_SECRET_KEY' => '9b45e67513cb3377b0b18958c4de55be',
	       'API_VERSION' => '0.1',
	       'credentials' => array(
		    //'portal_user@gizur.com' => 'skcx0r0i',
		    'mobile_user@gizur.com' => 'ivry34aq',
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
