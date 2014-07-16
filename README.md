Veritrans Prestashop Module
===========================

Veritrans :heart: Prestashop!

## Installation Instruction

1. Download the plugin either from this repository or the Prestashop Extension.

2. Extract the modules, then rename the folder modules as **veritranspay** and zip this modules as **veritranspay.zip**

3. Go to your Prestashop administration page and go to **"Modules"** menu.

4. Click on the **"Add a new module"** and locate the **veritranspay.zip** file, then upload it.

5. Find the **Veritrans Pay** module in the module list and install the module.

## MAP Configuration

1. Change the following settings in your Merchant Administration Portal:
   
   * Payment Notification URL: 

     - Prestashop 1.4 and lower: `http://[your-site-url]/modules/veritranspay/notification.php`

     - Prestashop 1.5 and higher: `http://[your-site-url]/index.php?fc=module&module=veritranspay&controller=notification`

   * Finish Redirect URL: 

     - Prestashop 1.4 and lower: `http://[your-site-url]/modules/veritranspay/order_confirmation.php`

     - Prestashop 1.5 and higher: `http://[your-site-url]/index.php?fc=module&module=veritranspay&controller=confirmation`

   * Unfinish Redirect URL: `http://[your-site-url]`

   * Error Redirect URL:

     - Prestashop 1.4 and lower: `http://[your-site-url]/modules/veritranspay/order_confirmation.php`

     - Prestashop 1.5 and higher: `http://[your-site-url]/index.php?fc=module&module=veritranspay&controller=confirmation`

2. Enjoy!
