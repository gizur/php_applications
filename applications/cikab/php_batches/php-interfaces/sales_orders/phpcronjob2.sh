#!/usr/bin/php
<?php
/**
 * 
 * 
 * created date : 05/05/2012
 * created by : anil singh
 * @author anil singh <anil-singh@essindia.co.in>
 * flow : the basic flow of this page is extract all salesorder record 
 * from the interface table and create file set. 
 * if any issues in the query then auto roleback.
 * modify date : 05/05/2012
 */
/**
 * using of $salesordertable
 * the purpuse of using first query '$salesordertable' of the all fetch 
 * record into the master table when status will be created 
 * and approved.
 */
/**
 * using of query $salesordermaptable into the first while loop
 * the purpose of using of the query '$salesordermaptable' when fetched 
 * saleoredrid and saleorder_no above query from the master table
 * and get product name,productid,accountname,duedate,product quantity 
 * from above saleorderid.
 * we know that one salesorderid or salesorder_no has been multiple product 
 * or items.
 * if any issues in related saleorderid query then it will be rollback 
 * all record(related to saleorderid). 
 * after rollback we can see error report into the syslog file.
 * path : /home/var/log/syslog
 */

/**
 * Contains all environemnt dependent configuration, database, MQ,ftp logins etc.
 */
require_once __DIR__ . '/../config.inc.php';

/**
 * include SQS instance file 
 */
require_once __DIR__ . '/../config.sqs.inc.php';

/**
 * for use databse connection
 */
require_once __DIR__ . '/../config.database.php';

/**
 * set autocommit off
 */
@mysql_query("set autocommit = 0");

/**
 * set satrt trasaction on
 */
@mysql_query("start transaction");

/**
 * ready state of syslog
 */
openlog("phpcronjob2", LOG_PID | LOG_PERROR, LOG_LOCAL0);

$salesordertable = "select accountname 
                  from `" . $dbconfig_integration['db_name'] . "`.`salesorder_interface` 
                  where sostatus in ('created','approved') group by accountname limit 0," . $dbconfig_batchvaliable['batch_valiable'] . "";
$exequery = @mysql_query($salesordertable, $obj1->link);

/**
 *  query not execute successfull. write error in  log file 
 */
if (!$exequery) {
    $message = mysql_error();
    mysql_close($obj1->link);
    $access = date("y/m/d h:i:s");
    syslog(LOG_WARNING, "" . $message . " at " . $access . "");
}

$numrows = @mysql_num_rows($exequery);

if (!empty($numrows)) {
    $mysqlerror = "";
    $findproblemsalesorder = array();
    while ($interfaceorderid = mysql_fetch_array($exequery)) {
        /**
         * get details data into interface table with related saleorder_no  
         */
        $createDate = date("YmdHi");
        $ourfilenamedir = __DIR__ . "/cronsetfiles/";

        if (!@is_dir($ourfilenamedir)) {
            @mkdir($ourfilenamedir, 0777);
        }

        $filename = "SET.GZ.FTP.IN.BST." . $createDate . "." . $interfaceorderid['accountname'] . "";
        $ourfilename = $ourfilenamedir . $filename;

        $salesorderallorderno = " select * from `" . $dbconfig_integration['db_name'] . "`.`salesorder_interface` " .
                    "where accountname='" . $interfaceorderid['accountname'] . "' AND sostatus in ('created','approved')";
        $findproblemsalesorderid = "";
        $queryerror = "";
        $allok = true;
        $string = "";
        $leadzero = "";
        $accountlenth = "";
        $productnamearray = array();
        $productaccountarray = array();
        $productlength = "";
        $leadzeroproduct = "";
        $productquantitylength = "";
        $filenotwritemsg = "";
        $leadzeroproductquantity = "";

        $executequery = @mysql_query($salesorderallorderno, $obj1->link);

        if (!$executequery) {
            /**
             *   write which type error into the query 
             */
            $queryerror = mysql_error();
            /**  set $allok is false
             */
            $allok = false;
        } else {

            while ($intfacerows = mysql_fetch_array($executequery)) {

                /**  write data in file
                 */
                $fh = fopen($ourfilename, 'w') or die("can't open file");

                /**
                 * for check duplicate product and write productname in set file with+
                 */
                if (!in_array($intfacerows['productname'], $productnamearray)) {
                    $productlength = strlen($intfacerows['productname']);
                    $productquantitylength = strlen($intfacerows['productquantity']);

                    /**
                     * for product name length count if product name length less the 6 then auto add zero berfor the product name
                     */
                    if ($productlength < 6) {
                        $leadzeroproduct = leadingzero($productlength);
                    }

                    /**
                     * for product quantity length count if product quantity length less the 3 then auto add zero berfor the product quantity
                     */
                    if ($productquantitylength < 3) {
                        $leadzeroproductquantity = leadingzero(3, $productquantitylength);
                    }

                    $multiproduct[] = "189" . $leadzeroproduct . $intfacerows['productname'] . $leadzeroproductquantity . $intfacerows['productquantity'];
                    $productnamearray[] = $intfacerows['productname'];
                }

                /**
                 * for check duplicate account name and write account name in set file
                 */
                if (!in_array($intfacerows['accountname'], $productaccountarray)) {
                    /**
                     * count account name length if length less then 6 then leading zero in account name.
                     */
                    $accountlenth = strlen($intfacerows['accountname']);
                    if ($accountlenth < 6) {
                        $leadzero = leadingzero(6, $accountlenth);
                    }
                    /**
                     * final account name;
                     * $accountname
                     */
                    $finalformataccountname = $leadzero . $intfacerows['accountname'];
                }

                
                
                $currentdate = date("YmdHi");
                $salesID = preg_replace('/[A-Z]/', '', $intfacerows['salesorder_no']);
                $originalordernomber = "7777" . $salesID;

                /**
                 * for find the order no. total length if length will be greater then 6 then auto remove from the starting
                 */
                $orderlength = strlen($originalordernomber);

                if ($orderlength > 6) {
                    $accessorderlength = $orderlength - 6;
                    /**
                     *  auto remove order no.  
                     */
                    $ordernomber = substr($originalordernomber, $accessorderlength);
                } else {
                    $ordernomber = $originalordernomber;
                }


                /*
                 *  end    
                 */
                if(!empty($intfacerows['duedate']) && $intfacerows['duedate'] != '0000-00-00')
                    $deliveryday = date("ymd", strtotime($intfacerows['duedate']));
                else
                    $deliveryday = date("ymd");
                
                $futuredeliverydate1 = strtotime(date("Y-m-d", strtotime($deliveryday)) . "+2 day");
                $futuredeliverydate = date('ymd', $futuredeliverydate1);
            }
            $finalformatproductname = implode("+", $multiproduct);
            unset($multiproduct);
            unset($productnamearray);
            /**
             * end of last while
             */
        }
        /**
         *  close else
         */
        $millisec = toTimestamp();
        /**
         *  Write SET Files
         */
        
        $string = "HEADERGIZUR           " . $currentdate . "{$millisec}M256      " . 
            "RUTIN   .130KF27777100   mottagning initierad" . 
            "                                                                         001" . 
            $finalformataccountname . "1+03751+038" . $ordernomber . "+226" . 
            $futuredeliverydate . "+039" . $deliveryday . "+040" . $ordernomber . "+" . 
            $finalformatproductname . "+C         RUTIN   .130KF27777100   " . 
            "Mottagning avslutad    BYTES/BLOCKS/RETRIES=1084 /5    /0";
        /**
         * Split line and place \n at every 80 chars
         */
        $pieces = str_split($string, 80);
        $string = join("\n", $pieces);
        /**
         * End Write Files
         */
        fwrite($fh, $string);
        fclose($fh);

        /**
         *   check file successfully write or not 
         */
        if (file_exists($ourfilename)) {
            /**
             * send files in messageq server
             */
            $messagequniqueid = $interfaceorderid['accountname'];
            /**
             * Insert Salesorder_no into message_que Table because recieved related message 
             */
            if (InsertRecordToMsg($dbconfig_integration['db_name'], $messagequniqueid, $filename, $obj1->link)) {
                $_message = $messagequniqueid;
                $_response = $sqs->send_message($amazonqueue_config['_url'], $_message);
                if ($_response->status == 200) {
                    echo " [x] " . $messagequniqueid . " ' Sent successfully in messageQ'\n";
                } else {
                    $findproblemsalesorder[] = "Some problems in Amazon SQS Queue " . $amazonqueue_config['_url'] . ".";
                    $allok = false;
                }
            } else {
                $findproblemsalesorder[] = "Some problem in Inserting Salesorder number into msg que table.";
                $allok = false;
            }
            // } 
            /**
             * end messasgeq process 
             */
            $interfaceexequery = 1;
        } else {
            $interfaceexequery = 0;
            $findproblemsalesorder[] = "set file error : the file does not write in folder . the sales order no is " . $interfaceorderid['salesorder_no'] . " ";
            /**
             *  set $allok is false 
             */
            $allok = false;
        }

        /**
         *  for all row succussfully inserted into the interface table then autometic commit commance execute other wise rollback command execute
         */
        $allok = ($allok && $interfaceexequery);
    }

    /**
     *   if the query successfull then commit comand execute here 
     */
    if ($allok) {
        mysql_query("commit");
        mysql_close($obj1->link);
        echo "all process succussfilly executed.!!!! /n";
    }

    /**
     * if the query not successfull the rollback command execute here 
     */ else {
        mysql_query("rollback");
        mysql_close($obj1->link);
        $access = date("y/m/d h:i:s");

        /** write error message into the syslog		
         */
        $findproblemsalesordermsg = implode(" ", $findproblemsalesorder);
        $message = "sorry ! - some problem in " . $findproblemsalesordermsg . ". error is : " . $queryerror . " at " . $access . "  ";
        syslog(LOG_WARNING, "" . $message . "");
    }
    /**
     *  end of first while
     */
}
/**
 * end of if
 */
/**
 * if record will be empty 
 */ else {
    echo "no record found!!!!";
}
?>

<?php
/*
 * Get 4 first digit from millisecond
 */
function toTimestamp()
{
    $seconds = round(microtime(true) * 1000);
    $remainder = substr("$seconds",-4);

    return $remainder;
}
/**
 * auto adding zero befor number  
 */
function leadingzero($limitnumber = 6, $number)
{
    $leadzero = "";
    $leadingzero = $limitnumber - $number;
    for ($i = 0; $i < $leadingzero; $i++) {
        $leadzero.= 0;
    }
    return $leadzero;
}

/**
 *   Create function For Insert salesorder no into the msg que table when successfully created file and que in messagemq server.
 */
function InsertRecordToMsg($db, $accountname, $Setfile, $connf)
{

    $Insertsalesordertable = "INSERT INTO `$db`.`saleorder_msg_que` 
        SET accountname= '$accountname', 
            ftpfilename= '$Setfile'  ON DUPLICATE KEY UPDATE accountname = '$accountname'";
    $exequery = @mysql_query($Insertsalesordertable, $connf);
    if ($exequery) {
        $updateinterfasesatus = "UPDATE `$db`.`salesorder_interface` 
            SET sostatus = 'Delivered' where accountname = '$accountname'";
        $exequery = mysql_query($updateinterfasesatus, $connf);
        if ($exequery) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

$conn->close();
?>

