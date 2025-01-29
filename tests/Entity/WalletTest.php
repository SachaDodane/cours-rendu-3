<?php

namespace Tests\Entity;

use App\Entity\Wallet;
use PHPUnit\Framework\TestCase;

class WalletTest extends TestCase
{
    /**
     * @dataProvider validCurrencyProvider
     */
    public function testConstructorWithValidCurrency(string $currency): void
    {
        $wallet = new Wallet($currency);
        $this->assertEquals(0, $wallet->getBalance());
        $this->assertEquals($currency, $wallet->getCurrency());
    }

    /**
     * @dataProvider invalidCurrencyProvider
     */
    public function testConstructorWithInvalidCurrency(string $currency): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid currency');
        new Wallet($currency);
    }

    public function testSetBalanceWithValidAmount(): void
    {
        $wallet = new Wallet('USD');
        $wallet->setBalance(100.0);
        $this->assertEquals(100.0, $wallet->getBalance());
    }

    public function testSetBalanceWithNegativeAmount(): void
    {
        $wallet = new Wallet('USD');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid balance');
        $wallet->setBalance(-100.0);
    }

    public function testAddFundWithValidAmount(): void
    {
        $wallet = new Wallet('USD');
        $wallet->addFund(50.0);
        $this->assertEquals(50.0, $wallet->getBalance());
    }

    public function testAddFundWithNegativeAmount(): void
    {
        $wallet = new Wallet('USD');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid amount');
        $wallet->addFund(-50.0);
    }

    public function testRemoveFundWithValidAmount(): void
    {
        $wallet = new Wallet('USD');
        $wallet->setBalance(100.0);
        $wallet->removeFund(50.0);
        $this->assertEquals(50.0, $wallet->getBalance());
    }

    public function testRemoveFundWithInsufficientFunds(): void
    {
        $wallet = new Wallet('USD');
        $wallet->setBalance(40.0);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient funds');
        $wallet->removeFund(50.0);
    }

    public function testRemoveFundWithNegativeAmount(): void
    {
        $wallet = new Wallet('USD');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid amount');
        $wallet->removeFund(-50.0);
    }

    public function validCurrencyProvider(): array
    {
        return [
            'USD currency' => ['USD'],
            'EUR currency' => ['EUR']
        ];
    }

    public function invalidCurrencyProvider(): array
    {
        return [
            'GBP currency' => ['GBP'],
            'JPY currency' => ['JPY'],
            'Empty string' => [''],
            'Invalid string' => ['INVALID']
        ];
    }
}
