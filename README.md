# LOAN'S — 住宅購入・ローン比較管理アプリ

検討物件の登録・比較とローン試算を行う Web アプリケーションです。

## Phase 1（実装済み）

- `properties` テーブルのマイグレーション
- `Property` モデル（バリデーションルール付き）
- `PropertyService` による CRUD ビジネスロジック
- `PropertyController` + Blade ビュー（Tailwind CSS）
- 詳細画面の地図表示（Leaflet + OpenStreetMap）
- PHPUnit によるバリデーション・CRUD テスト

## 技術スタック

- PHP 8.3+ / Laravel 13
- Tailwind CSS 4
- Leaflet + OpenStreetMap（有料地図 API は未使用）
- SQLite（ローカル既定）

## セットアップ

```bash
composer install
cp .env.example .env   # 初回のみ
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
```

## 起動

```bash
php artisan serve --host=127.0.0.1 --port=8765
```

ブラウザで http://127.0.0.1:8765 を開くと物件一覧に遷移します。

開発時に Vite を併用する場合:

```bash
npm run dev
```

## テスト

```bash
php artisan test
```

## 主な URL

| パス | 説明 |
|------|------|
| `/properties` | 物件一覧 |
| `/properties/create` | 物件登録 |
| `/properties/{id}` | 物件詳細（地図表示） |
| `/properties/{id}/edit` | 物件編集 |
