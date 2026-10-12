# LOAN'S

住宅購入の候補物件管理と、ローン試算・審査判定を行う Web アプリです。

## 機能

- **候補物件**: 登録・一覧・詳細・編集・削除
- **物件情報**: 種目 / 間取り / 築年 / 専有面積 / 構造・階建て / 駅徒歩 / 評価 / メモ
- **画像**: 複数枚登録（クライアント側で圧縮）、一覧は1枚目、未登録は NO IMAGE
- **地図**: Leaflet + 国土地理院タイル。郵便番号・住所検索で地図表示
- **ローン試算**: 月々返済・総返済・頭金概算・審査シグナル（安全 / 注意 / 危険）
- **補助金案内**: 結果画面に公式サイトへの参考リンク（`config/subsidies.php`）

認証・外部課金 API は使いません。ローカル / Docker で完結します。

## 必要環境

| 項目 | 内容 |
|------|------|
| PHP | 8.3 以上（Sail 利用時は 8.4） |
| Node.js | 20 系推奨（フロントビルド用） |
| DB | MySQL 8（Sail）または SQLite |
| Docker | Sail 利用時に必要 |

ホストの PHP 7.x では動作しません。

## はじめ方（Laravel Sail / 推奨）

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

ブラウザ: http://localhost

Sail のアプリサービス名は **`laravel.test`** です。

```bash
docker compose exec laravel.test php artisan migrate
docker compose exec laravel.test php artisan test
./vendor/bin/sail artisan migrate   # 同等
```

## ホストで動かす場合（PHP 8.3+）

```bash
composer install
cp .env.example .env
php artisan key:generate
# .env で DB_CONNECTION=sqlite にし、database/database.sqlite を作成
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

## 地図・住所検索（API キー不要）

| 用途 | サービス |
|------|----------|
| 地図タイル | 国土地理院 |
| 住所 → 座標 | 国土地理院 住所検索 |
| 郵便番号 → 住所 | zipcloud |
| 座標 → 住所 | HeartRails Geo API（ボタン実行） |

検索結果はサーバー側でキャッシュします。

## 主な画面

| パス | 内容 |
|------|------|
| `/properties` | 候補物件一覧 |
| `/properties/create` | 物件追加 |
| `/properties/{id}` | 物件詳細 |
| `/simulations/create` | ローン試算・審査判定 |
| `/simulations/{id}` | 試算結果 |

## テスト

```bash
php artisan test
# または
docker compose exec laravel.test php artisan test
```

## 技術スタック

- Laravel 13 / PHP 8.3+
- Blade + Tailwind CSS 4 + Vite
- Laravel Sail（PHP 8.4 + MySQL 8.4）
- Leaflet + 国土地理院タイル

## ライセンス

[MIT](LICENSE)
