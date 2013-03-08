
//------------------------------
//
// 2013-03-08, Prabhat Khera
//
// Copyright Gizur AB 2012
//
// Functions:
//  * Test jobs cron-style
//
// Install with dependencies: npm install 
//
// Documentation is 'docco style' - http://jashkenas.github.com/docco/
//
// Using Google JavaScript Style Guide - 
// http://google-styleguide.googlecode.com/svn/trunk/javascriptguide.xml
//
//------------------------------


"use strict";


// Includes
// =========
var AWS = require('aws-sdk');
var mysql = require("mysql");
var http    = require('http');
var fs = require('fs');
var exec = require('child_process').exec;

// Configs
// =======
// Load / read the AWS credentials
AWS.config.loadFromPath('./_secure/credentials.json');

// Load the configurations
var config  = require('./_secure/config.js').Config;

// If server requires secure connection
// use https
if(config.IS_HTTPS)
    http = require('https');

// Connection to vtiger MySQL db
var connection = mysql.createConnection({
    host: config.DB_HOST,
    user: config.DB_USER,
    password: config.DB_PASSWORD,
    database: config.DB_NAME
});

// Connection to integration MySQL db
var int_connection = mysql.createConnection({
    host: config.DB_I_HOST,
    user: config.DB_I_USER,
    password: config.DB_I_PASSWORD,
    database: config.DB_I_NAME
});

// ### Function testPhpBatch
// ========================
// 
// Used to test php batch files
// Accept 2 parameters
// test   : test case
// batch  : path to file

function testPhpBatch(test, batch){
    
    
}

// #### Connect with the databases.
connection.connect();
int_connection.connect();

// Expected test results
// =====================
var salesOrdervTigerBefore = 5,
salesOrderIntegrationBefore = 0,
salesOrdervTigerAfter = 0,
salesOrderIntegrationAfter = 5;
    
// Group all Tests
// ===============
// Before excuting this script,
// Edit test cases for the expected value.
exports.group = {
    // **Check sales orders in vtiger before hitting cron job 1**
    "Checking Sales Orders in vTiger ('Created','Approved') before hitting cron job 1" : function(test){
        connection.query("SELECT SO.salesorderid, SO.salesorder_no FROM " +
            "vtiger_salesorder SO " + 
            "WHERE SO.sostatus IN ('Created','Approved') " +
            "LIMIT 0, 10", function(err, rows, fields) {
                if (err) throw err;
                
                var result = false;
                if(rows.length == salesOrdervTigerBefore)
                    result = true;
                
                test.ok(result, salesOrdervTigerBefore + " sales orders expected, " + rows.length + " found.");
                test.done();
            });
    },
    // **Check sales orders in integration db before hitting cron job 1**
    "Checking Sales Order In Integration Database before hitting Cron job 1" : function(test){
        int_connection.query("SELECT salesorder_no, " +
            "accountname " +
            "FROM salesorder_interface " +
            "WHERE sostatus IN ('created', 'approved') " +
            "GROUP BY salesorder_no, accountname", function(err, rows, fields) {
                if (err) throw err;
                
                var result = false;
                if(rows.length == salesOrderIntegrationBefore)
                    result = true;
                
                test.ok(result, salesOrderIntegrationBefore + " sales orders expected, " + rows.length + " found.");
                test.done();
            });
    },
    // **Hitting cron job 1**
    "Hitting Cron Job 1" : function(test){
        exec("chmod +x " + config.PHP_BATCHES_1, function (error, stdout, stderr) {
            if (error !== null)
                test.ok(true, "Error in chmod +x " + batch);
            else
                exec(config.PHP_BATCHES_1, function (error, stdout, stderr) {
                    if (error !== null)
                        test.ok(false, "Error executing " + batch);
                    else
                        test.ok(true, "Error executing " + batch);
                    test.done();
                });
        });
    },
    // **Check sales orders in vtiger after hitting cron job 2**
    "Checking Sales Orders in vTiger ('Created','Approved') after hitting cron job 1" : function(test){
        connection.query("SELECT SO.salesorderid, SO.salesorder_no FROM " +
            "vtiger_salesorder SO " + 
            "WHERE SO.sostatus IN ('Created','Approved') " +
            "LIMIT 0, 10", function(err, rows, fields) {
                if (err) throw err;
                
                var result = false;
                if(rows.length == salesOrdervTigerAfter)
                    result = true;
                
                test.ok(result, salesOrdervTigerAfter + " sales orders expected, " + rows.length + " found.");
                test.done();
            });
    },
    // **Check sales orders in integration db after hitting cron job 1**
    "Checking Sales Order In Integration Database" : function(test){
        int_connection.query("SELECT salesorder_no, " +
            "accountname " +
            "FROM salesorder_interface " +
            "WHERE sostatus IN ('created', 'approved') " +
            "GROUP BY salesorder_no, accountname", function(err, rows, fields) {
                if (err) throw err;
                
                var result = false;
                if(rows.length == salesOrderIntegrationAfter)
                    result = true;
                
                test.ok(result, salesOrderIntegrationAfter + " sales orders expected, " + rows.length + " found.");
                test.done();
            });
    },
    // #### Closing connections
    // 
    // Reason behind putting closing connections in
    // a test is, not to close connections
    // before test execution.
    "Closing Connections" : function(test){
        connection.destroy();
        int_connection.destroy();
        test.ok(true, "Connections closed.");
        test.done();
    }
};