# LOAN'S — 住宅購入・ローン比較管理アプリ

検討物件の登録・比較とローン試算を行う Web アプリケーションです。

## Phase 1（実装済み）

- `properties` テーブルのマイグレーション
- `Property` モデル（バリデーションルール付き）
- `PropertyService` による CRUD ビジネスロジック
- `PropertyController` + Blade ビュー（Tailwind CSS）
- 詳細画面の地図表示（Leaflet + OpenStreetMap）
- PHPUnit によるバリデーション・CRUD テスト

## Phase 2（実装済み）

- `simulations` テーブル（物件紐付け・審査入力・計算結果スナップショット）
- `LoanCalculatorService`（元利均等返済・月額固定費合算・審査判定・審査金利ストレステスト）
- `SimulationService` / `SimulationController` + Blade UI
- 審査シグナル（安全 / 注意 / 危険）の色分け表示
- PHPUnit（計算エッジケース + シミュレーションフロー）

## Phase 3（実装済み）

- Leaflet ＋ OpenStreetMap（CDN、APIキー不要）
- 再利用可能な Blade コンポーネント `x-map` と `resources/js/loans-map.js`
- 物件一覧の複数ピンマップ、詳細の単一ピン（物件名・価格ポップアップ）
- 登録/編集時のインタラクティブ座標ピッカー（東京駅フォールバック）
## 技術スタック

- PHP 8.3+ / Laravel 13
- Tailwind CSS 4
- Leaflet + OpenStreetMap
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
| `/properties` | 候補物件一覧（1枚目画像 / NO IMAGE） |
| `/properties/create` | 物件を追加（間取り・築年・画像複数・郵便番号自動入力） |
| `/properties/{id}` | 物件詳細（画像閲覧・地図表示） |
| `/properties/{id}/edit` | 物件編集 |
| `/simulations` | シミュレーション履歴 |
| `/simulations/create` | ローン試算・審査判定 |
| `/simulations/{id}` | 試算結果（頭金概算・返済額・審査シグナル・補助金参考リンク） |
