<?php

declare(strict_types=1);

final class CatalogService
{
    /** @var Product[] */
    private array $products = [];

    /** @var Category[] */
    private array $categories = [];

    public function __construct(
        private string $productsPath,
        private string $categoriesPath
    ) {
        $this->loadCategories();
        $this->loadProducts();
    }

    private function loadCategories(): void
    {
        if (!is_readable($this->categoriesPath)) {
            throw new RuntimeException('Categories file is not readable: ' . $this->categoriesPath);
        }

        $raw = file_get_contents($this->categoriesPath);
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            throw new RuntimeException('Invalid categories JSON.');
        }

        foreach ($data as $row) {
            $this->categories[(int)$row['id']] = new Category(
                (int)$row['id'],
                (string)$row['name']
            );
        }
    }

    private function loadProducts(): void
    {
        if (!is_readable($this->productsPath)) {
            throw new RuntimeException('Products file is not readable: ' . $this->productsPath);
        }

        $raw = file_get_contents($this->productsPath);
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            throw new RuntimeException('Invalid products JSON.');
        }

        foreach ($data as $row) {
            $this->products[] = new Product(
                (int)$row['id'],
                (string)$row['name'],
                (float)$row['price'],
                (int)$row['category_id'],
                (bool)($row['active'] ?? true)
            );
        }
    }

    /** @return Product[] */
    public function allProducts(): array
    {
        return $this->products;
    }

    /** @return Category[] */
    public function allCategories(): array
    {
        return array_values($this->categories);
    }

    public function findCategory(int $id): ?Category
    {
        return $this->categories[$id] ?? null;
    }

    /** Filter products by category id. 0 means all. */
    public function filterByCategory(int $categoryId = 0): array
    {
        if ($categoryId === 0) {
            return $this->products;
        }

        return array_values(array_filter(
            $this->products,
            fn(Product $p) => $p->categoryId() === $categoryId
        ));
    }

    /** Only active / visible products */
    public function activeProducts(): array
    {
        return array_values(array_filter(
            $this->products,
            fn(Product $p) => $p->isVisible()
        ));
    }

    /** Average price of given products (or all if empty) */
    public function averagePrice(?array $products = null): float
    {
        $list = $products ?? $this->products;

        if ($list === []) {
            return 0.0;
        }

        $sum = array_reduce(
            $list,
            fn(float $acc, Product $p) => $acc + $p->price(),
            0.0
        );

        return round($sum / count($list), 2);
    }

    /** Count of products in catalog (optionally filtered) */
    public function count(?array $products = null): int
    {
        return count($products ?? $this->products);
    }
}
