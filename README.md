# Shoproom

Shoproom is a small shop management app built with Laravel 12, Vue 3, and MySQL. Administrators manage shops, owner accounts, and the shared product catalog. Shop owners choose catalog items or add their own, then set the price, details, thumbnail, and visibility for their shop.

## Requirements

- PHP 8.2 or newer with `pdo_mysql`, `mbstring`, `openssl`, `xml`, `curl`, `fileinfo`, and `tokenizer`
- Composer 2
- Node.js 20.19+ or 22.12+ with npm
- MySQL 8+ or MariaDB 10.5+

## Setup

1. Clone the repository and enter the project directory.

   ```bash
   git clone https://github.com/SiddiqueAb/shoproom-ecommerce-management.git
   cd shoproom-ecommerce-management
   ```

2. Create a database and a database user. For MySQL, for example:

   ```sql
   CREATE DATABASE shoproom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'shoproom'@'127.0.0.1' IDENTIFIED BY 'choose-a-local-password';
   GRANT ALL PRIVILEGES ON shoproom.* TO 'shoproom'@'127.0.0.1';
   FLUSH PRIVILEGES;
   ```

3. Install the PHP dependencies and create your local environment file.

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

4. Set `DB_PASSWORD` in `.env` to the password you used when creating the database user. Set `APP_URL` to `http://127.0.0.1:8000` for the local server below.

5. Create the tables and sample accounts.

   ```bash
   php artisan migrate --seed
   ```

6. Install the frontend dependencies and build the assets.

   ```bash
   npm install
   npm run build
   ```

7. Start the application.

   ```bash
   php artisan serve
   ```

   Open [http://127.0.0.1:8000](http://127.0.0.1:8000). During development, `npm run dev` can be used in a second terminal instead of rebuilding after each frontend change.

## Sample logins

The seeder creates these accounts with the password `password`:

| Role | Email | Shop |
| --- | --- | --- |
| Administrator | `admin@example.com` | All shops |
| Shop owner | `owner@example.com` | Green Basket |
| Shop owner | `maya@example.com` | Little Market |

These are development accounts for evaluation. Change the passwords or remove the sample accounts before using a shared or production environment.

## How it works

- The shared `products` table holds catalog details and records who added each product.
- The `shop_product` table assigns a product to a shop and stores that shop's price, description, thumbnail URL, and customer visibility.
- A product in the shared catalog is not shown to customers until it has an active assignment in an active shop.
- Deleting a shop removes its assignments. Deleting a catalog product removes it from every shop.
- The seed data includes an unassigned product to show that catalog items are not automatically visible in storefronts.

Administrators can manage shops, assign owners, manage owner accounts, edit catalog products, and control shop listings. Shop owners can browse the shared catalog, list products in their assigned shop, create products that join the catalog, and manage shop-specific listing details. Customer pages are available at `/shop/{shop-slug}`.

## Fresh install notes

`composer.lock` and `package-lock.json` are included. Migrations and sample data are in `database/migrations` and `database/seeders`. The application uses Laravel sessions and file-based cache by default, so no Redis or queue worker is needed for a local run.
