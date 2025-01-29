<?php

namespace Tests\Entity;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @dataProvider validProductDataProvider
     */
    public function testConstructWithValidData(string $name, array $prices, string $type): void
    {
        $product = new Product($name, $prices, $type);
        $this->assertEquals($name, $product->getName());
        $this->assertEquals($prices, $product->getPrices());
        $this->assertEquals($type, $product->getType());
    }

    /**
     * @dataProvider invalidTypeProvider
     */
    public function testConstructWithInvalidType(string $type): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid type');
        new Product('Test Product', ['USD' => 10.0], $type);
    }

    public function testSetPricesWithValidData(): void
    {
        $product = new Product('Test Product', [], 'tech');
        $prices = ['USD' => 10.0, 'EUR' => 8.5];
        $product->setPrices($prices);
        $this->assertEquals($prices, $product->getPrices());
    }

    public function testSetPricesWithInvalidCurrency(): void
    {
        $product = new Product('Test Product', [], 'tech');
        $prices = ['GBP' => 10.0, 'EUR' => 8.5];
        $product->setPrices($prices);
        $this->assertEquals(['EUR' => 8.5], $product->getPrices());
    }

    public function testSetPricesWithNegativeValues(): void
    {
        $product = new Product('Test Product', [], 'tech');
        $prices = ['USD' => -10.0, 'EUR' => 8.5];
        $product->setPrices($prices);
        $this->assertEquals(['EUR' => 8.5], $product->getPrices());
    }

    public function testBuyProductWithSufficientFunds(): void
    {
        $product = new Product('Test Product', ['USD' => 10.0], 'tech');
        $person = $this->createMock(\App\Entity\Person::class);
        $wallet = $this->createMock(\App\Entity\Wallet::class);
        
        $wallet->method('getCurrency')->willReturn('USD');
        $wallet->method('getBalance')->willReturn(20.0);
        $wallet->expects($this->once())->method('removeFund')->with(10.0);
        
        $person->method('getWallet')->willReturn($wallet);
        
        $product->buy($person);
    }

    public function testBuyProductWithInsufficientFunds(): void
    {
        $product = new Product('Test Product', ['USD' => 10.0], 'tech');
        $person = $this->createMock(\App\Entity\Person::class);
        $wallet = $this->createMock(\App\Entity\Wallet::class);
        
        $wallet->method('getCurrency')->willReturn('USD');
        $wallet->method('getBalance')->willReturn(5.0);
        
        $person->method('getWallet')->willReturn($wallet);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient funds');
        $product->buy($person);
    }

    public function testBuyProductWithIncompatibleCurrency(): void
    {
        $product = new Product('Test Product', ['USD' => 10.0], 'tech');
        $person = $this->createMock(\App\Entity\Person::class);
        $wallet = $this->createMock(\App\Entity\Wallet::class);
        
        $wallet->method('getCurrency')->willReturn('EUR');
        
        $person->method('getWallet')->willReturn($wallet);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Product not available in this currency');
        $product->buy($person);
    }

    public function validProductDataProvider(): array
    {
        return [
            'Tech product' => ['Laptop', ['USD' => 1000.0], 'tech'],
            'Food product' => ['Apple', ['EUR' => 0.5], 'food'],
            'Alcohol product' => ['Wine', ['USD' => 20.0, 'EUR' => 18.0], 'alcohol'],
            'Other product' => ['Book', ['USD' => 15.0], 'other']
        ];
    }

    public function invalidTypeProvider(): array
    {
        return [
            'Empty type' => [''],
            'Invalid type' => ['invalid'],
            'Numeric type' => ['123'],
            'Special chars' => ['@#$%']
        ];
    }
}
