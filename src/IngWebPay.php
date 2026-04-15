<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay;

use AndreighioC\IngWebPay\DTOs\OrderStatusExtendedResponse;
use AndreighioC\IngWebPay\DTOs\OrderStatusResponse;
use AndreighioC\IngWebPay\DTOs\RegisterResponse;
use AndreighioC\IngWebPay\Exceptions\IngWebPayException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Main service class for interacting with the ING WebPay REST API.
 *
 * Supports all API operations: register (sale), registerPreAuth,
 * getOrderStatus, getOrderStatusExtended, reverse, and deposit.
 */
class IngWebPay
{
    protected string $username;
    protected string $password;
    protected string $currency;
    protected string $returnUrl;
    protected string $language;
    protected string $orderNumberMode;
    protected string $baseUrl;

    public function __construct()
    {
        $this->username = config('ingwebpay.username', '');
        $this->password = config('ingwebpay.password', '');
        $this->currency = config('ingwebpay.currency', '946');
        $this->returnUrl = config('ingwebpay.return_url', '/ingwebpay/callback');
        $this->language = config('ingwebpay.language', 'ro');
        $this->orderNumberMode = config('ingwebpay.order_number_mode', 'merchant');

        $environment = config('ingwebpay.environment', 'test');
        $this->baseUrl = config("ingwebpay.base_urls.{$environment}");
    }

    // -------------------------------------------------------------------------
    //  Transaction Registration (Sale)
    // -------------------------------------------------------------------------

    /**
     * Register a new sale transaction with ING WebPay.
     *
     * The amount must be provided in minor currency units (bani / cents).
     * For example, 102.31 RON should be sent as 10231.
     *
     * @param  int          $amount          Amount in minor units (e.g. 10231 for 102.31 RON)
     * @param  string|null  $orderNumber     Unique order number (required if order_number_mode is "merchant")
     * @param  string|null  $description     Optional transaction description
     * @param  string|null  $returnUrl       Override the default return URL
     * @param  string|null  $language        Override the default language ("ro" or "en")
     * @param  string|null  $email           Payer email for automatic payment confirmation
     * @param  string|null  $currency        Override the default currency code
     * @param  string|null  $reconciliationId  Optional reconciliation identifier
     * @return RegisterResponse
     *
     * @throws IngWebPayException
     */
    public function register(
        int $amount,
        ?string $orderNumber = null,
        ?string $description = null,
        ?string $returnUrl = null,
        ?string $language = null,
        ?string $email = null,
        ?string $currency = null,
        ?string $reconciliationId = null,
    ): RegisterResponse {
        return $this->doRegister(
            endpoint: 'register.do',
            amount: $amount,
            orderNumber: $orderNumber,
            description: $description,
            returnUrl: $returnUrl,
            language: $language,
            email: $email,
            currency: $currency,
            reconciliationId: $reconciliationId,
        );
    }

    // -------------------------------------------------------------------------
    //  Transaction Registration (Pre-Authorization)
    // -------------------------------------------------------------------------

    /**
     * Register a new pre-authorization transaction with ING WebPay.
     *
     * Pre-authorizations must be completed (deposited) within 14 calendar days
     * for VISA/Mastercard or 7 calendar days for Maestro.
     *
     * @param  int          $amount          Amount in minor units
     * @param  string|null  $orderNumber     Unique order number
     * @param  string|null  $description     Optional transaction description
     * @param  string|null  $returnUrl       Override the default return URL
     * @param  string|null  $language        Override the default language
     * @param  string|null  $email           Payer email
     * @param  string|null  $currency        Override the default currency code
     * @param  string|null  $reconciliationId  Optional reconciliation identifier
     * @return RegisterResponse
     *
     * @throws IngWebPayException
     */
    public function registerPreAuth(
        int $amount,
        ?string $orderNumber = null,
        ?string $description = null,
        ?string $returnUrl = null,
        ?string $language = null,
        ?string $email = null,
        ?string $currency = null,
        ?string $reconciliationId = null,
    ): RegisterResponse {
        return $this->doRegister(
            endpoint: 'registerPreAuth.do',
            amount: $amount,
            orderNumber: $orderNumber,
            description: $description,
            returnUrl: $returnUrl,
            language: $language,
            email: $email,
            currency: $currency,
            reconciliationId: $reconciliationId,
        );
    }

    // -------------------------------------------------------------------------
    //  Transaction Status
    // -------------------------------------------------------------------------

    /**
     * Get the status of a transaction using getOrderStatus.do.
     *
     * @param  string       $orderId   The unique order ID assigned by ING WebPay
     * @param  string|null  $language  Override the default language
     * @return OrderStatusResponse
     *
     * @throws IngWebPayException
     */
    public function getOrderStatus(string $orderId, ?string $language = null): OrderStatusResponse
    {
        $params = [
            'userName' => $this->username,
            'password' => $this->password,
            'orderId'  => $orderId,
            'language' => $language ?? $this->language,
        ];

        $response = $this->sendRequest('getOrderStatus.do', $params);

        return OrderStatusResponse::fromResponse($response);
    }

    /**
     * Get the extended status of a transaction using getOrderStatusExtended.do.
     *
     * Returns richer data including actionCode, cardAuthInfo, and merchant attributes.
     *
     * @param  string       $orderId   The unique order ID assigned by ING WebPay
     * @param  string|null  $language  Override the default language
     * @return OrderStatusExtendedResponse
     *
     * @throws IngWebPayException
     */
    public function getOrderStatusExtended(string $orderId, ?string $language = null): OrderStatusExtendedResponse
    {
        $params = [
            'userName' => $this->username,
            'password' => $this->password,
            'orderId'  => $orderId,
            'language' => $language ?? $this->language,
        ];

        $response = $this->sendRequest('getOrderStatusExtended.do', $params);

        return OrderStatusExtendedResponse::fromResponse($response);
    }

    // -------------------------------------------------------------------------
    //  Transaction Reversal (Cancel)
    // -------------------------------------------------------------------------

    /**
     * Reverse (cancel) a transaction before batch settlement (COT 22:00).
     *
     * Works for both regular authorizations and pre-authorizations.
     *
     * @param  string       $orderId   The unique order ID to reverse
     * @param  string|null  $language  Override the default language
     * @return array                   Raw API response
     *
     * @throws IngWebPayException
     */
    public function reverse(string $orderId, ?string $language = null): array
    {
        $params = [
            'userName' => $this->username,
            'password' => $this->password,
            'orderId'  => $orderId,
            'language' => $language ?? $this->language,
        ];

        return $this->sendRequest('reverse.do', $params);
    }

    // -------------------------------------------------------------------------
    //  Pre-Authorization Completion (Deposit)
    // -------------------------------------------------------------------------

    /**
     * Complete (deposit) a pre-authorized transaction.
     *
     * If the amount is 0, the transaction is automatically completed with
     * the initial pre-authorized amount. The amount cannot exceed the
     * original pre-authorization amount.
     *
     * @param  string       $orderId   The pre-authorization order ID
     * @param  int          $amount    Amount in minor units (0 = full initial amount)
     * @param  string|null  $language  Override the default language
     * @return array                   Raw API response
     *
     * @throws IngWebPayException
     */
    public function deposit(string $orderId, int $amount = 0, ?string $language = null): array
    {
        $params = [
            'userName' => $this->username,
            'password' => $this->password,
            'orderId'  => $orderId,
            'amount'   => $amount,
            'language' => $language ?? $this->language,
        ];

        return $this->sendRequest('deposit.do', $params);
    }

    // -------------------------------------------------------------------------
    //  Refund
    // -------------------------------------------------------------------------

    /**
     * Refund a deposited transaction (after batch settlement / COT).
     *
     * @param  string  $orderId  The order ID to refund
     * @param  int     $amount   Amount to refund in minor units
     * @return array             Raw API response
     *
     * @throws IngWebPayException
     */
    public function refund(string $orderId, int $amount): array
    {
        $params = [
            'userName' => $this->username,
            'password' => $this->password,
            'orderId'  => $orderId,
            'amount'   => $amount,
        ];

        return $this->sendRequest('refund.do', $params);
    }

    // -------------------------------------------------------------------------
    //  Helpers
    // -------------------------------------------------------------------------

    /**
     * Convert a decimal amount (e.g. 102.31) to minor units (e.g. 10231).
     */
    public static function toMinorUnits(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Convert minor units (e.g. 10231) to a decimal amount (e.g. 102.31).
     */
    public static function toMajorUnits(int $minorUnits): float
    {
        return $minorUnits / 100;
    }

    /**
     * Get the current base URL being used.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Check whether the service is configured for the test environment.
     */
    public function isTestEnvironment(): bool
    {
        return config('ingwebpay.environment') === 'test';
    }

    // -------------------------------------------------------------------------
    //  Internal Methods
    // -------------------------------------------------------------------------

    /**
     * Shared logic for register.do and registerPreAuth.do.
     */
    protected function doRegister(
        string $endpoint,
        int $amount,
        ?string $orderNumber,
        ?string $description,
        ?string $returnUrl,
        ?string $language,
        ?string $email,
        ?string $currency,
        ?string $reconciliationId,
    ): RegisterResponse {
        $params = [
            'userName'    => $this->username,
            'password'    => $this->password,
            'amount'      => $amount,
            'currency'    => $currency ?? $this->currency,
            'returnUrl'   => $returnUrl ?? $this->resolveReturnUrl(),
            'language'    => $language ?? $this->language,
            'orderBundle' => '{}',
            'jsonParams'  => '{"FORCE_3DS2":"true"}',
        ];

        if ($this->orderNumberMode === 'merchant' && $orderNumber !== null) {
            $params['orderNumber'] = $orderNumber;
        }

        if ($description !== null) {
            $params['description'] = $description;
        }

        if ($email !== null) {
            $params['email'] = $email;
        }

        if ($reconciliationId !== null) {
            $params['reconciliationId'] = $reconciliationId;
        }

        $response = $this->sendRequest($endpoint, $params);

        return RegisterResponse::fromResponse($response);
    }

    /**
     * Resolve the full return URL (converts relative paths to absolute).
     */
    protected function resolveReturnUrl(): string
    {
        $url = $this->returnUrl;

        if (! str_starts_with($url, 'http')) {
            $url = rtrim(config('app.url', ''), '/') . '/' . ltrim($url, '/');
        }

        return $url;
    }

    /**
     * Send an HTTPS POST request to the ING WebPay API.
     *
     * @param  string  $endpoint  API endpoint (e.g. "register.do")
     * @param  array   $params    Request parameters
     * @return array              Decoded JSON response
     *
     * @throws IngWebPayException
     */
    protected function sendRequest(string $endpoint, array $params): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        try {
            Log::debug('ING WebPay request', [
                'endpoint' => $endpoint,
                'params'   => array_diff_key($params, array_flip(['password'])),
            ]);

            $response = Http::timeout(30)
                ->asForm()
                ->post($url, $params);

            if (! $response->successful()) {
                throw IngWebPayException::connectionFailed(
                    "HTTP {$response->status()}: {$response->body()}"
                );
            }

            $data = $response->json();

            if ($data === null) {
                throw IngWebPayException::connectionFailed(
                    'Invalid JSON response from ING WebPay'
                );
            }

            Log::debug('ING WebPay response', [
                'endpoint' => $endpoint,
                'response' => $data,
            ]);

            return $data;

        } catch (IngWebPayException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw IngWebPayException::connectionFailed($e->getMessage(), $e);
        }
    }
}
