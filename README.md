# LOAN'S

候補物件の管理と住宅ローン試算・審査判定を行う Web アプリ。

## 必要環境

| 項目 | 内容 |
|------|------|
| PHP | 8.3+（Sail は 8.4） |
| Node.js | 20+ |
| DB | MySQL 8（Sail）または SQLite |
| Docker | Sail 利用時 |

## セットアップ（Sail）

```bash
git clone https://github.com/yuki-snowy/LOANS.git
cd LOANS
cp .env.example .env
docker compose up -d --build
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate --seed
docker compose exec laravel.test php artisan storage:link
docker compose exec laravel.test npm install
docker compose exec laravel.test npm run build
```

http://localhost

Artisan / npm / テストは `docker compose exec laravel.test …` で実行する。

## セットアップ（ホスト）

```bash
composer install
cp .env.example .env
php artisan key:generate
# DB_CONNECTION=sqlite / database/database.sqlite
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

## 画面

| パス | 内容 |
|------|------|
| `/` | トップ |
| `/properties` | 候補物件 |
| `/properties/create` | 物件追加 |
| `/simulations` | シミュレーション履歴 |
| `/simulations/create` | ローン試算 |

## ライセンス

[MIT](LICENSE)
