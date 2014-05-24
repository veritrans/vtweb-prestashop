vtweb-prestashop
================

Veritrans VT-Web plugin for Prestashop

## How to use

### STEP 1 : Installing module
Given you already extract the ZIP file.
- Save the "veritranspay" folder within as a .zip file ("veritranspay.zip") .
- Go to your Prestashop administration page and go to **"Modules"** menu.
- Click on the **"Add a new module"** and locate the **veritranspay.zip** file, then upload it.
- You will find a **Veritrans Pay** module in the module list and install the module.

Now the veritrans module is installed on your Prestashop.

### STEP 2 : Configuration Setting
After the module is installed, you need to configure the **Merchant ID** and **Merchant Hash**
 before using the module.
 - Click **"Configure"** option below the Veritrans Pay module.
 - Insert your Merchant ID and Merchant Hash and click **"Update settings"** to save this configuration.


Veritrans Prestashop Module
===========================

Veritrans :heart: Prestashop!

## Installation Instruction

1. Download this repository.

2. Upload to your Prestashop installation.

3. Change the following settings in your MAP:

   * Payment Notification URL: 

     - Prestashop 1.4 and lower: `http://[your-site-url]/modules/veritranspay/notification.php`

     - Prestashop 1.5 and higher: `http://[your-site-url]/index.php?fc=module&module=veritranspay&controller=notification`

   * Finish Redirect URL: 

     - Prestashop 1.4 and lower: `http://[your-site-url]/modules/veritranspay/order_confirmation.php`

     - Prestashop 1.5 and higher: `http://[your-site-url]/index.php?controller=confirmation`

   * Unfinish Redirect URL: `http://[your-site-url]`

   * Error Redirect URL:

     - Prestashop 1.4 and lower: `http://[your-site-url]/modules/veritranspay/order_confirmation.php`

     - Prestashop 1.5 and higher: `http://[your-site-url]/index.php?controller=confirmation`

4. Enjoy!
