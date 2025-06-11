/**
 * Copyright © 2025 MageStack. All rights reserved.
 * See COPYING.txt for license details.
 *
 * DISCLAIMER
 *
 * Do not make any kind of changes to this file if you
 * wish to upgrade this extension to newer version in the future.
 *
 * @category  MageStack
 * @package   MageStack_GoogleLogin
 * @author    Amit Biswas <amit.biswas.webdeveloper@gmail.com>
 * @copyright 2025 MageStack
 * @license   https://opensource.org/licenses/MIT  MIT License
 * @link      https://github.com/attherateof/GoogleLogin
 */

define([
    'uiComponent',
    'Magento_Customer/js/model/customer'
], function (Component, customer) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return Component.extend({
        isCustomerLoginRequired: checkoutConfig.isCustomerLoginRequired,
        redirectUrl: checkoutConfig.googleAuth.redirectUrl,
        isEnabled: checkoutConfig.googleAuth.isEnabled,
        defaults: {
            template: 'MageStack_GoogleLogin/google-login'
        },

        isActive: function () {
            return !customer.isLoggedIn() && this.isEnabled;
        },
    });
});
