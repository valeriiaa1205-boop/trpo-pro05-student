<?php

declare(strict_types=1);

final class Product
{
    public function __construct(
        private int $id,
        private string $name,
        private float $price,
        private int $categoryId,
        private bool $active = true
    ) {
        if ($id <= 0) {
            throw new InvalidArgumentException('Product id must be a positive integer.');
        }

        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Product name cannot be empty.');
        }

        if ($price < 0) {
            throw new InvalidArgumentException('Product price cannot be negative.');
        }

        if ($categoryId <= 0) {
            throw new InvalidArgumentException('Product categoryId must be a positive integer.');
        }

        $this->name = $name;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): float
    {
        return $this->price;
    }

    public function categoryId(): int
    {
        return $this->categoryId;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /** Domain: formatted price for display */
    public function formattedPrice(): string
    {
        return number_format($this->price, 2, '.', ' ') . ' ₽';
    }

    /** Domain: short status label */
    public function statusLabel(): string
    {
        return $this->active ? 'В наличии' : 'Недоступен';
    }

    /** Domain: whether product can be shown in public catalog */
    public function isVisible(): bool
    {
        return $this->active && $this->price > 0;
    }

    /** Domain: apply discount percentage and return new price (does not mutate) */
    public function priceWithDiscount(float $percent): float
    {
        if ($percent < 0 || $percent > 100) {
            throw new InvalidArgumentException('Discount percent must be between 0 and 100.');
        }

        return round($this->price * (1 - $percent / 100), 2);
    }
}
