# Mage2 Module: Mage Stack Google Login
MageStack_GoogleLogin is a Magento 2 module designed to streamline the OAuth 2 using google.

## Requirements
- Magento 2.4.8
- PHP 8.4
- MageStack Core module
    ``composer require mage-stack/module-core``
- MageStack Social login module
    ``composer require mage-stack/module-social-login``

## Module version
- 1.0.0

## Main Functionalities
- Provide social login using Google OAuth 2

## Installation
1. **Install the module via Composer**:
    To install this module, run the following command in your Magento root directory:
    - ``composer require mage-stack/module-google-login``
2. **Enable the module:**
    After installation, enable the module by running:
   - ``php bin/magento module:enable MageStack_GoogleLogin``
3. **Apply database updates:**
    Run the setup upgrade command to apply any database changes:
    - ``php bin/magento setup:upgrade``
4. **Flush the Magento cache:**
    Finally, flush the cache:
   -  ``php bin/magento cache:flush``

## Usage
- Visit https://console.cloud.google.com/ and create client key and client secret 
- Set client key and secret in magento 2 admin
- Verify login in frontend

## Contributing
If you would like to contribute to this module, feel free to fork the repository and create a pull request. Please make sure to follow the coding standards of Magento 2.

## Reporting Issues
If you encounter any issues or need support, please create an issue on the GitHub Issues page. We will review and address your concerns as soon as possible.

## License
This module is licensed under the MIT License.

## Support
If you find this module useful, consider supporting me By giving this module a star on github
