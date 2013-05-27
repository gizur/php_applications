<?php

global $languageArr;
$languageArr = array(
    'Trailer App Portal' => 'Trailer App Portal',
    'Date' => 'Date',
    'Time' => 'Time',
    'Account' => 'Account',
    'Contact' => 'Contact',
    'Damage Reported' => 'Damage Reported',
    'In operation' => 'In operation',
    'Damaged' => 'Damaged',
    'Ticket ID' => 'Ticket ID',
    'Trailer ID' => 'Trailer ID',
    'Status of damage' => 'Status of damage',
    'Mark damage repaired' => 'Mark damage repaired',
    'Pictures' => 'Pictures',
    'Driver caused damage' => 'Driver caused damage',
    'Type of damage' => 'Type of damage',
    'Position on trailer for damage' => 'Position on trailer for damage',
    'Upload Pictures' => 'Upload Pictures',
    'Create Date' => 'Create Date',
    'Ticket Category' => 'Ticket Category',
    'Location for damage report' => 'Location for damage report',
    /*  For Mobile app  */
    'Report Damage' => 'Report Damage',
    'Reset' => 'Reset',
    'Submit' => 'Submit',
    'Back' => 'Back',
    'Cancel' => 'Cancel',
    'Survey' => 'Survey',
    'Trailer type' => 'Trailer type',
    'Own' => 'Own',
    'Rented' => 'Rented',
    'ID' => 'ID',
    'Place' => 'Place',
    'Sealed' => 'Sealed',
    'Yes' => 'Yes',
    'No' => 'No',
    'Plates' => 'Plates',
    'Straps' => 'Straps',
    'Reported Damages' => 'Reported Damages',
    'Report a new damage' => 'Report a new damage',
    'Type' => 'Type',
    'Position' => 'Position',
    'Position on trailer' => 'Position on trailer',
    /*  Others  */
    'Total rows' => 'Total rows',
    'Rows' => 'Rows',
    'Create new Trouble ticket' => 'Create new Trouble ticket',
    'Trouble ticket' => 'Trouble ticket',
    'Trouble ticket List' => 'Trouble ticket List',
    'Login' => 'Login',
    'Logout' => 'Logout',
    'About' => 'About',
    'Welcome' => 'Welcome',
    'Status of damage' => 'Status of damage',
    'Damage' => 'Damage',
    /* Custom Variables */
    'Anteckningar' => 'Notes',
    'Damage Status' => 'Damage Status',
    'Ej påbörjat' => 'Not started',
    'Under utredning' => 'Under investigation',
    'Väntar på kompletterande uppgifter' => 'Waiting for information',
    'Ärende stängt' => 'Closed'
);

function getTranslatedString($str)
{
    global $languageArr;
    return (isset($languageArr[$str])) ? $languageArr[$str] : $str;
}

?>
