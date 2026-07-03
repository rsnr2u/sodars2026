<?php

declare(strict_types=1);

namespace App\Core\ValueObjects;

use InvalidArgumentException;

final class Money
{
    private int $amount;

    private Currency $currency;

    public function __construct(int $amount, Currency $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function add(Money $other): Money
    {
        $this->assertSameCurrency($other);

        return new self($this->amount + $other->getAmount(), $this->currency);
    }

    public function subtract(Money $other): Money
    {
        $this->assertSameCurrency($other);

        return new self($this->amount - $other->getAmount(), $this->currency);
    }

    public function multiply(float $multiplier): Money
    {
        return new self((int) round($this->amount * $multiplier), $this->currency);
    }

    private function assertSameCurrency(Money $other): void
    {
        if (! $this->currency->equals($other->getCurrency())) {
            throw new InvalidArgumentException('Currencies must match for money calculations.');
        }
    }

    public function formatted(): string
    {
        // Simple decimal division format output helper.
        $decimal = number_format($this->amount / 100, 2);

        return $this->currency->getCode().' '.$decimal;
    }
}
