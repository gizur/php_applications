<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header$
 * Description:  Defines the Account SugarBean Account entity with the necessary
 * methods and variables.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
include_once('config.php');
require_once('include/logging.php');
require_once('data/SugarBean.php');
require_once('include/utils/utils.php');
require_once('user_privileges/default_module_view.php');


// Account is used to store vtiger_account information.
class CikabTroubleTicket extends CRMEntity {
	var $log;
	var $db;
		
	var $table_name = "vtiger_quotes";
	var $table_index= 'quoteid';
	var $tab_name = Array('vtiger_crmentity','vtiger_quotes','vtiger_salesorder','vtiger_inventoryproductrel','vtiger_products');
	var $tab_name_index = Array('vtiger_crmentity'=>'crmid','vtiger_quotes'=>'quoteid','vtiger_salesorder'=>'salesorderid','vtiger_inventoryproductrel'=>'id','vtiger_products'=>'productid');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_quotescf', 'quoteid');
	var $entity_table = "vtiger_crmentity";
	
	var $billadr_table = "vtiger_quotesbillads";

	var $object_name = "CikabTroubleTicket";

	var $new_schema = true;

	var $column_fields = Array();

	var $sortby_fields = Array('subject','crmid','smownerid','accountname','lastname');		

	// This is used to retrieve related vtiger_fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'smownerid', 'opportunity_id', 'case_id', 'contact_id', 'task_id', 'note_id', 'meeting_id', 'call_id', 'email_id', 'parent_name', 'member_id' );

	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
				//'Quote No'=>Array('crmentity'=>'crmid'),
				// Module Sequence Numbering
				'Product Id'=>Array('products'=>'productid'),
				'Product Name'=>Array('products'=>'productname'),
				'Quantity'=>Array('inventoryproductrel'=>'quantity'), 
				 );
	
	var $list_fields_name = Array(
				        'Product Id'=>'productid',
				        'Product Name'=>'productname',
				        'Quantity'=>'quantity',
				          );
	var $list_link_field= 'subject';

	var $search_fields = Array(
				'Quote No'=>Array('quotes'=>'quote_no'),
				'Subject'=>Array('quotes'=>'subject'),
				'Account Name'=>Array('quotes'=>'accountid'),
				'Quote Stage'=>Array('quotes'=>'quotestage'), 
				);
	
	var $search_fields_name = Array(
					'Quote No'=>'quote_no',
				        'Subject'=>'subject',
				        'Account Name'=>'account_id',
				        'Quote Stage'=>'quotestage',
				      );

	// This is the list of vtiger_fields that are required.
	var $required_fields =  array("accountname"=>1);

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'crmid';
	var $default_sort_order = 'ASC';
	//var $groupTable = Array('vtiger_quotegrouprelation','quoteid');
	
	var $mandatory_fields = Array('subject','createdtime' ,'modifiedtime');
	
	/**	Constructor which will set the column_fields in this object
	 */	
    
    function CikabTroubleTicket() {
        $this->log = LoggerManager::getLogger('CikabTroubleTicket');
        $this->db = PearDatabase::getInstance();
        $this->log->debug("Entering CikabTroubleTicket() method ...");
        $this->log->debug("Exiting CikabTroubleTicket() method ...");
    }
}

?>
