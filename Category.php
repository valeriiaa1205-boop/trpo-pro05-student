<?php

declare(strict_types=1);

final class Category
{
    public function __construct(
        private int $id,
        private string $name
    ) {
        if ($id <= 0) {
            throw new InvalidArgumentException('Category id must be a positive integer.');
        }

        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Category name cannot be empty.');
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

    /** Domain: slug-like identifier for URLs / filters */
    public function slug(): string
    {
        $map = [
            'Аксессуары' => 'accessories',
            'Оборудование' => 'equipment',
            'Учебные материалы' => 'materials',
        ];

        return $map[$this->name] ?? ('cat-' . $this->id);
    }
}
