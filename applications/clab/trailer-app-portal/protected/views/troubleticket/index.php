<!-- 
 * vtigerCRM vtyiiCPng - web based vtiger CRM Customer Portal
 * Copyright (C) 2011 Opencubed shpk: JPL TSolucio, S.L./StudioSynthesis, S.R.L.
 *
 * This file is part of vtyiiCPng.
 *
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0 ("License")
 * You may not use this file except in compliance with the License
 * The Original Code is:  Opencubed Open Source
 * The Initial Developer of the Original Code is Opencubed.
 * Portions created by Opencubed are Copyright (C) Opencubed.
 * All Rights Reserved.
 *
 * vtyiiCPng is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
-->

<?PHP

 ///// Added by Anil Singh 23Dec11
$helpdeskrmfieldskey=array('5','4');
///////
$list = '';
$closedlist = '';
if($result == '') {
	$list .= '<tr><td>';
	$list .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">';
	$list .= '<tr><td class="pageTitle">LBL_NONE_SUBMITTED</td></tr></table>';
	$list .= '</td></tr>';
} else {

	$header = $result[0]['head'][0];
	$data = $result[1]['data'];
		//////////////////////////////////////////////  Added By Anil Singh  For Remove field in above array.
for($i=0;$i<=count($helpdeskrmfieldskey);$i++)
	{
	 //$key=remove_item_by_value($header,$helpdeskrmfields[$i],'fielddata');
	 if(array_key_exists($helpdeskrmfieldskey[$i], $header))
	 {
	  unset($header[$helpdeskrmfieldskey[$i]]);
	  $header=array_values($header);
	  for($j=0;$j<=count($data);$j++)
      {	  
	  unset($data[$j][$helpdeskrmfieldskey[$i]]);
	  $data=array_values($data);
	   }
	   }
	 }
	 //////////////////////////////////////////////////// End
	 $nooffields = count($header);
	 $nooffields_data = count($data[0]);
	 $rowcount = count($data);
	$showstatus = $_REQUEST['showstatus'];
		if($showstatus != '' && $rowcount >= 1) {
			$list .= '<tr><td colspan="1"><div id="scrollTab">';
			$list .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">';
			$list .= '<tr><td class="mnu">'.$showstatus.'LBL_TICKETS</td></tr></table>';
			$list .= '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
			$list .= '<tr>';
	
			for($i=0; $i<$nooffields; $i++)
			{
				$header_value = $header[$i]['fielddata'];
				$list .= '<td class="detailedViewHeader" align="center">'.$header_value.'</td>';
			}
			$list .= '</tr>';
	
			$ticketexist = 0;
			for($i=0;$i<count($data);$i++)
			{		
				$ticketlist = '';
		
				if ($i%2==0)
					$ticketlist .= '<tr class="dvtLabel">';
				else
					$ticketlist .= '<tr class="dvtInfo">';
			
				$ticket_status = '';
				for($j=0; $j<$nooffields; $j++) {			
					$ticketlist .= '<td>'.$data[$i][$j]['fielddata'].'</td>';
					if ($header[$j]['fielddata'] == 'Status') {
						$ticket_status = $data[$i][$j]['fielddata'];
					}
				}
			$ticketlist .= '</tr>';
	
			if($ticket_status == $showstatus){
				$list .= $ticketlist; 
				$ticketexist++;
			}		
		}
		if($ticketexist == 0)
		{
			$list .= '<tr><td>&nbsp;</td></tr><tr><td class="pageTitle">LBL_NONE_SUBMITTED</td></tr>';
		}
	
		$list .= '</table>';
	
	}
	else {
		$list .= '<tr><td colspan="2"><div id="scrollTab">';
		$list .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">';
		$list .= '<tr><td class="mnu">LBL_MY_OPEN_TICKETS</td></tr></table>';
		$list .= '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
		$list .= '<tr>';
	
		$closedlist .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">';
		$closedlist .= '<tr><td class="mnu">LBL_CLOSED_TICKETS</td></tr></table>';
		$closedlist .= '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
		$closedlist .= '<tr>';
		$closeheaderlist="";
		for($i=0; $i<$nooffields; $i++)
		{
		   $header_value = $header[$i]['fielddata'];
			if(!empty($header_value))  //// Added By Anil Singh
			{
			$headerlist .= '<td class="detailedViewHeader" align="center">'.$header_value.'</td>';
			}
		}
		///////////////////////////////////////////////////  For Remove Releted to in close tag
		for($i=0; $i<$nooffields; $i++)
		{
		   	unset($header[2]);
			$closeheader_value = $header[$i]['fielddata'];
			if(!empty($closeheader_value))  //// Added By Anil Singh
			{
			$closeheaderlist .= '<td class="detailedViewHeader" align="center">'.$closeheader_value.'</td>';
			}
			
		}
		////////////////////////////////////////////////////////////// End
		$header=array_values($header);
		$headerlist .= '</tr>';
		$list .= $headerlist;
		$closedlist .= $closeheaderlist;
	 for($i=0;$i<count($data);$i++)
		{
			$ticketlist = '';
			
			if ($i%2==0)
				$ticketlist .= '<tr class="dvtLabel">';
			else
				$ticketlist .= '<tr class="dvtInfo">';
			
			$ticket_status = '';
			$ticketlist_reverse = '';
			for($j=$nooffields_data-1; $j>=0; $j--) {

				//////////////////////////////////////////////// End
				if ($j==$nooffields_data-1) {
					$ticket_status = $data[$i][$j]['fielddata'];
				}
				if(!empty($data[$i][$j]['fielddata']))
                 {	
				if(!($ticket_status == 'Closed' and $j==2))				 
				$ticketlist_reverse = '<td>'.$data[$i][$j]['fielddata'].'</td>' . $ticketlist_reverse;
				}				
			}
			$ticketlist .= $ticketlist_reverse;
			$ticketlist .= '</tr>';
	
			if($ticket_status == 'Closed')
				$closedlist .= $ticketlist;
			elseif($ticket_status != '')
				$list .= $ticketlist;
		}	
	
		$list .= '</table>';
		$closedlist .= '</table>';
	
		$closedlist .= '</div></td></tr>';
	
		$list .= '<br><br>'.$closedlist;
	}
}
echo $list;

?>
