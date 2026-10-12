# LOAN'S — 住宅購入・ローン比較管理アプリ

検討物件の登録・比較とローン試算を行う Web アプリケーションです。

**必須 PHP: 8.3 以上（推奨 8.4）**  
Laravel 13 のため、ホストの PHP 7.x では `php artisan` も `composer` も動きません。

## Windows / Docker で動かす（推奨）

ホスト PHP が古い場合は、**必ずコンテナ内**でコマンドを実行してください。

```powershell
cd D:\docker\LOANS
docker compose build
docker compose up -d
```

起動後:

- アプリ: http://localhost:8000
- MySQL: `mysql:3306`（ユーザー `laravel` / パスワード `secret` / DB `laravel`）

マイグレーションや artisan はホストではなくコンテナで:

```powershell
docker compose exec app php -v
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan storage:link
```

`vendor` がホストの古い PHP で作られている場合は、コンテナで入れ直します:

```powershell
docker compose exec app rm -rf vendor
docker compose exec app composer install
docker compose restart app
```

止めるとき:

```powershell
docker compose down
```

## セットアップ（PHP 8.3+ がホストにある場合）

```bash
composer install
cp .env.example .env   # 初回のみ
php artisan key:generate
npm install
npm run build
```

### SQLite（既定）

```bash
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
```

### MySQL

`.env` で MySQL を指定したうえでマイグレーションを実行してください。  
`property_images` テーブルは最新マイグレーションで追加されます。

```bash
php artisan migrate
php artisan storage:link
php artisan db:seed   # 任意
```

## 起動（ホスト PHP 8.3+）

```bash
php artisan serve --host=127.0.0.1 --port=8765
```

開発時に Vite を併用する場合:

```bash
npm run dev
```

## パフォーマンス（ローカル）

- セッション / キャッシュは `file`、キューは `sync`（`.env.example` 既定）。Docker MySQL への都度アクセスを避けます。
- Leaflet は npm 同梱し、地図がある画面だけ動的読み込みします。
- 物件一覧は表紙画像（1枚）のみ eager load します。

pull 後に重さが残る場合は `.env` を確認し、次のように揃えてください。

```env
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

## テスト

```bash
php artisan test
```

## 主な URL

| パス | 説明 |
|------|------|
| `/properties` | 候補物件一覧（1枚目画像 / NO IMAGE） |
| `/properties/create` | 物件を追加（間取り・築年・画像複数・郵便番号自動入力） |
| `/properties/{id}` | 物件詳細（画像閲覧・地図表示） |
| `/properties/{id}/edit` | 物件編集 |
| `/simulations` | シミュレーション履歴 |
| `/simulations/create` | ローン試算・審査判定 |
| `/simulations/{id}` | 試算結果（頭金概算・返済額・審査シグナル・補助金参考リンク） |

## 技術スタック

- PHP 8.3+ / Laravel 13
- Tailwind CSS 4
- Leaflet + OpenStreetMap（バンドル、地図ページのみ）
- SQLite（ローカル既定） / MySQL 対応
