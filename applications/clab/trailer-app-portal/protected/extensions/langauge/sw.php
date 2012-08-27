<?php
global $langaugeArr;
$langaugeArr = array (
'Survey' => 'Surray',
'Damage' => 'DDDMag'
);

function getTranslatedString($str)
{
	global $langaugeArr;
	return (isset($langaugeArr[$str]))?$langaugeArr[$str]:$str;
}
?>
