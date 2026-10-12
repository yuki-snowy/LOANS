# LOAN'S — 住宅購入・ローン比較管理アプリ

検討物件の登録・比較とローン試算を行う Web アプリケーションです。

**必須 PHP: 8.3 以上（Sail では 8.4）**  
ホストの PHP 7.x では `php artisan` / `composer` は使えません。

## Windows + Laravel Sail（推奨）

このリポジトリは Sail のサービス名 **`laravel.test`** を使います。  
`app` というサービスはありません。

### 1. 起動（ホストの PowerShell）

```powershell
cd D:\docker\LOANS
git pull
copy .env.example .env   # 初回のみ（既存 .env があるなら不要）
docker compose up -d --build
```

アプリ: http://localhost

### 2. マイグレーション（ホストの PowerShell）

コンテナの**外**（`PS D:\docker\LOANS>`）で実行します。

```powershell
docker compose exec laravel.test php -v
docker compose exec laravel.test php artisan migrate
docker compose exec laravel.test php artisan storage:link
docker compose exec laravel.test php artisan db:seed
```

または Sail ラッパーでも同じです。

```powershell
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan storage:link
```

### 3. よくある間違い

| やってしまったこと | 正しいやり方 |
|---|---|
| コンテナに入ったあと `docker compose exec app ...` | コンテナ内に `docker` はない。中では `php artisan migrate` だけ |
| `docker compose exec app ...` | サービス名は **`laravel.test`** |
| ホストで `php artisan`（PHP 7.x） | 必ず `docker compose exec laravel.test php artisan ...` |

コンテナに入っているとき（プロンプトが `root@...:/var/www/html#`）:

```bash
php -v
php artisan migrate
php artisan storage:link
exit
```

### 4. vendor を入れ直す場合（ホストの PowerShell）

```powershell
docker compose exec laravel.test rm -rf vendor
docker compose exec laravel.test composer install
docker compose restart laravel.test
```

### 5. 停止

```powershell
docker compose down
```

## .env（Sail / MySQL）

Sail 利用時の例:

```env
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

`Table 'laravel.property_images' doesn't exist` のときは、まだ migrate できていません。上の migrate を実行してください。

## ホストに PHP 8.3+ がある場合

```bash
composer install
cp .env.example .env
php artisan key:generate
# SQLite を使うなら DB_CONNECTION=sqlite にして database.sqlite を作成
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

## テスト

```bash
php artisan test
# Sail 上なら
docker compose exec laravel.test php artisan test
```

## 主な URL

| パス | 説明 |
|------|------|
| `/properties` | 候補物件一覧 |
| `/properties/create` | 物件を追加 |
| `/properties/{id}` | 物件詳細 |
| `/simulations/create` | ローン試算・審査判定 |

## 技術スタック

- PHP 8.3+ / Laravel 13
- Laravel Sail（PHP 8.4 + MySQL 8.4）
- Tailwind CSS 4
- Leaflet + OpenStreetMap
