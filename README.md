# Magento 2 Bestseller Widget

![alt text](https://raw.githubusercontent.com/magekey/module-bestseller-widget/master/docs/images/preview.png)

## Features:

- Bestseller Daily Widget
- Bestseller Monthly Widget
- Bestseller Yearly Widget

## Installing the Extension

    composer require magekey/module-bestseller-widget

## Deployment

    php bin/magento maintenance:enable                  #Enable maintenance mode
    php bin/magento setup:upgrade                       #Updates the Magento software
    php bin/magento setup:di:compile                    #Compile dependencies
    php bin/magento setup:static-content:deploy         #Deploys static view files
    php bin/magento cache:flush                         #Flush cache
    php bin/magento maintenance:disable                 #Disable maintenance mode

## Versions tested
> 2.2.2
> 2.2.3
