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

use InvalidArgumentException;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use MageStack\SocialLogin\Api\OAuthProviderInterface;
use MageStack\SocialLogin\Api\SocialAuthServiceInterface;
use MageStack\SocialLogin\Api\SocialAuthStateManagerInterface;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Controller for callback
 *
 * @class Callback
 * @namespace MageStack\GoogleLogin\Controller\Auth
 */
class Callback implements HttpGetActionInterface
{
    /**
     * Redirect paths
     */
    private const DEFAULT_REDIRECT_PATH = 'customer/account';
    private const CHECKOUT_REDIRECT_PATH = 'checkout';
    private const AUTH_FAILURE_PATH = 'customer/account/login';

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     * @param SessionManagerInterface $customerSession
     * @param OAuthProviderInterface $oAuthProvider
     * @param SocialAuthServiceInterface $socialAuthService
     * @param SocialAuthStateManagerInterface $authStateMgr
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly RedirectFactory $redirectFactory,
        private readonly ManagerInterface $messageManager,
        private readonly SessionManagerInterface $customerSession,
        private readonly OAuthProviderInterface $oAuthProvider,
        private readonly SocialAuthServiceInterface $socialAuthService,
        private readonly SocialAuthStateManagerInterface $authStateMgr,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Execute callback
     *
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->redirectFactory->create();

        try {
            $this->prepareState();

            if (!$this->isValidRequest()) {
                return $this->handleInvalidRequest($resultRedirect);
            }

            $customer = $this->resolveCustomer();

            if ($customer) {
                return $this->loginCustomer($customer, $resultRedirect);
            }

            $this->messageManager->addErrorMessage(__('Customer not found. Please try to register.')->render());
        } catch (Throwable $e) {
            $this->handleException($e);
        }

        return $resultRedirect->setPath(self::AUTH_FAILURE_PATH);
    }

    /**
     * Prepare state
     *
     * @return void
     */
    private function prepareState(): void
    {
        $identifier = $this->customerSession->getSessionId();
        $this->authStateMgr->setIdentifier($identifier);
    }

    /**
     * Is valid request?
     *
     * @return bool
     */
    private function isValidRequest(): bool
    {
        $code = $this->request->getParam('code');
        $state = $this->request->getParam('state');
        if (is_string($code) && is_string($state)) {
            return $this->socialAuthService->isValidRequest($code, $state);
        }

        $message = sprintf(
            "Unable to retrive parameters. The code type is %s and state type is %s",
            get_debug_type($code),
            get_debug_type($state)
        );
        throw new InvalidArgumentException($message);
    }

    /**
     * Handle invalid request
     *
     * @param Redirect $redirect
     *
     * @return Redirect
     */
    private function handleInvalidRequest(Redirect $redirect): Redirect
    {
        $this->messageManager->addErrorMessage(__('Validation failed. Please try again later.')->render());
        return $redirect->setPath(self::AUTH_FAILURE_PATH);
    }

    /**
     * Resolve customer
     *
     * @return CustomerInterface|null
     */
    private function resolveCustomer(): ?CustomerInterface
    {
        $code = $this->request->getParam('code');
        if (is_string($code)) {
            return $this->socialAuthService->resolveCustomer($this->oAuthProvider, $code);
        }

        $message = sprintf(
            "Unable to retrive parameters. The code type is %s",
            get_debug_type($code)
        );
        throw new InvalidArgumentException($message);
    }

    /**
     * Login customer
     *
     * @param CustomerInterface $customer
     * @param Redirect $redirect
     *
     * @return Redirect
     */
    private function loginCustomer(CustomerInterface $customer, Redirect $redirect): Redirect
    {
        /**
         * @phpstan-ignore-next-line
         */
        $this->customerSession->setCustomerDataAsLoggedIn($customer);

        if ($this->authStateMgr->isAtCheckout()) {
            $this->authStateMgr->forgetIsAtCheckout();
            return $redirect->setPath(self::CHECKOUT_REDIRECT_PATH);
        }

        return $redirect->setPath(self::DEFAULT_REDIRECT_PATH);
    }

    /**
     * Handle exception
     *
     * @param Throwable $th
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    private function handleException(Throwable $th): void
    {
        $this->logger->error(
            '[MageStack][Google][Auth] Error happens during callback',
            [
                'error_message' => $th->getMessage(),
                'stack_trace' => $th->getTraceAsString()
            ]
        );
        $this->messageManager->addErrorMessage(
            __('An error occurred during social authentication. Please try again later.')->render()
        );
    }
}
