# Mage2 Module MageStack Google Login

    ``mage-stack/module-advance-filters``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
Logstash wrapper

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/MageStack`
 - Enable the module by running `php bin/magento module:enable MageStack_SwooleWebSocket`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require magestack/module-swoolewebsocket`
 - enable the module by running `php bin/magento module:enable MageStack_SwooleWebSocket`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration

<!-- window.webSocketClient.join('private_channel');

window.webSocketClient.listen('private_channel', function (data) {
  console.log('Order was shipped', data);
});

window.webSocketClient.sendMessage({'message' : 'Hello World!'}, 'private_channel'); -->


## Specifications

 - Console Command
	- Satatus


## Attributes



