<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';

final class HomeController
{
    public function index(): void
    {
        $products = Product::featured(8);
        $featuredCategories = Product::featuredCategories();

        require __DIR__ . '/../views/home.php';
    }
}
