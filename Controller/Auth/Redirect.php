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

namespace MageStack\GoogleLogin\Controller\Auth;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect as CoreRedirect;
use MageStack\SocialLogin\Api\OAuthProviderInterface;
use MageStack\SocialLogin\Api\SocialAuthStateManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Controller for redirect
 *
 * @class Redirect
 * @namespace MageStack\GoogleLogin\Controller\Auth
 */
class Redirect implements HttpGetActionInterface
{
    /**
     * Redirect paths
     */
    private const AUTH_FAILURE_PATH = 'customer/account/login';

    /**
     * Constructor
     *
     * @param RedirectFactory $redirectFactory
     * @param RequestInterface $request
     * @param OAuthProviderInterface $oAuthProvider
     * @param SocialAuthStateManagerInterface $authStateMgr
     * @param SessionManagerInterface $sessionManager
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly RedirectFactory $redirectFactory,
        private readonly RequestInterface $request,
        private readonly OAuthProviderInterface $oAuthProvider,
        private readonly SocialAuthStateManagerInterface $authStateMgr,
        private readonly SessionManagerInterface $sessionManager,
        private readonly ManagerInterface $messageManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Execute redirect
     *
     * @return CoreRedirect
     */
    public function execute()
    {
        /** @var CoreRedirect $resultRedirect */
        $resultRedirect = $this->redirectFactory->create();

        try {
            $sessionId = $this->sessionManager->getSessionId();
            $this->authStateMgr->setIdentifier($sessionId)->setState();

            $isRegister = (bool) $this->request->getParam('is_register');
            if ($isRegister) {
                $this->authStateMgr->setAsRegisterRequest();
            }

            $isAtCheckout = (bool) $this->request->getParam('is_at_checkout');
            if ($isAtCheckout) {
                $this->authStateMgr->setAsCheckoutRequest();
            }

            $googleUrl = $this->oAuthProvider->getAuthorizationUrl();
            $resultRedirect->setUrl($googleUrl);
        } catch (Throwable $th) {
            $this->logger->error(
                '[MageStack][Google][Auth] Error happens during redirect',
                [
                    'error_message' => $th->getMessage(),
                    'stack_trace' => $th->getTraceAsString()
                ]
            );
            $this->messageManager->addErrorMessage(__('Something went wrong. Please try again later.')->render());
            $resultRedirect->setPath(self::AUTH_FAILURE_PATH);
        }

        return $resultRedirect;
    }
}
