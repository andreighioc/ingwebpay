<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Tests\Feature;

use AndreighioC\IngWebPay\IngWebPay;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;

class IngWebPayIntegrationTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [\AndreighioC\IngWebPay\IngWebPayServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'IngWebPay' => \AndreighioC\IngWebPay\Facades\IngWebPay::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('ingwebpay.environment', 'test');
        $app['config']->set('ingwebpay.username', 'TEST_USER');
        $app['config']->set('ingwebpay.password', 'TEST_PASS');
        $app['config']->set('ingwebpay.currency', '946');
        $app['config']->set('ingwebpay.return_url', 'https://example.com/callback');
        $app['config']->set('ingwebpay.language', 'ro');
        $app['config']->set('ingwebpay.order_number_mode', 'merchant');
    }

    // =========================================================================
    //  Service Resolution
    // =========================================================================

    public function test_service_is_bound_as_singleton(): void
    {
        $instance1 = app(IngWebPay::class);
        $instance2 = app(IngWebPay::class);

        $this->assertSame($instance1, $instance2);
    }

    public function test_facade_resolves(): void
    {
        $this->assertInstanceOf(IngWebPay::class, \AndreighioC\IngWebPay\Facades\IngWebPay::getFacadeRoot());
    }

    public function test_is_test_environment(): void
    {
        $service = app(IngWebPay::class);

        $this->assertTrue($service->isTestEnvironment());
    }

    public function test_base_url_is_test(): void
    {
        $service = app(IngWebPay::class);

        $this->assertStringContainsString('securepay-uat.ing.ro', $service->getBaseUrl());
    }

    // =========================================================================
    //  Register (Faked HTTP)
    // =========================================================================

    public function test_register_success(): void
    {
        Http::fake([
            '*/register.do' => Http::response([
                'orderId' => 'abc-123-def',
                'formUrl' => 'https://securepay-uat.ing.ro/mpi_uat/merchants/test/payment.html?mdOrder=abc-123-def',
            ], 200),
        ]);

        $service = app(IngWebPay::class);
        $response = $service->register(
            amount: 10000,
            orderNumber: 'ORD-001',
            description: 'Test payment',
        );

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('abc-123-def', $response->orderId);
        $this->assertNotNull($response->formUrl);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'register.do')
                && $request['amount'] == 10000
                && $request['orderNumber'] === 'ORD-001'
                && $request['jsonParams'] === '{"FORCE_3DS2":"true"}'
                && $request['orderBundle'] === '{}';
        });
    }

    public function test_register_pre_auth_success(): void
    {
        Http::fake([
            '*/registerPreAuth.do' => Http::response([
                'orderId' => 'preauth-001',
                'formUrl' => 'https://securepay-uat.ing.ro/mpi_uat/merchants/test/payment.html?mdOrder=preauth-001',
            ], 200),
        ]);

        $service = app(IngWebPay::class);
        $response = $service->registerPreAuth(amount: 5000, orderNumber: 'PRE-001');

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('preauth-001', $response->orderId);

        Http::assertSent(fn ($r) => str_contains($r->url(), 'registerPreAuth.do'));
    }

    public function test_register_duplicate_order_error(): void
    {
        Http::fake([
            '*/register.do' => Http::response([
                'errorCode'    => '1',
                'errorMessage' => 'An order with the same number has already been processed.',
            ], 200),
        ]);

        $service = app(IngWebPay::class);
        $response = $service->register(amount: 100, orderNumber: 'DUP-001');

        $this->assertFalse($response->isSuccessful());
        $this->assertSame('1', $response->errorCode);
    }

    // =========================================================================
    //  getOrderStatus (Faked HTTP)
    // =========================================================================

    public function test_get_order_status_deposited(): void
    {
        Http::fake([
            '*/getOrderStatus.do' => Http::response([
                'OrderStatus'    => 2,
                'ErrorCode'      => '0',
                'ErrorMessage'   => 'Success',
                'OrderNumber'    => 'ORD-001',
                'Pan'            => '425601**0206',
                'cardholderName' => 'Test User',
                'Amount'         => 10000,
                'depositAmount'  => 10000,
                'currency'       => '946',
            ], 200),
        ]);

        $service = app(IngWebPay::class);
        $status = $service->getOrderStatus('abc-123-def');

        $this->assertTrue($status->isSuccessful());
        $this->assertSame('ORD-001', $status->orderNumber);
        $this->assertSame(100.0, $status->getAmountInMajorUnits());
    }

    public function test_get_order_status_declined(): void
    {
        Http::fake([
            '*/getOrderStatus.do' => Http::response([
                'OrderStatus'  => 6,
                'ErrorCode'    => '2',
                'ErrorMessage' => 'Payment is declined',
                'OrderNumber'  => 'ORD-002',
                'Amount'       => 5000,
            ], 200),
        ]);

        $service = app(IngWebPay::class);
        $status = $service->getOrderStatus('xyz-789');

        $this->assertTrue($status->isFailed());
        $this->assertFalse($status->isSuccessful());
    }

    // =========================================================================
    //  getOrderStatusExtended (Faked HTTP)
    // =========================================================================

    public function test_get_order_status_extended(): void
    {
        Http::fake([
            '*/getOrderStatusExtended.do' => Http::response([
                'errorCode'             => '0',
                'errorMessage'          => 'Success',
                'orderNumber'           => 'ORD-001',
                'orderStatus'           => 2,
                'actionCode'            => 0,
                'actionCodeDescription' => 'Approved',
                'amount'                => 10000,
                'currency'              => '946',
                'date'                  => 1700000000000,
                'cardAuthInfo'          => [
                    'pan'            => '425603**2773',
                    'cardholderName' => 'Test User',
                    'approvalCode'   => '123456',
                ],
            ], 200),
        ]);

        $service = app(IngWebPay::class);
        $status = $service->getOrderStatusExtended('abc-123-def');

        $this->assertTrue($status->isSuccessful());
        $this->assertSame('425603**2773', $status->getPan());
        $this->assertSame('Test User', $status->getCardholderName());
        $this->assertSame('123456', $status->getApprovalCode());
    }

    // =========================================================================
    //  Reverse (Faked HTTP)
    // =========================================================================

    public function test_reverse_transaction(): void
    {
        Http::fake([
            '*/reverse.do' => Http::response([
                'errorCode'    => '0',
                'errorMessage' => 'Success',
            ], 200),
        ]);

        $service = app(IngWebPay::class);
        $result = $service->reverse('abc-123-def');

        $this->assertSame('0', $result['errorCode']);

        Http::assertSent(fn ($r) => str_contains($r->url(), 'reverse.do')
            && $r['orderId'] === 'abc-123-def'
        );
    }

    // =========================================================================
    //  Deposit (Faked HTTP)
    // =========================================================================

    public function test_deposit_pre_auth(): void
    {
        Http::fake([
            '*/deposit.do' => Http::response([
                'errorCode'    => '0',
                'errorMessage' => 'Success',
            ], 200),
        ]);

        $service = app(IngWebPay::class);
        $result = $service->deposit('preauth-001', 5000);

        $this->assertSame('0', $result['errorCode']);

        Http::assertSent(fn ($r) => str_contains($r->url(), 'deposit.do')
            && $r['amount'] == 5000
        );
    }

    // =========================================================================
    //  Refund (Faked HTTP)
    // =========================================================================

    public function test_refund_transaction(): void
    {
        Http::fake([
            '*/refund.do' => Http::response([
                'errorCode'    => '0',
                'errorMessage' => 'Success',
            ], 200),
        ]);

        $service = app(IngWebPay::class);
        $result = $service->refund('abc-123-def', 5000);

        $this->assertSame('0', $result['errorCode']);

        Http::assertSent(fn ($r) => str_contains($r->url(), 'refund.do')
            && $r['amount'] == 5000
        );
    }

    // =========================================================================
    //  HTTP Error Handling
    // =========================================================================

    public function test_http_error_throws_exception(): void
    {
        Http::fake([
            '*/register.do' => Http::response('Internal Server Error', 500),
        ]);

        $this->expectException(\AndreighioC\IngWebPay\Exceptions\IngWebPayException::class);
        $this->expectExceptionMessage('ING WebPay connection failed');

        $service = app(IngWebPay::class);
        $service->register(amount: 100, orderNumber: 'ERR-001');
    }

    public function test_invalid_json_throws_exception(): void
    {
        Http::fake([
            '*/register.do' => Http::response('not json', 200),
        ]);

        $this->expectException(\AndreighioC\IngWebPay\Exceptions\IngWebPayException::class);
        $this->expectExceptionMessage('Invalid JSON');

        $service = app(IngWebPay::class);
        $service->register(amount: 100, orderNumber: 'BADJSON-001');
    }
}
