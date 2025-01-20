<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\Wallet;

class WalletTest extends TestCase
{
    private Wallet $wallet;
    private string $defaultCurrency = "EUR";

    protected function setUp(): void
    {
        $this->wallet = new Wallet($this->defaultCurrency);
    }

    public function testConstructor(): void
    {
        $this->assertEquals(0, $this->wallet->getBalance());
        $this->assertEquals($this->defaultCurrency, $this->wallet->getCurrency());
    }

    public function testGetAndSetBalance(): void
    {
        $this->wallet->setBalance(100.0);
        $this->assertEquals(100.0, $this->wallet->getBalance());
    }

    public function testSetBalanceWithNegativeAmount(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid balance');
        $this->wallet->setBalance(-100.0);
    }

    public function testGetAndSetCurrency(): void
    {
        // Test avec une devise valide
        $this->wallet->setCurrency('USD');
        $this->assertEquals('USD', $this->wallet->getCurrency());

        // Vérification que les devises disponibles sont correctes
        $this->assertEquals(['USD', 'EUR'], Wallet::AVAILABLE_CURRENCY);
    }

    public function testSetCurrencyWithInvalidCurrency(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid currency');
        $this->wallet->setCurrency('GBP');
    }

    public function testAddFund(): void
    {
        $initialBalance = $this->wallet->getBalance();
        $amount = 50.0;

        $this->wallet->addFund($amount);
        $this->assertEquals($initialBalance + $amount, $this->wallet->getBalance());

        // Test avec plusieurs ajouts
        $this->wallet->addFund($amount);
        $this->assertEquals($initialBalance + ($amount * 2), $this->wallet->getBalance());
    }

    public function testAddFundWithNegativeAmount(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid amount');
        $this->wallet->addFund(-50.0);
    }

    public function testRemoveFund(): void
    {
        // Préparation du portefeuille avec un solde initial
        $this->wallet->addFund(100.0);
        $initialBalance = $this->wallet->getBalance();
        $amount = 50.0;

        $this->wallet->removeFund($amount);
        $this->assertEquals($initialBalance - $amount, $this->wallet->getBalance());

        // Test avec plusieurs retraits
        $this->wallet->removeFund(25.0);
        $this->assertEquals(25.0, $this->wallet->getBalance());
    }

    public function testRemoveFundWithNegativeAmount(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid amount');
        $this->wallet->removeFund(-50.0);
    }

    public function testRemoveFundWithInsufficientFunds(): void
    {
        $this->wallet->addFund(50.0);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient funds');
        $this->wallet->removeFund(100.0);
    }

    public function testRemoveFundExactAmount(): void
    {
        $this->wallet->addFund(100.0);
        $this->wallet->removeFund(100.0);
        $this->assertEquals(0, $this->wallet->getBalance());
    }

    public function testFloatingPointPrecision(): void
    {
        $this->wallet->addFund(5);
        $this->wallet->addFund(6);
        $this->assertEquals(11, $this->wallet->getBalance(), '', 0.0001);
    }
}