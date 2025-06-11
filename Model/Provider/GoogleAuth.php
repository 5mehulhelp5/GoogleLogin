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

namespace MageStack\GoogleLogin\Model\Provider;

use InvalidArgumentException;
use MageStack\SocialLogin\Api\OAuthProviderInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\UrlInterface;
use MageStack\SocialLogin\Api\SocialAuthStateManagerInterface;
use MageStack\GoogleLogin\Api\ConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Google auth provider
 *
 * @class GoogleAuth
 * @namespace MageStack\GoogleLogin\Model\Provider
 */
class GoogleAuth implements OAuthProviderInterface
{
    /**
     * Slugs
     */
    public const REDIRECT_SLUG = 'mage-stack-google/auth/redirect';
    private const CALLBACK_SLUG = 'mage-stack-google/auth/callback';

    /**
     * Constructor
     *
     * @param SocialAuthStateManagerInterface $authStateMgr
     * @param SerializerInterface $serializer
     * @param UrlInterface $urlBuilder
     * @param ConfigInterface $config
     * @param Curl $curl
     * @param LoggerInterface $logger
     * @param string $authorizationUrl
     * @param string $userInfoUrl
     * @param string $tokenUrl
     */
    public function __construct(
        private readonly SocialAuthStateManagerInterface $authStateMgr,
        private readonly SerializerInterface $serializer,
        private readonly UrlInterface $urlBuilder,
        private readonly ConfigInterface $config,
        private readonly Curl $curl,
        private readonly LoggerInterface $logger,
        private readonly string $authorizationUrl,
        private readonly string $userInfoUrl,
        private readonly string $tokenUrl
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getAuthorizationUrl(): string
    {
        $queryParams = [
            'response_type' => 'code',
            'client_id' => $this->config->getClientKey(),
            'redirect_uri' => $this->getCallbackUrl(),
            'scope' => $this->getScope(),
            'state' => $this->authStateMgr->getState(),
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];

        return $this->authorizationUrl . '?' . http_build_query($queryParams);
    }

    /**
     * @inheritDoc
     */
    public function getCallbackUrl(): string
    {
        return $this->urlBuilder->getUrl(self::CALLBACK_SLUG, ['_secure' => true]);
    }

    /**
     * @inheritDoc
     */
    public function getScope(): string
    {
        return 'email profile';
    }

    /**
     * @inheritDoc
     */
    public function getLoginRedirectUrl(): string
    {
        $params = ['_secure' => true];

        return $this->getRedirectUrl($params);
    }

    /**
     * @inheritDoc
     */
    public function getCheckoutLoginRedirectUrl(): string
    {
        $params = [
            '_secure' => true,
            'is_at_checkout' => 'true'
        ];

        return $this->getRedirectUrl($params);
    }

    /**
     * @inheritDoc
     */
    public function getRegisterRedirectUrl(): string
    {
        $params = [
            '_secure' => true,
            'is_register' => 'true'
        ];

        return $this->getRedirectUrl($params);
    }

    /**
     * @inheritDoc
     */
    public function getCheckoutRegisterRedirectUrl(): string
    {
        $params = [
            '_secure' => true,
            'is_register' => 'true',
            'is_at_checkout' => 'true'
        ];

        return $this->getRedirectUrl($params);
    }

    /**
     * Get redirect url
     *
     * @param array $params
     * @phpstan-param array<string, string|true> $params
     *
     * @return string
     */
    private function getRedirectUrl(array $params): string
    {
        return $this->urlBuilder->getUrl(self::REDIRECT_SLUG, $params);
    }

    /**
     * @inheritDoc
     */
    public function getAccessToken(string $code): ?string
    {
        $postFields = [
            'code' => $code,
            'client_id' => $this->config->getClientKey(),
            'client_secret' => $this->config->getClientSecret(),
            'redirect_uri' => $this->getCallbackUrl(),
            'grant_type' => 'authorization_code'
        ];

        $this->curl->post($this->tokenUrl, $postFields);
        $response = $this->parseBody($this->curl->getBody());
        $mapped = $this->mapBody(
            $response,
            [
                'access_token' => 'token'
            ]
        );

        return $mapped['token'] ? (string) $mapped['token'] : null;
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo(string $accessToken): array
    {
        $this->curl->addHeader('Authorization', 'Bearer ' . $accessToken);
        $this->curl->get($this->userInfoUrl);

        $parsedBody = $this->parseBody($this->curl->getBody());

        return $this->mapBody(
            $parsedBody,
            [
                'email' => 'email',
                'given_name' => 'first_name',
                'family_name' => 'last_name',
            ]
        );
    }

    /**
     * Parse api response body
     *
     * @param mixed $body
     *
     * @return array<int|string, mixed>
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    private function parseBody(mixed $body): array
    {
        $parsedBody = [];
        $stringBody = is_string($body) ? $body : '{}';

        try {
            $unSerializedBody = $this->serializer->unserialize($stringBody);
            if (is_array($unSerializedBody)) {
                $parsedBody = $unSerializedBody;
            }

        } catch (Throwable $th) {
            $this->logger->error(
                '[MageStack][Google][Auth] Error happens during response body parse',
                [
                    'error_message' => $th->getMessage(),
                    'stack_trace' => $th->getTraceAsString(),
                    'body' => is_object($body) ? "object" : $body
                ]
            );
        }

        return $parsedBody;
    }

    /**
     * Map user information
     *
     * @param array $body
     * @phpstan-param array<string|int, mixed> $body
     * @param string[] $requiredFields
     *
     * @throws InvalidArgumentException
     *
     * @return string[]
     */
    private function mapBody(array $body, array $requiredFields): array
    {
        if (empty($body)) {
            throw new InvalidArgumentException('Invalid response from OAuth Provider');
        }

        $map = [];

        foreach ($requiredFields as $field => $mappedField) {
            if (!isset($body[$field]) || !is_string($body[$field])) {
                throw new InvalidArgumentException('Invalid response from OAuth Provider : ' . $field);
            }
            $map[$mappedField] = (string) $body[$field];
        }

        return $map;
    }
}
