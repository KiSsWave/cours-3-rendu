<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\Person;
use App\Entity\Wallet;
use App\Entity\Product;

class PersonTest extends TestCase
{
    private Person $person;
    private string $name = "Clem JD";
    private string $defaultCurrency = "EUR";

    protected function setUp(): void
    {
        $this->person = new Person($this->name, $this->defaultCurrency);
    }

    public function testConstructor(): void
    {
        $this->assertEquals($this->name, $this->person->getName());
        $this->assertInstanceOf(Wallet::class, $this->person->getWallet());
        $this->assertEquals($this->defaultCurrency, $this->person->getWallet()->getCurrency());
    }

    public function testSetAndGetName(): void
    {
        $newName = "Jane Doe";
        $this->person->setName($newName);
        $this->assertEquals($newName, $this->person->getName());
    }

    public function testSetAndGetWallet(): void
    {
        $newWallet = new Wallet("USD");
        $this->person->setWallet($newWallet);
        $this->assertSame($newWallet, $this->person->getWallet());
    }

    public function testHasFund(): void
    {
        $this->assertFalse($this->person->hasFund());

        $this->person->getWallet()->addFund(100.0);
        $this->assertTrue($this->person->hasFund());

        $this->person->getWallet()->removeFund(100.0);
        $this->assertFalse($this->person->hasFund());
    }

    public function testTransfertFund(): void
    {
        $receiver = new Person("Jane Doe", "EUR");
        $amount = 50.0;

        $this->person->getWallet()->addFund(100.0);
        $initialBalance = $this->person->getWallet()->getBalance();

        $this->person->transfertFund($amount, $receiver);

        $this->assertEquals($initialBalance - $amount, $this->person->getWallet()->getBalance());
        $this->assertEquals($amount, $receiver->getWallet()->getBalance());
    }

    public function testTransfertFundWithDifferentCurrencies(): void
    {
        $receiver = new Person("Jane Doe", "USD");
        $this->person->getWallet()->addFund(100.0);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Can't give money with different currencies");

        $this->person->transfertFund(50.0, $receiver);
    }

    public function testDivideWallet(): void
    {
        $this->person->getWallet()->addFund(100.0);

        $person1 = new Person("Person 1", "EUR");
        $person2 = new Person("Person 2", "EUR");
        $person3 = new Person("Person 3", "EUR");

        $persons = [$person1, $person2, $person3];

        $this->person->divideWallet($persons);

        //Ce test ne dois pas fonctionner car la division n'est pas équitable
        $this->assertEquals(33.34, $person1->getWallet()->getBalance());

        //Ce test dois fonctionner car la division est équitable
        $this->assertEquals(33.33, $person2->getWallet()->getBalance());
        $this->assertEquals(33.33, $person3->getWallet()->getBalance());
        $this->assertEquals(0, $this->person->getWallet()->getBalance());
    }

    public function testDivideWalletWithDifferentCurrencies(): void
    {
        $this->person->getWallet()->addFund(100.0);

        $person1 = new Person("Person 1", "EUR");
        $person2 = new Person("Person 2", "USD");
        $person3 = new Person("Person 3", "EUR");

        $persons = [$person1, $person2, $person3];

        $this->person->divideWallet($persons);

        // Should only divide between EUR currency persons
        $this->assertEquals(50.0, $person1->getWallet()->getBalance());
        $this->assertEquals(0, $person2->getWallet()->getBalance());
        $this->assertEquals(50.0, $person3->getWallet()->getBalance());
    }

    public function testBuyProduct(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('listCurrencies')->willReturn(['EUR', 'USD']);
        $product->method('getPrice')->with('EUR')->willReturn(50.0);

        $this->person->getWallet()->addFund(100.0);
        $initialBalance = $this->person->getWallet()->getBalance();

        $this->person->buyProduct($product);

        $this->assertEquals($initialBalance - 50.0, $this->person->getWallet()->getBalance());
    }

    public function testBuyProductWithInvalidCurrency(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('listCurrencies')->willReturn(['USD', 'GBP']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Can't buy product with this wallet currency");

        $this->person->buyProduct($product);
    }
}