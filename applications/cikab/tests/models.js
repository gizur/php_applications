{
    models: {
        Account: {
            'vtiger_account',{
                accountid: Number, 
                accountname: String
            },
            {
                id   : "accountid"
            }
        }
        InventoryProduct: {
            'vtiger_inventoryproductrel', {
                id: Number,
                quantity: Number, 
                productid: Number
            },
            {
                id   : null
            }
        }
        Product: {
            'vtiger_products', {
                productid: Number,
                productname: String
            },
            {
                id   : "productid"
            }
        }
        SalesOrder: {
            'vtiger_salesorder', {
                salesorderid: Number,
                salesorder_no: String,
                quoteid: Number,
                contactid: Number,
                duedate: String,
                accountid: Number,
                sostatus: String
            },
            {
                id   : "salesorderid"
            }
        }
    }
}