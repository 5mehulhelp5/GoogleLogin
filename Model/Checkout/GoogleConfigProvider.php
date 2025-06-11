<?php

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

declare(strict_types=1);

namespace MageStack\GoogleLogin\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use MageStack\SocialLogin\Api\OAuthProviderInterface;

/**
 * Expose google social auth information to checkout config
 *
 * @class GoogleConfigProvider
 * @namespace MageStack\GoogleLogin\Model\Checkout
 */
class GoogleConfigProvider implements ConfigProviderInterface
{
    /**
     * Constructor
     *
     * @param OAuthProviderInterface $oAuthProvider
     */
    public function __construct(
        private readonly OAuthProviderInterface $oAuthProvider
    ) {
    }

    /**
     * @inheritdoc
     *
     * @phpstan-ignore-next-line
     */
    public function getConfig(): array
    {
        return [
            'googleAuth' => [
                'redirectUrl' => $this->oAuthProvider->getCheckoutLoginRedirectUrl(),
            ]
        ];
    }
}
