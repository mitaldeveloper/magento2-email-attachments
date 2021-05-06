# Custom form with email attachment module for Magento 2

The module for submit form like contact us or career form and have option to attach a file and will get this file in email.

## Installation

1. Go to Magento 2 root directory

2. Install module:

 
- Download [the latest version here](https://github.com//mitaldeveloper/magento2-email-attachments/archive/refs/heads/master.zip) 
- Extract `master.zip` file to root folder.
- Go to Magento root folder and run upgrade command line to install `Mital_Careers`:



3. Enter following commands to enable module:


   php bin/magento module:enable Mital_Careers
   php bin/magento setup:upgrade
   php bin/magento cache:clean


## Attached files will place under magento/pub/media/Mital/Careers

## You can access this form by yourstore.com/careers
