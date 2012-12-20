<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
//error_reporting(E_ALL);
require_once 'modules/Emails/mail.php';
require_once 'modules/HelpDesk/HelpDesk.php';
require_once 'modules/CikabTroubleTicket/CustomConfig.php';

class CustomHelpDeskHandler extends VTEventHandler
{

    public $custom_fields = array();

    public function CustomHelpDeskHandler()
    {
        $this->custom_fields = CustomConfig::$custom_fields;
    }

    function handleEvent($eventName, $entityData)
    {
        global $log, $adb;
        $log->debug("IN CustomHelpDeskHandler");
        if ($eventName == 'vtiger.entity.aftersave' && $entityData->isNew()) {

            $log->debug("IN CustomHelpDeskHandler :: vtiger.entity.aftersave with isNew");
            $moduleName = $entityData->getModuleName();
            if ($moduleName == 'HelpDesk') {
                $ticketId = $entityData->getId();
                /*
                 * Increase/Decrease
                 */
                $cf_642 = $entityData->focus->column_fields[$this->custom_fields['increase_decrease']];
                /*
                 * Requested Date
                 */
                $cf_644 = $entityData->focus->column_fields[$this->custom_fields['requested_date']];
                /*
                 * Product Quantity
                 */
                $cf_645 = $entityData->focus->column_fields[$this->custom_fields['product_quantity']];
                /*
                 * Product Id
                 */
                $product_id = $entityData->focus->column_fields['product_id'];

                /*
                 * Validate ticket fields before executing the code.
                 */
                if (!empty($product_id) &&
                    in_array($cf_642, array('Increase', 'Decrease')) &&
                    !empty($cf_644) && !empty($cf_645)) {

                    $log->debug("IN CustomHelpDeskHandler :: 
                        calling _operationDecreaseOrIncrease
                        ($ticketId, $product_id, $cf_645, $cf_642, 1);");
                    /* If request is for Decrease */
                    $this->_operationDecreaseOrIncrease($ticketId, $product_id, $cf_645, $cf_642, 1);
                }
            }
        }
    }

    function _operationDecreaseOrIncrease($ticketId, $product_id, $cf_645, $type, $count = 1)
    {
        global $log, $adb;
        $log->debug("IN _operationDecreaseOrIncrease($ticketId, $product_id, $cf_645, $type, $count);");
        /*
         * GET THE FIRST TICKET WITH INCREASE/DECREASE REQUEST
         * FOR SAME PRODUCT.
         */
        $opp_type = null;
        if ($type == 'Increase')
            $opp_type = 'Decrease';            
        else
            $opp_type = 'Increase';

        $result = $this->getFirstTicketByProductId($product_id, $opp_type);
        
        $quantity = $result->fields[$this->custom_fields['product_quantity']];
        if (!empty($quantity)) {
            /*
             * IF QUANTITY EQUALS TO THE ORDERED QUANTITY 
             */
            if ($quantity == $cf_645) {
                $this->closeTroubleTicket($result->fields['ticketid']);
                /*
                 * CLOSING THE CURRENT CREATED TICKET 
                 */
                $this->closeTroubleTicket($ticketId);
            } elseif ($quantity > $cf_645) {
                /*
                 * CLOSING THE FETCHED TICKET 
                 */
                $this->closeTroubleTicket($result->fields['ticketid']);
                $new_quantity = $quantity - $cf_645;
                /*
                 * CLOSING THE CURRENT CREATED TICKET 
                 */
                $this->closeTroubleTicket($ticketId);
                /*
                 * CREATE A NEW TICKET WITH NEW QUANTITY 
                 */
                $this->cloneATicketWithNewQuantity($result->fields['ticketid'], $new_quantity);
            } elseif ($quantity < $cf_645) {
                /*
                 * THIS CONDITION WILL CALLED WHEN REQUESTED DECREASE QUANTITY IS 
                 * GREATER THAN THE FETCHED TICKET QUANTITY.
                 */
                $this->closeTroubleTicket($result->fields['ticketid']);
                /*
                 * CLOSING THE CURRENT CREATED TICKET 
                 */
                $this->closeTroubleTicket($ticketId);
                /*
                 * NOW RE-CALL THE SAME FUNCTION TO MATCH FOR
                 * THE BALANCE QUANTITY.
                 */
                $new_quantity = $cf_645 - $quantity;
                $this->_operationDecreaseOrIncrease($ticketId, $product_id, $new_quantity, $type, ++$count);
            }
        } else {
            if ($count > 1) {
                /*
                 * CREATE A NEW TICKET WITH NEW QUANTITY 
                 */
                $this->cloneATicketWithNewQuantity($ticketId, $cf_645);
            }
        }
    }

    function closeTroubleTicket($id)
    {
        global $log, $adb;
        
        $result_sts = $this->getTicketByTicketId($id);
        
        $log->debug("TICKET STATUS TO CLOSE : " . json_encode($result_sts));
        
        if ($result_sts->fields['status'] != 'Closed') {
            $sql = "UPDATE vtiger_troubletickets 
            SET vtiger_troubletickets.status = 'Closed'
            WHERE ticketid = ?";
            $result = $adb->pquery($sql, array($id));
            if ($result) {
                /*
                 * UPDATE QUOTE
                 */
                $parent_id = $result_sts->fields['parent_id'];
                $quantity = $result_sts->fields[$this->custom_fields['product_quantity']];
                $in_de = $result_sts->fields[$this->custom_fields['increase_decrease']];
                $product_id = $result_sts->fields['product_id'];
                
                $this->increaseOrDecreaseFirstQuoteByParentId($parent_id, $quantity, $in_de, $product_id);
                $log->debug("CLOSED TICKET : $id");
                return true;
            } else {
                $log->debug("FAILED CLOSING TICKET : $id");
                return false;
            }
        } else {
            return true;
        }
    }

    function cloneATicketWithNewQuantity($ticketId, $new_quantity)
    {
        global $log, $adb;
        $log->debug("CLONING TICKET : $ticketId WITH QUANTITY $new_quantity.");

        $_REQUEST['file'] = 'ListView';

        $ticket = $this->getTicketByTicketId($ticketId);

        $new_ticket = new HelpDesk();

        $new_ticket->column_fields['ticket_no'] = NULL;
        $new_ticket->column_fields['assigned_user_id'] = $ticket->fields['smownerid'];
        $new_ticket->column_fields['parent_id'] = $ticket->fields['parent_id'];
        $new_ticket->column_fields['ticketpriorities'] = $ticket->fields['priority'];
        $new_ticket->column_fields['product_id'] = $ticket->fields['product_id'];
        $new_ticket->column_fields['ticketseverities'] = $ticket->fields['severity'];
        $new_ticket->column_fields['ticketstatus'] = 'Open';
        $new_ticket->column_fields['ticketcategories'] = $ticket->fields['category'];
        $new_ticket->column_fields['update_log'] = $ticket->fields['update_log'];
        $new_ticket->column_fields['hours'] = $ticket->fields['hours'];
        $new_ticket->column_fields['days'] = $ticket->fields['days'];
        $new_ticket->column_fields['createdtime'] = ''; //$ticket->fields['createdtime'];
        $new_ticket->column_fields['modifiedtime'] = ''; //$ticket->fields['modifiedtime'];
        $new_ticket->column_fields['ticket_title'] = $ticket->fields['title'];
        $new_ticket->column_fields['description'] = $ticket->fields['description'];
        $new_ticket->column_fields['solution'] = $ticket->fields['solution'];
        $new_ticket->column_fields['comments'] = 'Auto creted by system.';
        $new_ticket->column_fields['modifiedby'] = $ticket->fields['modifiedby'];
        $new_ticket->column_fields['from_portal'] = $ticket->fields['from_portal'];
        $new_ticket->column_fields[$this->custom_fields['increase_decrease']] = $ticket->fields[$this->custom_fields['increase_decrease']];
        $new_ticket->column_fields[$this->custom_fields['requested_date']] = $ticket->fields[$this->custom_fields['requested_date']];
        $new_ticket->column_fields[$this->custom_fields['product_quantity']] = $new_quantity;


        $new_ticket->save("HelpDesk");
        
        /*
         * Incase of creating a new ticket
         * Increase a quote by new quantity.
         */
        if($ticket->fields[$this->custom_fields['increase_decrease']] == 'Increase')
            $opt_type = 'Decrease';
        else
            $opt_type = 'Increase';
        
        $this->increaseOrDecreaseFirstQuoteByParentId($ticket->fields['parent_id'], 
            $new_quantity, $opt_type, $ticket->fields['product_id']);
        
        $log->debug("Cloned Ticket $ticketId with ID : " . $new_ticket->id);
    }

    function getFirstTicketByProductId($product_id, $type)
    {
        global $log, $adb;
        $log->debug("Fetch Ticket with product id : $product_id and Type : $type");
        $query = "SELECT 
                    vtiger_crmentity.*,
                    vtiger_troubletickets.*,
                    vtiger_ticketcf.*
                FROM
                    vtiger_troubletickets
                        INNER JOIN
                    vtiger_ticketcf ON vtiger_troubletickets.ticketid = vtiger_ticketcf.ticketid
                        INNER JOIN
                    vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid
                        LEFT JOIN
                    vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
                        LEFT JOIN
                    vtiger_groups ON vtiger_crmentity.smownerid = vtiger_groups.groupid
                WHERE
                    vtiger_crmentity.deleted = 0 AND 
                    vtiger_troubletickets.ticketid > 0 AND
                    vtiger_troubletickets.status = 'Open' AND
                    vtiger_troubletickets.product_id = ? AND 
                    vtiger_troubletickets.product_id IS NOT NULL AND
                    vtiger_ticketcf.{$this->custom_fields['increase_decrease']} IN ( ? ) AND
                    vtiger_ticketcf.{$this->custom_fields['product_quantity']} > 0
                ORDER BY vtiger_ticketcf.{$this->custom_fields['requested_date']} ASC, 
                vtiger_troubletickets.ticketid ASC LIMIT 1";
        return $result = $adb->pquery($query, array($product_id, $type));
    }

    function getTicketByTicketId($id)
    {
        global $log, $adb;
        $log->debug("Fetch Ticket id : $id");
        $query = "SELECT 
                    vtiger_crmentity.*,
                    vtiger_troubletickets.*,
                    vtiger_ticketcf.*
                FROM
                    vtiger_troubletickets
                        INNER JOIN
                    vtiger_ticketcf ON vtiger_troubletickets.ticketid = vtiger_ticketcf.ticketid
                        INNER JOIN
                    vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid
                        LEFT JOIN
                    vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
                        LEFT JOIN
                    vtiger_groups ON vtiger_crmentity.smownerid = vtiger_groups.groupid
                WHERE
                    vtiger_crmentity.deleted = 0 AND 
                    vtiger_troubletickets.ticketid = ?";
        return $result = $adb->pquery($query, array($id));
    }

    function getQuoteByProductId($product_id)
    {
        global $log, $adb;
        $log->debug("Fetch Quote by Product Id : $product_id");
        $query = "SELECT
                i.productid,
                i.id,
                i.quantity,
                p.product_no productno,
                p.productname,
                p.productsheet,
                i.quantity
            FROM
                vtiger_inventoryproductrel i
                    LEFT JOIN
                vtiger_quotes q ON i.id = q.quoteid
                    INNER JOIN
                vtiger_products p ON p.productid = i.productid
                    INNER JOIN
                vtiger_crmentity CE ON CE.crmid = i.id
            WHERE
                CE.deleted = 0 AND 
                q.quotestage NOT IN ('Rejected' , 'Delivered', 'Closed') AND
                p.discontinued = 1 AND
                i.productid = ?
            ORDER BY i.id ASC LIMIT 1;";
        return $result = $adb->pquery($query, array($product_id));
    }

    function increaseOrDecreaseFirstQuoteByParentId($parent_id, 
        $quantity, 
        $in_de,
        $product_id)
    {
        global $log, $adb;
        $log->debug("In increaseOrDecreaseFirstQuoteByParentId($parent_id, 
        $quantity, 
        $in_de, $product_id)");
        $log->debug("Fetch Quote by Parent Id : $parent_id");
        $query = "SELECT
                i.productid,
                i.id,
                i.quantity,
                p.product_no,
                p.productname,
                p.productsheet,
                i.quantity,
                q.accountid,
                q.contactid
            FROM
                vtiger_inventoryproductrel i
                    LEFT JOIN
                vtiger_quotes q ON i.id = q.quoteid
                    INNER JOIN
                vtiger_products p ON p.productid = i.productid
                    INNER JOIN
                vtiger_crmentity CE ON CE.crmid = i.id
            WHERE
                CE.deleted = 0 AND 
                q.quotestage NOT IN ('Rejected' , 'Delivered', 'Closed') AND
                i.productid = ? AND
                p.discontinued = 1 AND
                (
                    q.accountid IN (select c.accountid 
                        from vtiger_contactdetails c
                        where c.contactid = ?
                    ) 
                    OR 
                    q.contactid IN (
                        SELECT c1.contactid 
                        FROM vtiger_contactdetails c1
                        WHERE c1.accountid = (
                            select c.accountid 
                            from vtiger_contactdetails c
                            where c.contactid = ( ? )
                        )
                    ) 
                )
            ORDER BY i.id ASC LIMIT 1";
        $result = $adb->pquery($query, array($product_id, $parent_id, $parent_id));
        if ($result) {
            $new_quantity = 0;
            if($in_de == 'Increase')
                $new_quantity = $result->fields['quantity'] + $quantity;
            else
                $new_quantity = $result->fields['quantity'] - $quantity;
            $query_up = "UPDATE vtiger_inventoryproductrel i
                SET i.quantity = ? WHERE
                i.id = ? AND productid = ?";
            $adb->pquery($query_up, array($new_quantity, $result->fields['id'], $product_id));
        }
    }

}

?>