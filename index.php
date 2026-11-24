<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Model/Product.php';
require_once __DIR__ . '/../src/Model/Category.php';
require_once __DIR__ . '/../src/Service/CatalogService.php';

$baseDir = dirname(__DIR__);
$productsPath = $baseDir . '/data/products.json';
$categoriesPath = $baseDir . '/data/categories.json';

$error = null;
$catalog = null;

try {
    $catalog = new CatalogService($productsPath, $categoriesPath);
} catch (Throwable $e) {
    $error = $e->getMessage();
}

// Filter handling (not inside Product)
$selectedCategoryId = 0;
if (isset($_GET['category'])) {
    $selectedCategoryId = filter_var($_GET['category'], FILTER_VALIDATE_INT);
    if ($selectedCategoryId === false || $selectedCategoryId < 0) {
        $selectedCategoryId = 0;
    }
}

$products = [];
$categories = [];
$averagePrice = 0.0;
$totalCount = 0;

if ($catalog !== null) {
    $categories = $catalog->allCategories();
    $products = $catalog->filterByCategory($selectedCategoryId);
    $averagePrice = $catalog->averagePrice($products);
    $totalCount = $catalog->count($products);
}

// Demo of invalid constructor (for requirement #10) — only when ?demo_error=1
$demoError = null;
if (isset($_GET['demo_error'])) {
    try {
        new Product(0, 'Bad', -10, 0);
    } catch (InvalidArgumentException $e) {
        $demoError = $e->getMessage();
    }
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог товаров — Практическая работа №5</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f4f6fb;
            color: #1f2937;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.45;
        }
        header {
            background: #111827;
            color: #fff;
            padding: 20px 38px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .brand { font-weight: 800; font-size: 22px; }
        .wrap { max-width: 1100px; margin: 34px auto; padding: 0 24px; }
        .hero {
            background: #fff;
            border-radius: 18px;
            padding: 32px;
            box-shadow: 0 12px 36px rgba(0,0,0,.06);
            margin-bottom: 24px;
        }
        .pill {
            display: inline-block;
            background: #e9efff;
            color: #315efb;
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 13px;
            margin-bottom: 10px;
        }
        .muted { color: #6b7280; }
        .stats {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
            margin-top: 16px;
        }
        .stat {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px 18px;
            min-width: 140px;
        }
        .stat strong { display: block; font-size: 20px; }
        form.filter {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        select, button {
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            font-size: 15px;
        }
        button {
            background: #315efb;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        button:hover { background: #2448c9; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 16px;
        }
        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .card b { font-size: 17px; }
        .price { font-weight: 700; color: #111827; font-size: 18px; }
        .status-ok { color: #059669; }
        .status-off { color: #9ca3af; }
        .alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .empty {
            background: #fff;
            border: 1px dashed #d1d5db;
            border-radius: 14px;
            padding: 40px;
            text-align: center;
            color: #6b7280;
        }
        footer {
            text-align: center;
            color: #9ca3af;
            padding: 30px 20px;
            font-size: 14px;
        }
        @media (max-width: 760px) {
            header { padding: 16px 20px; }
            .wrap { margin-top: 20px; }
        }
    </style>
</head>
<body>
<header>
    <div class="brand">Каталог товаров</div>
    <span>ТРПО · Практика 5</span>
</header>

<div class="wrap">
    <?php if ($error !== null): ?>
        <div class="alert">Ошибка загрузки каталога: <?= e($error) ?></div>
    <?php else: ?>

    <div class="hero">
        <span class="pill">PHP ООП · модели предметной области</span>
        <h1>Каталог товаров</h1>
        <p class="muted">Данные загружаются из <code>data/products.json</code> и <code>data/categories.json</code> через <code>CatalogService</code>. Объекты <code>Product</code> и <code>Category</code> передаются в представление.</p>

        <div class="stats">
            <div class="stat">
                <span class="muted">Товаров в выборке</span>
                <strong><?= (int)$totalCount ?></strong>
            </div>
            <div class="stat">
                <span class="muted">Средняя цена</span>
                <strong><?= e(number_format($averagePrice, 2, '.', ' ')) ?> ₽</strong>
            </div>
            <div class="stat">
                <span class="muted">Категорий</span>
                <strong><?= count($categories) ?></strong>
            </div>
        </div>

        <form class="filter" method="get" action="">
            <label for="category">Категория:</label>
            <select name="category" id="category">
                <option value="0"<?= $selectedCategoryId === 0 ? ' selected' : '' ?>>Все категории</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat->id() ?>"<?= $selectedCategoryId === $cat->id() ? ' selected' : '' ?>>
                        <?= e($cat->name()) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Применить фильтр</button>
            <?php if ($selectedCategoryId !== 0): ?>
                <a href="?" style="margin-left:8px;color:#315efb;">Сбросить</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($demoError !== null): ?>
        <div class="alert">Демонстрация InvalidArgumentException: <?= e($demoError) ?></div>
    <?php endif; ?>

    <?php if ($products === []): ?>
        <div class="empty">
            <p>Нет товаров, соответствующих выбранному фильтру.</p>
        </div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($products as $product): ?>
                <?php
                    $category = $catalog->findCategory($product->categoryId());
                    $categoryName = $category ? $category->name() : 'Без категории';
                ?>
                <article class="card">
                    <span class="pill"><?= e($categoryName) ?></span>
                    <b><?= e($product->name()) ?></b>
                    <div class="price"><?= e($product->formattedPrice()) ?></div>
                    <div class="<?= $product->isActive() ? 'status-ok' : 'status-off' ?>">
                        <?= e($product->statusLabel()) ?>
                    </div>
                    <div class="muted">ID: <?= (int)$product->id() ?></div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<footer>
    Практическая работа №5 · PHP ООП · Классы, конструкторы и модели предметной области
</footer>
</body>
</html>
