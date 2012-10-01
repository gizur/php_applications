update vtiger_ws_operation_seq set id = id + 1;

INSERT INTO `vtiger_ws_operation`(`operationid`, `name`, `handler_path`, `handler_method`, `type`, `prelogin`) VALUES 
((select id from vtiger_ws_operation_seq),'logincustomer','include/Webservices/LoginCustomer.php','vtws_logincustomer','POST',1);

INSERT INTO `vtiger_ws_operation_parameters`(`operationid`, `name`, `type`, `sequence`)
VALUES ((select id from vtiger_ws_operation_seq),'username','string',1);

INSERT INTO `vtiger_ws_operation_parameters`(`operationid`, `name`, `type`, `sequence`)
VALUES ((select id from vtiger_ws_operation_seq),'password','string',2);


update vtiger_ws_operation_seq set id = id + 1;

INTO `vtiger_ws_operation`(`operationid`, `name`, `handler_path`, `handler_method`, `type`, `prelogin`) VALUES 
((select id from vtiger_ws_operation_seq),'relatetroubleticketdocument','include/Webservices/RelateTroubleTicketDocument.php','vtws_relatetroubleticketdocument','POST',0);

INSERT INTO `vtiger_ws_operation_parameters`(`operationid`, `name`, `type`, `sequence`) 
VALUES ((select id from vtiger_ws_operation_seq),'crmid','string',1);

INSERT INTO `vtiger_ws_operation_parameters`(`operationid`, `name`, `type`, `sequence`) 
VALUES ((select id from vtiger_ws_operation_seq),'notesid','string',2);


update vtiger_ws_operation_seq set id = id + 1;

INTO `vtiger_ws_operation`(`operationid`, `name`, `handler_path`, `handler_method`, `type`, `prelogin`) VALUES 
((select id from vtiger_ws_operation_seq),'gettroubleticketdocumentfile','include/Webservices/RelateTroubleTicketDocument.php','vtws_gettroubleticketdocumentfile','GET',0);

INSERT INTO `vtiger_ws_operation_parameters`(`operationid`, `name`, `type`, `sequence`) 
VALUES ((select id from vtiger_ws_operation_seq),'notesid','string',1);

update vtiger_ws_operation_seq set id = id + 1;

INTO `vtiger_ws_operation`(`operationid`, `name`, `handler_path`, `handler_method`, `type`, `prelogin`) VALUES 
((select id from vtiger_ws_operation_seq),'getrelatedtroubleticketdocument','include/Webservices/RelateTroubleTicketDocument.php','vtws_getrelatedtroubleticketdocument','GET',0);

INSERT INTO `vtiger_ws_operation_parameters`(`operationid`, `name`, `type`, `sequence`) 
VALUES ((select id from vtiger_ws_operation_seq),'crmid','string',1);

update vtiger_ws_operation_seq set id = id + 1;

INTO `vtiger_ws_operation`(`operationid`, `name`, `handler_path`, `handler_method`, `type`, `prelogin`) VALUES 
((select id from vtiger_ws_operation_seq),'changepw','include/Webservices/LoginCustomer.php','vtws_logincustomer','POST',0);

INSERT INTO `vtiger_ws_operation_parameters`(`operationid`, `name`, `type`, `sequence`) 
VALUES ((select id from vtiger_ws_operation_seq),'username','string',1);

INSERT INTO `vtiger_ws_operation_parameters`(`operationid`, `name`, `type`, `sequence`) 
VALUES ((select id from vtiger_ws_operation_seq),'oldpassword','string',2);

INSERT INTO `vtiger_ws_operation_parameters`(`operationid`, `name`, `type`, `sequence`) 
VALUES ((select id from vtiger_ws_operation_seq),'newpassword','string',3);

update vtiger_ws_operation_seq set id = id + 1;

INTO `vtiger_ws_operation`(`operationid`, `name`, `handler_path`, `handler_method`, `type`, `prelogin`) VALUES 
((select id from vtiger_ws_operation_seq),'resetpassword','include/Webservices/LoginCustomer.php','vtws_logincustomer','POST',1);

INSERT INTO `vtiger_ws_operation_parameters`(`operationid`, `name`, `type`, `sequence`) 
VALUES ((select id from vtiger_ws_operation_seq),'username','string',1);
