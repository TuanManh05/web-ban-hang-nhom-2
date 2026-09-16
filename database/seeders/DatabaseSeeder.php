<?php

declare(strict_types=1);

require_once __DIR__ . '/../factories/ProductFactory.php';

final class DatabaseSeeder
{
    public function __construct(private PDO $pdo)
    {
    }

    public function run(): void
    {
        $this->pdo->beginTransaction();

        try {
            $userStatement = $this->pdo->prepare(
                'INSERT INTO users (name, email, password, role, status)
                 VALUES (:name, :email, :password, :role, 1)
                 ON DUPLICATE KEY UPDATE
                    name = VALUES(name), password = VALUES(password),
                    role = VALUES(role), status = 1'
            );

            foreach ([
                ['Quản trị viên', 'admin@nhom2.local', 'Admin@123', 'admin'],
                ['Khách hàng mẫu', 'customer@nhom2.local', 'Customer@123', 'customer'],
            ] as [$name, $email, $password, $role]) {
                $userStatement->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => $role,
                ]);
            }

            $categoryStatement = $this->pdo->prepare(
                'INSERT INTO categories (name, slug, status)
                 VALUES (:name, :slug, 1)
                 ON DUPLICATE KEY UPDATE name = VALUES(name), status = 1'
            );

            foreach ([
                ['Điện thoại', 'dien-thoai'],
                ['Laptop', 'laptop'],
                ['Phụ kiện', 'phu-kien'],
                ['Máy ảnh', 'may-anh'],
            ] as [$name, $slug]) {
                $categoryStatement->execute(['name' => $name, 'slug' => $slug]);
            }

            $categoryIds = $this->pdo
                ->query("SELECT id FROM categories WHERE status = 1 ORDER BY id")
                ->fetchAll(PDO::FETCH_COLUMN);

            $productStatement = $this->pdo->prepare(
                'INSERT INTO products
                    (category_id, name, slug, description, price, stock, status)
                 VALUES
                    (:category_id, :name, :slug, :description, :price, :stock, :status)'
            );

            foreach (ProductFactory::generate(15, $categoryIds) as $product) {
                $productStatement->execute($product);
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
