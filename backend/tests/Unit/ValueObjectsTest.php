<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\ValueObjects\Coordinates;
use App\Core\ValueObjects\Currency;
use App\Core\ValueObjects\EmailAddress;
use App\Core\ValueObjects\Money;
use App\Core\ValueObjects\PhoneNumber;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ValueObjectsTest extends TestCase
{
    public function test_currency_and_money_calculations(): void
    {
        $inr = new Currency('INR');
        $this->assertEquals('INR', $inr->getCode());

        $m1 = new Money(10000, $inr); // ₹100.00
        $m2 = new Money(5000, $inr);  // ₹50.00

        $sum = $m1->add($m2);
        $this->assertEquals(15000, $sum->getAmount());
        $this->assertEquals('INR 150.00', $sum->formatted());

        $diff = $m1->subtract($m2);
        $this->assertEquals(5000, $diff->getAmount());

        $product = $m2->multiply(1.5);
        $this->assertEquals(7500, $product->getAmount());
    }

    public function test_currency_mismatch_throws_exception(): void
    {
        $inr = new Currency('INR');
        $usd = new Currency('USD');

        $m1 = new Money(100, $inr);
        $m2 = new Money(100, $usd);

        $this->expectException(InvalidArgumentException::class);
        $m1->add($m2);
    }

    public function test_coordinates_validation(): void
    {
        $coords = new Coordinates(16.3067, 80.4365);
        $this->assertEquals(16.3067, $coords->getLatitude());
        $this->assertEquals(80.4365, $coords->getLongitude());

        $this->expectException(InvalidArgumentException::class);
        new Coordinates(95.0, 80.0);
    }

    public function test_phone_number_validation(): void
    {
        $phone = new PhoneNumber('+919876543210');
        $this->assertEquals('+919876543210', $phone->getNumber());

        $this->expectException(InvalidArgumentException::class);
        new PhoneNumber('invalid-phone');
    }

    public function test_email_address_validation(): void
    {
        $email = new EmailAddress('info@sodars.com');
        $this->assertEquals('info@sodars.com', $email->getEmail());

        $this->expectException(InvalidArgumentException::class);
        new EmailAddress('invalid-email');
    }
}
