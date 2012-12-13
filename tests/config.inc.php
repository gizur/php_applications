<?php

class configuration {

    public function get() {
	    return array(
	       'url' => 'https://api.gizur.com/api/index.php/api/',
	       'GIZURCLOUD_API_KEY' => 'GZCLD50694086B196F50694086B19E7',
	       'GIZURCLOUD_SECRET_KEY' => '50694086b18cd0.9497426050694086b18fa8.66729980',
	       'API_VERSION' => '0.1',
	       'credentials' => array(
		    'mobile_app@gizur.com' => 'cwvvzvb0',
		    //'jonas.colmsjo@gizur.com' => '507d136b23699',
	       )
	    );
    }
    
    public function getBikerPortalCredentials()
    {
        return array(
            'url' => 'http://bike-portal-env.elasticbeanstalk.com/applications/cikab',
            'credentials' => array(
                array('user_name' => 'jonas.colmsjo@gizur.com',
                    'user_password' => 'homeend01', 'id' => 216),
                array('user_name' => 'jonas@gizur.com',
                    'user_password' => 'homeend01', 'id' => ''),
            ),
            'version' => '5.4.0'
        );
    }
}
