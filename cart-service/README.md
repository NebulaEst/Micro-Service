# Cart Service

Laravel 13 API service backed by its own PostgreSQL database. It runs next
to the Express/MySQL Product Service; the two services communicate through
their APIs and do not share a database.

## Run both services with Docker

Requirements: Docker Desktop, PHP 8.3+, and Composer 2. From the
`Micro-Service` project root, install the Cart Service dependencies and
initialize its environment and application key once:

```powershell
Set-Location cart-service
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
if (-not (Select-String -Path .env -Pattern '^APP_KEY=base64:' -Quiet)) { php artisan key:generate }
Set-Location ..
```

Build and start both services and their databases:

```powershell
docker compose up --build -d
docker compose ps
```

The Cart Service is available at `http://localhost:3001`, Product Service at
`http://localhost:3000`. The Cart Service runs its migrations on startup.
Follow its startup logs with `docker compose logs -f cart-service`.

To run only Cart Service and PostgreSQL, use the Compose file in this folder:

```powershell
Set-Location cart-service
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
if (-not (Select-String -Path .env -Pattern '^APP_KEY=base64:' -Quiet)) { php artisan key:generate }
docker compose up --build -d
```

## Run Cart Service locally

With PostgreSQL running through `cart-service/docker-compose.yml`, install the
PHP dependencies, migrate the database, and start Laravel:

```powershell
Set-Location cart-service
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
if (-not (Select-String -Path .env -Pattern '^APP_KEY=base64:' -Quiet)) { php artisan key:generate }
docker compose up -d cart-db
php artisan migrate
php artisan serve --port=3001
```

Requires PHP 8.3+, Composer 2, and the `pdo_pgsql` extension. For local
development, `.env.example` points to PostgreSQL on `127.0.0.1`; Compose
overrides the host with `cart-db` inside the container network.

## Cart API

All responses are JSON. Invalid request bodies return `422`; missing cart
items and unmatched routes return `404`.

| Method | Path | Description |
| --- | --- | --- |
| `GET` | `/carts/{userId}` | Get a user's items and total quantity |
| `POST` | `/carts` | Add an item, or increase its quantity if it already exists |
| `PUT` | `/carts/items/{id}` | Set an item's quantity |
| `DELETE` | `/carts/items/{id}` | Remove an item |
| `DELETE` | `/carts/{userId}` | Empty a user's cart |

Example request bodies:

```json
{"user_id": 1, "product_id": 1, "quantity": 2}
```

```json
{"quantity": 5}
```

Run the Cart Service test suite with `php artisan test`.
