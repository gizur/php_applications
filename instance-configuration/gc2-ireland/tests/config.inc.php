<?php

class configuration {

    public function get() {
	    return array(
	       'url' => 'https://c2.gizur.com/api/',
	       //'url' => 'https://phpapplications3-env-tk3itzr6av.elasticbeanstalk.com/api/index.php/api/',
	       'GIZURCLOUD_API_KEY' => 'GZCLD50826A54755AB50826A5475624',
	       'GIZURCLOUD_SECRET_KEY' => '50826a54755009.5822592450826a54755292.56509362',
	       'API_VERSION' => '0.1',
	       'credentials' => array(
		    //'portal_user@gizur.com' => '2hxrftmd',
		    'mobile_user@gizur.com' => 'ivry34aq',
	       ),
           'clientid' =>  'clab'
	    );
    }
    
    public function getBikerPortalCredentials()
    {
        return array(
            'url' => 'https://c2.gizur.com/cikab/vtiger',
            'credentials' => array(
                array('user_name' => 'gizur-ess-prabhat@gizur.com',
                    'user_password' => '1flagyoh', 'id' => 10201),
            ),
            'version' => '5.4.0'
        );
    }
}
