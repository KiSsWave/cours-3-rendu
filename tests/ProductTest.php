<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\Product;
use App\Entity\Wallet;

class ProductTest extends TestCase
{
    private Product $product;
    private string $defaultName = "Test Product";
    private array $defaultPrices = ["EUR" => 10.0, "USD" => 12.0];
    private string $defaultType = "food";

    protected function setUp(): void
    {
        $this->product = new Product($this->defaultName, $this->defaultPrices, $this->defaultType);
    }

    public function testConstructor(): void
    {
        $this->assertEquals($this->defaultName, $this->product->getName());
        $this->assertEquals($this->defaultPrices, $this->product->getPrices());
        $this->assertEquals($this->defaultType, $this->product->getType());
    }

    public function testSetAndGetName(): void
    {
        $newName = "New Product Name";
        $this->product->setName($newName);
        $this->assertEquals($newName, $this->product->getName());
    }

    public function testSetAndGetType(): void
    {
        $validTypes = ['food', 'tech', 'alcohol', 'other'];

        foreach ($validTypes as $type) {
            $this->product->setType($type);
            $this->assertEquals($type, $this->product->getType());
        }
    }

    public function testSetTypeWithInvalidType(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid type');
        $this->product->setType('invalid_type');
    }

    public function testSetAndGetPrices(): void
    {
        $newPrices = [
            "EUR" => 15.0,
            "USD" => 18.0
        ];
        $this->product->setPrices($newPrices);
        $this->assertEquals($newPrices, $this->product->getPrices());
    }

    public function testSetPricesFiltersInvalidCurrencies(): void
    {
        $prices = [
            "EUR" => 15.0,
            "GBP" => 13.0,  // Devise invalide
            "USD" => 18.0
        ];
        $expectedPrices = [
            "EUR" => 15.0,
            "USD" => 18.0
        ];
        $this->product->setPrices($prices);
        $this->assertEquals($expectedPrices, $this->product->getPrices());
    }

    public function testSetPricesFiltersNegativePrices(): void
    {
        $prices = [
            "EUR" => 15.0,
            "USD" => -18.0  // Prix négatif
        ];
        $expectedPrices = [
            "EUR" => 15.0
        ];
        $this->product->setPrices($prices);
        $this->assertEquals($expectedPrices, $this->product->getPrices());
    }

    public function testGetTVAForFoodProduct(): void
    {
        $this->product->setType('food');
        $this->assertEquals(0.1, $this->product->getTVA());
    }

    public function testGetTVAForNonFoodProduct(): void
    {
        $types = ['tech', 'alcohol', 'other'];
        foreach ($types as $type) {
            $this->product->setType($type);
            $this->assertEquals(0.2, $this->product->getTVA());
        }
    }

    public function testListCurrencies(): void
    {
        $expected = ['EUR', 'USD'];
        $this->assertEquals($expected, $this->product->listCurrencies());


        $this->product->setPrices(['EUR' => 15.0]);
        $this->assertEquals(['EUR'], $this->product->listCurrencies());
    }

    public function testGetPrice(): void
    {
        $this->assertEquals(10.0, $this->product->getPrice('EUR'));
        $this->assertEquals(12.0, $this->product->getPrice('USD'));
    }

    public function testGetPriceWithInvalidCurrency(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid currency');
        $this->product->getPrice('GBP');
    }

    public function testGetPriceWithUnavailableCurrency(): void
    {
        $product = new Product($this->defaultName, ['EUR' => 10.0], $this->defaultType);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Currency not available for this product');
        $product->getPrice('USD');
    }

    public function testMultiplePriceOperations(): void
    {
        // Test complet des opérations sur les prix
        $initialPrices = ['EUR' => 10.0, 'USD' => 12.0];
        $this->product->setPrices($initialPrices);

        // Vérifie les prix initiaux
        $this->assertEquals($initialPrices, $this->product->getPrices());

        // Modifie un prix
        $newPrices = ['EUR' => 15.0, 'USD' => 18.0];
        $this->product->setPrices($newPrices);

        // Vérifie que les prix ont été mis à jour
        $this->assertEquals($newPrices, $this->product->getPrices());
        $this->assertEquals(['EUR', 'USD'], $this->product->listCurrencies());
        $this->assertEquals(15.0, $this->product->getPrice('EUR'));
    }
}