<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Tests\Unit;

use AndreighioC\IngWebPay\DTOs\OrderStatusExtendedResponse;
use AndreighioC\IngWebPay\DTOs\OrderStatusResponse;
use AndreighioC\IngWebPay\DTOs\RegisterResponse;
use AndreighioC\IngWebPay\Enums\Currency;
use AndreighioC\IngWebPay\Enums\Environment;
use AndreighioC\IngWebPay\Enums\OrderStatus;
use AndreighioC\IngWebPay\Exceptions\IngWebPayException;
use AndreighioC\IngWebPay\IngWebPay;
use PHPUnit\Framework\TestCase;

class IngWebPayTest extends TestCase
{
    // =========================================================================
    //  Enums
    // =========================================================================

    public function test_currency_enum_values(): void
    {
        $this->assertSame('946', Currency::RON->value);
        $this->assertSame('978', Currency::EUR->value);
        $this->assertSame('RON', Currency::RON->label());
        $this->assertSame('EUR', Currency::EUR->label());
    }

    public function test_environment_enum_values(): void
    {
        $this->assertSame('test', Environment::TEST->value);
        $this->assertSame('production', Environment::PRODUCTION->value);
    }

    public function test_order_status_enum_values(): void
    {
        $this->assertSame(0, OrderStatus::REGISTERED->value);
        $this->assertSame(1, OrderStatus::PREAUTHORIZED->value);
        $this->assertSame(2, OrderStatus::DEPOSITED->value);
        $this->assertSame(3, OrderStatus::CANCELLED->value);
        $this->assertSame(4, OrderStatus::REVERSED->value);
        $this->assertSame(5, OrderStatus::ACS_INITIATED->value);
        $this->assertSame(6, OrderStatus::DECLINED->value);
    }

    public function test_order_status_is_successful(): void
    {
        $this->assertTrue(OrderStatus::DEPOSITED->isSuccessful());
        $this->assertFalse(OrderStatus::REGISTERED->isSuccessful());
        $this->assertFalse(OrderStatus::PREAUTHORIZED->isSuccessful());
        $this->assertFalse(OrderStatus::DECLINED->isSuccessful());
    }

    public function test_order_status_is_preauthorized(): void
    {
        $this->assertTrue(OrderStatus::PREAUTHORIZED->isPreauthorized());
        $this->assertFalse(OrderStatus::DEPOSITED->isPreauthorized());
    }

    public function test_order_status_is_failed(): void
    {
        $this->assertTrue(OrderStatus::CANCELLED->isFailed());
        $this->assertTrue(OrderStatus::REVERSED->isFailed());
        $this->assertTrue(OrderStatus::DECLINED->isFailed());
        $this->assertFalse(OrderStatus::DEPOSITED->isFailed());
        $this->assertFalse(OrderStatus::REGISTERED->isFailed());
    }

    public function test_order_status_is_pending(): void
    {
        $this->assertTrue(OrderStatus::REGISTERED->isPending());
        $this->assertTrue(OrderStatus::ACS_INITIATED->isPending());
        $this->assertFalse(OrderStatus::DEPOSITED->isPending());
        $this->assertFalse(OrderStatus::DECLINED->isPending());
    }

    public function test_order_status_labels(): void
    {
        $this->assertSame('Deposited (authorized)', OrderStatus::DEPOSITED->label());
        $this->assertSame('Declined', OrderStatus::DECLINED->label());
        $this->assertSame('Pre-authorized', OrderStatus::PREAUTHORIZED->label());
    }

    // =========================================================================
    //  DTOs — RegisterResponse
    // =========================================================================

    public function test_register_response_successful(): void
    {
        $data = [
            'orderId'  => '86faed41-d33b-4f10-b3bf-9c2a98ba4bd7',
            'formUrl'  => 'https://securepay-uat.ing.ro/mpi_uat/merchants/test/payment_en.html?mdOrder=86faed41',
        ];

        $dto = RegisterResponse::fromResponse($data);

        $this->assertTrue($dto->isSuccessful());
        $this->assertSame('86faed41-d33b-4f10-b3bf-9c2a98ba4bd7', $dto->orderId);
        $this->assertStringContainsString('securepay', $dto->formUrl);
        $this->assertNull($dto->errorCode);
        $this->assertNull($dto->errorMessage);
    }

    public function test_register_response_with_error(): void
    {
        $data = [
            'errorCode'    => '1',
            'errorMessage' => 'An order with the same number has already been processed.',
        ];

        $dto = RegisterResponse::fromResponse($data);

        $this->assertFalse($dto->isSuccessful());
        $this->assertNull($dto->orderId);
        $this->assertNull($dto->formUrl);
        $this->assertSame('1', $dto->errorCode);
    }

    public function test_register_response_error_code_zero_is_successful(): void
    {
        $data = [
            'orderId'   => 'abc-123',
            'formUrl'   => 'https://example.com/pay',
            'errorCode' => '0',
        ];

        $dto = RegisterResponse::fromResponse($data);

        $this->assertTrue($dto->isSuccessful());
    }

    // =========================================================================
    //  DTOs — OrderStatusResponse
    // =========================================================================

    public function test_order_status_response_deposited(): void
    {
        $data = [
            'OrderStatus'    => 2,
            'ErrorCode'      => '0',
            'ErrorMessage'   => 'Success',
            'OrderNumber'    => '12345',
            'Pan'            => '425601**0206',
            'expiration'     => '202512',
            'cardholderName' => 'John Doe',
            'Amount'         => 10231,
            'depositAmount'  => 10231,
            'currency'       => '946',
            'approvalCode'   => '448520',
            'authCode'       => 2,
            'Ip'             => '192.168.1.1',
        ];

        $dto = OrderStatusResponse::fromResponse($data);

        $this->assertTrue($dto->isSuccessful());
        $this->assertFalse($dto->isFailed());
        $this->assertFalse($dto->isPending());
        $this->assertSame(OrderStatus::DEPOSITED, $dto->orderStatus);
        $this->assertSame('12345', $dto->orderNumber);
        $this->assertSame('425601**0206', $dto->pan);
        $this->assertSame(10231, $dto->amount);
        $this->assertSame(102.31, $dto->getAmountInMajorUnits());
        $this->assertSame('946', $dto->currency);
    }

    public function test_order_status_response_declined(): void
    {
        $data = [
            'OrderStatus'  => 6,
            'ErrorCode'    => '2',
            'ErrorMessage' => 'Payment is declined',
            'OrderNumber'  => '12266',
            'Pan'          => '425601**0206',
            'Amount'       => 100,
            'Ip'           => '192.168.5.158',
        ];

        $dto = OrderStatusResponse::fromResponse($data);

        $this->assertFalse($dto->isSuccessful());
        $this->assertTrue($dto->isFailed());
        $this->assertSame(OrderStatus::DECLINED, $dto->orderStatus);
    }

    public function test_order_status_response_pending(): void
    {
        $data = [
            'OrderStatus' => 0,
            'OrderNumber' => '99999',
            'Amount'      => 5000,
        ];

        $dto = OrderStatusResponse::fromResponse($data);

        $this->assertFalse($dto->isSuccessful());
        $this->assertFalse($dto->isFailed());
        $this->assertTrue($dto->isPending());
    }

    public function test_order_status_response_null_amount(): void
    {
        $data = [
            'OrderNumber' => '11111',
        ];

        $dto = OrderStatusResponse::fromResponse($data);

        $this->assertNull($dto->getAmountInMajorUnits());
    }

    // =========================================================================
    //  DTOs — OrderStatusExtendedResponse
    // =========================================================================

    public function test_order_status_extended_response(): void
    {
        $data = [
            'errorCode'             => '0',
            'errorMessage'          => 'Success',
            'orderNumber'           => '107370',
            'orderStatus'           => 2,
            'actionCode'            => 0,
            'actionCodeDescription' => 'Approved',
            'amount'                => 100,
            'currency'              => '946',
            'date'                  => 1403680642722,
            'orderDescription'      => 'Test order',
            'ip'                    => '193.17.195.110',
            'merchantOrderParams'   => [],
            'attributes'            => [
                ['name' => 'mdOrder', 'value' => 'ff0b026c-c319-4e0f-af1f-230834b0eaec'],
            ],
            'cardAuthInfo' => [
                'expiration'     => '202604',
                'cardholderName' => 'Test User',
                'approvalCode'   => '448520',
                'pan'            => '425603**2773',
            ],
        ];

        $dto = OrderStatusExtendedResponse::fromResponse($data);

        $this->assertTrue($dto->isSuccessful());
        $this->assertFalse($dto->isFailed());
        $this->assertSame('107370', $dto->orderNumber);
        $this->assertSame(0, $dto->actionCode);
        $this->assertSame('425603**2773', $dto->getPan());
        $this->assertSame('Test User', $dto->getCardholderName());
        $this->assertSame('448520', $dto->getApprovalCode());
        $this->assertSame(1.0, $dto->getAmountInMajorUnits());
    }

    public function test_order_status_extended_response_failed(): void
    {
        $data = [
            'errorCode'             => '0',
            'errorMessage'          => 'Success',
            'orderNumber'           => '107370',
            'orderStatus'           => 6,
            'actionCode'            => 210,
            'actionCodeDescription' => 'TransactionDenied',
            'amount'                => 100,
            'currency'              => '946',
        ];

        $dto = OrderStatusExtendedResponse::fromResponse($data);

        $this->assertFalse($dto->isSuccessful());
        $this->assertTrue($dto->isFailed());
        $this->assertSame('TransactionDenied', $dto->actionCodeDescription);
    }

    // =========================================================================
    //  Exception
    // =========================================================================

    public function test_exception_from_response(): void
    {
        $response = [
            'errorCode'    => '5',
            'errorMessage' => 'Incorrect value of a parameter.',
        ];

        $exception = IngWebPayException::fromResponse($response);

        $this->assertSame('Incorrect value of a parameter.', $exception->getMessage());
        $this->assertSame('5', $exception->getErrorCode());
    }

    public function test_exception_connection_failed(): void
    {
        $exception = IngWebPayException::connectionFailed('Timeout');

        $this->assertStringContainsString('Timeout', $exception->getMessage());
        $this->assertSame('CONNECTION_ERROR', $exception->getErrorCode());
    }

    // =========================================================================
    //  Helpers
    // =========================================================================

    public function test_to_minor_units(): void
    {
        $this->assertSame(10231, IngWebPay::toMinorUnits(102.31));
        $this->assertSame(100, IngWebPay::toMinorUnits(1.00));
        $this->assertSame(0, IngWebPay::toMinorUnits(0));
        $this->assertSame(50, IngWebPay::toMinorUnits(0.50));
        $this->assertSame(999, IngWebPay::toMinorUnits(9.99));
    }

    public function test_to_major_units(): void
    {
        $this->assertSame(102.31, IngWebPay::toMajorUnits(10231));
        $this->assertSame(1.0, IngWebPay::toMajorUnits(100));
        $this->assertSame(0.0, IngWebPay::toMajorUnits(0));
    }
}
