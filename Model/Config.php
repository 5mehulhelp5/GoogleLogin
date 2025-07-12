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

namespace MageStack\GoogleLogin\Model;

use MageStack\SocialLogin\Api\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use RuntimeException;

/**
 * Get configuration from admin;
 *
 * Class Config
 * @namespace MageStack\GoogleLogin\Model
 */
class Config implements ConfigInterface
{
    /**
     * Configuration paths
     */
    public const GOOGLE_AUTH_ENABLED_CONFIG_XML_PATH = 'social_login/google/is_enabled';
    public const GOOGLE_AUTH_API_KEY_CONFIG_XML_PATH = 'social_login/google/app_key';
    public const GOOGLE_AUTH_API_SECRET_CONFIG_XML_PATH = 'social_login/google/app_secret';

    /**
     * Class constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(?int $webSiteId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::GOOGLE_AUTH_ENABLED_CONFIG_XML_PATH,
            ScopeInterface::SCOPE_WEBSITE,
            $webSiteId
        );
    }

    /**
     * @inheritDoc
     */
    public function getClientKey(): string
    {
        $clientKey = $this->scopeConfig->getValue(
            self::GOOGLE_AUTH_API_KEY_CONFIG_XML_PATH
        );

        if (!is_string($clientKey)) {
            throw new RuntimeException(__('Client key can not be empty.')->render());
        }

        return $this->encryptor->decrypt($clientKey);
    }

    /**
     * @inheritDoc
     */
    public function getClientSecret(): string
    {
        $clientSecret = $this->scopeConfig->getValue(
            self::GOOGLE_AUTH_API_SECRET_CONFIG_XML_PATH
        );

        if (!is_string($clientSecret)) {
            throw new RuntimeException(__('Client key can not be empty.')->render());
        }

        return $this->encryptor->decrypt($clientSecret);
    }

    /**
     * @inheritDoc
     */
    public function getFrontendLabel(?int $storeId = null): string
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_AUTH_API_KEY_CONFIG_XML_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
