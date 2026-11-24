# Практическая работа №5

**Дисциплина:** Технологии разработки программного обеспечения  
**Тема:** PHP ООП. Классы, конструкторы и модели предметной области

## Описание результата

PHP-приложение с объектной моделью каталога товаров:

- `Product` — сущность товара (id, name, price, categoryId, active)
- `Category` — сущность категории
- `CatalogService` — сервис загрузки JSON, фильтрации и агрегации

Данные берутся из `data/products.json` и `data/categories.json`. Интерфейс показывает карточки товаров, фильтр по категории и среднюю цену.

## Схема ответственности

```
data/*.json
      │
      ▼
CatalogService  ──►  Product[] / Category[]
      │
      ▼
public/index.php  ──►  HTML-карточки + форма фильтра
```

| Элемент            | Ответственность                                      |
|--------------------|------------------------------------------------------|
| `Product`          | Состояние одного товара, валидация, предметные методы |
| `Category`         | Состояние категории                                  |
| `CatalogService`   | Чтение dataset, создание объектов, фильтр, агрегаты  |
| `public/index.php` | HTTP-вход, фильтр, вывод HTML (без логики моделей)   |

## Требования к окружению

- PHP 8.0+ (используются typed properties, constructor property promotion, `declare(strict_types=1)`)
- Веб-сервер с поддержкой PHP **или** встроенный сервер PHP

## Запуск

```bash
# из корня репозитория
php -S localhost:8080 -t public
```

Откройте в браузере: http://localhost:8080

### Проверка ошибочного конструктора

http://localhost:8080/?demo_error=1

Появится сообщение `InvalidArgumentException` (id ≤ 0 и отрицательная цена).

## Структура каталогов

```
trpo-pr05-student/
├── data/
│   ├── products.json
│   ├── categories.json
│   └── class_requirements.csv
├── public/
│   └── index.php
├── src/
│   ├── Model/
│   │   ├── Product.php
│   │   └── Category.php
│   └── Service/
│       └── CatalogService.php
├── .gitignore
└── README.md
```

## Предметные методы Product (не только getter/setter)

1. `formattedPrice()` — цена с форматированием и валютой
2. `statusLabel()` — человекочитаемый статус
3. `isVisible()` — можно ли показывать в публичном каталоге
4. `priceWithDiscount(float $percent)` — цена со скидкой (без мутации объекта)

## Фильтрация и агрегация

- `CatalogService::filterByCategory(int $categoryId)` — фильтр по категории
- `CatalogService::averagePrice(?array $products)` — средняя цена
- `CatalogService::count(?array $products)` — количество

## Почему require_once неудобен при росте проекта

При увеличении числа классов ручные `require_once` быстро становятся источником ошибок (пропущенный файл, циклические зависимости, разный порядок подключения). Решение — автозагрузка (PSR-4 + Composer `spl_autoload_register` / `vendor/autoload.php`).

## Сценарий проверки

1. Запустить сервер по инструкции выше.
2. Убедиться, что отображаются все 12 товаров и 3 категории.
3. Выбрать категорию «Оборудование» — должны остаться товары с `category_id = 2`.
4. Проверить блок «Средняя цена» — значение пересчитывается для текущей выборки.
5. Открыть `?demo_error=1` — увидеть обработанное исключение конструктора.
