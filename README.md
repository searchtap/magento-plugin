# Searchtap magento-2.x-plugin

**Official Website** https://www.searchtap.io/

**Installation Steps:**

To install plugin via [Composer], please follow the steps:

- ```$ composer require searchtap/magento-plugin```
- ```$ php bin/magento module:enable Bitqit_Searchtap```
- ```$ php bin/magento setup:upgrade && php bin/magento setup:static-content:deploy```

To install the plugin via [FTP], please follow the steps:

- ```$ Take pull from https://github.com/searchtap/magento-plugin```
- ```$ Unzip and upload Bitqit folder inside [app/code] ```
- ```$ Run the command in magento's root directory: php bin/magento module:enable Bitqit_Searchtap```
- ```$ Run the command in magento's root directory: php bin/magento setup:upgrade && php bin/magento setup:static-content:deploy```



