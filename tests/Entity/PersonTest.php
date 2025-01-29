<?php

namespace Tests\Entity;

use App\Entity\Person;
use App\Entity\Wallet;
use PHPUnit\Framework\TestCase;

class PersonTest extends TestCase
{
    public function testConstructor(): void
    {
        $person = new Person('John Doe', 'USD');
        $this->assertEquals('John Doe', $person->getName());
        $this->assertInstanceOf(Wallet::class, $person->getWallet());
        $this->assertEquals('USD', $person->getWallet()->getCurrency());
    }

    public function testSetName(): void
    {
        $person = new Person('John Doe', 'USD');
        $person->setName('Jane Doe');
        $this->assertEquals('Jane Doe', $person->getName());
    }

    public function testSetWallet(): void
    {
        $person = new Person('John Doe', 'USD');
        $newWallet = new Wallet('EUR');
        $person->setWallet($newWallet);
        $this->assertSame($newWallet, $person->getWallet());
    }

    public function testHasFundWithEmptyWallet(): void
    {
        $person = new Person('John Doe', 'USD');
        $this->assertFalse($person->hasFund());
    }

    public function testHasFundWithNonEmptyWallet(): void
    {
        $person = new Person('John Doe', 'USD');
        $person->getWallet()->addFund(100.0);
        $this->assertTrue($person->hasFund());
    }

    public function testTransfertFundWithSameCurrency(): void
    {
        $person1 = new Person('John Doe', 'USD');
        $person2 = new Person('Jane Doe', 'USD');
        
        $person1->getWallet()->addFund(100.0);
        $person1->transfertFund(50.0, $person2);
        
        $this->assertEquals(50.0, $person1->getWallet()->getBalance());
        $this->assertEquals(50.0, $person2->getWallet()->getBalance());
    }

    public function testTransfertFundWithDifferentCurrencies(): void
    {
        $person1 = new Person('John Doe', 'USD');
        $person2 = new Person('Jane Doe', 'EUR');
        
        $person1->getWallet()->addFund(100.0);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Can\'t give money with different currencies');
        $person1->transfertFund(50.0, $person2);
    }

    public function testDivideWalletWithSameCurrency(): void
    {
        $person1 = new Person('John Doe', 'USD');
        $person2 = new Person('Jane Doe', 'USD');
        $person3 = new Person('Jim Doe', 'USD');
        
        $person1->getWallet()->addFund(100.0);
        $person1->divideWallet([$person2, $person3]);
        
        $this->assertEquals(33.34, $person2->getWallet()->getBalance());
        $this->assertEquals(33.33, $person3->getWallet()->getBalance());
        $this->assertEquals(33.33, $person1->getWallet()->getBalance());
    }

    public function testDivideWalletWithMixedCurrencies(): void
    {
        $person1 = new Person('John Doe', 'USD');
        $person2 = new Person('Jane Doe', 'USD');
        $person3 = new Person('Jim Doe', 'EUR');
        
        $person1->getWallet()->addFund(100.0);
        $person1->divideWallet([$person2, $person3]);
        
        // Should only divide between USD wallets
        $this->assertEquals(50.0, $person2->getWallet()->getBalance());
        $this->assertEquals(0.0, $person3->getWallet()->getBalance());
        $this->assertEquals(50.0, $person1->getWallet()->getBalance());
    }

    public function testDivideWalletWithEmptyPersonsList(): void
    {
        $person = new Person('John Doe', 'USD');
        $person->getWallet()->addFund(100.0);
        $person->divideWallet([]);
        
        // Balance should remain unchanged
        $this->assertEquals(100.0, $person->getWallet()->getBalance());
    }
}
