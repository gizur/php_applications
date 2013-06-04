<?php

class configuration {

    public function get() {
        
	    return array(
	       'url' => 'https://api.gizur.com/api/',
	       'GIZURCLOUD_API_KEY' => 'GZCLD50694086B196F50694086B19E7',
	       'GIZURCLOUD_SECRET_KEY' => '50694086b18cd0.9497426050694086b18fa8.66729980',
	       'API_VERSION' => '0.1',
	       'credentials' => array(
		    'mobile_app@gizur.com' => 'cwvvzvb0',
		    //'jonas.colmsjo@gizur.com' => '507d136b23699',
	       )
	    );
        /*
	    return array(
	       'url' => 'https://api.gizur.com/api/',
	       'GIZURCLOUD_API_KEY' => 'GZCLD50EE9D44BEBD450EE9D44BEC50',
	       'GIZURCLOUD_SECRET_KEY' => '50ee9d44beb2d0.0165098250ee9d44beb591.45044222',
	       'API_VERSION' => '0.1',
	       'credentials' => array(
		    'demo@gizur.com' => 'demo',
		    //'jonas.colmsjo@gizur.com' => '507d136b23699',
	       )
	    );
        */
    }
    
    public function getBikerPortalCredentials()
    {
        return array(
            'url' => 'https://gizur.com/cikab/vtiger',
            'credentials' => array(
                array('user_name' => 'prabhat.khera@essindia.co.in',
                    'user_password' => '1flagyoh', 'id' => 10201),
            ),
            'version' => '5.4.0'
        );
    }
}
