# LOAN'S — 住宅購入・ローン比較管理アプリ

検討物件の登録・比較とローン試算を行う Web アプリケーションです。

## セットアップ

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

### MySQL / Docker（`DB_HOST=mysql` など）

`.env` で MySQL を指定したうえで、**必ずマイグレーションを実行**してください。  
`property_images` テーブルは最新マイグレーションで追加されます。

```bash
php artisan migrate
php artisan storage:link
# サンプルデータが必要なら
php artisan db:seed
```

コードを pull したあとに `Table '...property_images' doesn't exist` が出る場合も、同じく:

```bash
php artisan migrate
```

## 起動

```bash
php artisan serve --host=127.0.0.1 --port=8765
```

ブラウザで http://127.0.0.1:8765 を開くと候補物件に遷移します。

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
