# Preview and Tools for makeshop WordPress option

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-GPLv2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

makeshopのWordPress連携オプション用のプレビュー機能と商品表示ブロックを提供するWordPressプラグインです。

## 概要

Preview and Tools for makeshop WordPress optionは、[makeshop](https://www.makeshop.jp/)のWordPress連携オプションを強化するプラグインです。ブロックエディタでのプレビュー・保存機能を有効化し、makeshop商品を美しく表示するカスタムGutenbergブロックを提供します。

## 主な機能

### ✨ ブロックエディタプレビュー
- WordPress連携オプション上でブロックエディタのプレビューと保存機能を有効化
- リアルタイムプレビューで編集結果を即座に確認

### 🛍️ 商品表示ブロック
- makeshop商品をグリッドレイアウトで表示するGutenbergブロック
- 商品URL（1行に1つ）を入力するだけで簡単に商品を表示
- レスポンシブ対応（デスクトップ/タブレット/モバイル）

### ⚙️ カスタマイズ可能なセレクタ
- CSSセレクタを使用して商品情報を柔軟に取得
- デフォルトセレクタも用意されているため、すぐに使用開始可能
- サイト構造に応じてセレクタを自由にカスタマイズ

### 🎨 表示制御
- 画像、カテゴリ、価格、説明の表示/非表示を個別に制御
- 商品名と説明の表示行数をCSS line-clampで制御
- デスクトップ、タブレット、モバイルで異なるグリッド列数を設定可能

### ⚡ パフォーマンス
- WordPress Transient APIによる1時間キャッシュで高速表示
- 手動での商品データ更新も可能
- REST APIによる非同期商品データ取得

### 🌏 完全日本語対応
- すべてのUI要素が日本語で表示
- 管理画面も日本語で分かりやすい

## スクリーンショット

### 商品表示ブロック（フロントエンド）
![商品表示ブロックのフロントエンド表示](screenshots/screenshot-1.png)

### ブロックエディタ
![ブロックエディタでの編集画面](screenshots/screenshot-2.png)

### 管理画面
![管理画面の設定ページ](screenshots/screenshot-3.png)

## インストール

### WordPress管理画面から

1. WordPress管理画面の「プラグイン」→「新規追加」を開く
2. 「Preview and Tools for makeshop WordPress option」を検索
3. 「今すぐインストール」をクリック
4. 「有効化」をクリック

### 手動インストール

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/yourusername/tools-for-makeshop-wp-option.git
cd tools-for-makeshop-wp-option
npm install
npm run build
```

その後、WordPress管理画面でプラグインを有効化します。

## 使い方

### 1. 初期設定

1. WordPress管理画面の「makeshopツール」→「makeshopツール設定」を開く
2. **プレビュー設定**でプレビュー機能を有効化（オプション）
3. **ブロック設定**で商品表示ブロックを有効化
4. 各商品情報のCSSセレクタを設定（デフォルトのまま使用も可能）

### 2. ブロックの使用

1. 投稿またはページの編集画面を開く
2. ブロック追加ボタンをクリック
3. 「makeshop Product Display」ブロックを追加
4. サイドバーの「商品URL」欄に商品URLを入力（1行に1つ）
5. グリッド設定で列数を調整
6. 必要に応じて「商品データを更新」ボタンで最新情報を取得

### 3. CSSセレクタのカスタマイズ

CSSセレクタの例：

```css
/* クラスを持つ要素 */
h1.product-title

/* 任意の要素のクラス */
.price

/* IDを持つ要素 */
#product-name

/* 属性値を取得 */
meta[property=og:image]::attr(content)

/* 複数セレクタ（OR） */
.price, .product-price

/* 入れ子の要素 */
div.product img::attr(src)
```

## 技術仕様

### 必要要件

- WordPress 5.0以上
- PHP 7.4以上
- makeshopのアカウントと商品ページ

### 技術スタック

- **フロントエンド**: React, @wordpress/block-editor, SCSS
- **バックエンド**: PHP 7.4+, WordPress API
- **ビルドツール**: @wordpress/scripts
- **スクレイピング**: DOMDocument, DOMXPath
- **キャッシング**: WordPress Transient API

### アーキテクチャ

```
tools-for-makeshop-wp-option/
├── src/                          # React/JSソースファイル
│   ├── index.js                  # ブロックのメインJS
│   ├── editor.scss               # エディタスタイル
│   └── style.scss                # フロントエンドスタイル
├── includes/                     # PHPクラスファイル
│   ├── class-block-product-display.php  # ブロック登録・レンダリング
│   └── class-makeshop-scraper.php       # 商品情報スクレイピング
├── languages/                    # 翻訳ファイル
│   ├── tools-for-makeshop-wp-option-ja.po
│   └── tools-for-makeshop-wp-option-ja.mo
├── build/                        # ビルド済みファイル（自動生成）
├── tools-for-makeshop-wp-option.php  # メインプラグインファイル
├── readme.txt                    # WordPress.org用README
└── README.md                     # このファイル
```

## 開発

### セットアップ

```bash
# 依存関係のインストール
npm install

# 開発モード（ウォッチモード）
npm start

# 本番用ビルド
npm run build
```

### コーディング規約

- PHP: [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- JavaScript: [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- CSS: [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)

### 翻訳の更新

```bash
cd languages
# .poファイルを編集後
msgfmt -o tools-for-makeshop-wp-option-ja.mo tools-for-makeshop-wp-option-ja.po
```

## トラブルシューティング

### 商品が表示されない

1. 商品URLが正しいか確認してください
2. CSSセレクタが商品ページのHTML構造と一致しているか確認してください
3. ブラウザの開発者ツールで商品ページのHTML構造を確認してください
4. 管理画面でCSSセレクタを調整してください

### キャッシュをクリアしたい

- ブロックエディタの「商品データを更新」ボタンをクリック
- または、フロントエンドでは1時間経過後に自動的に更新されます

### エラーメッセージ: "ページから商品情報の解析に失敗しました"

このエラーは、指定されたCSSセレクタで商品名または画像が見つからない場合に発生します。以下を確認してください：

1. 商品URLが有効か
2. CSSセレクタが正しいか
3. 商品ページのHTML構造が変更されていないか

## よくある質問

### Q: makeshopとは何ですか？

A: makeshopは、GMOメイクショップ株式会社が提供するECサイト構築サービスです。WordPress連携オプションを使用することで、WordPressとmakeshopを連携できます。

### Q: どのmakeshopサイトでも動作しますか？

A: 基本的にはすべてのmakeshopサイトで動作しますが、サイトによってHTML構造が異なる場合があります。その場合はCSSセレクタを調整することで対応できます。

### Q: 商品データはどのくらいの頻度で更新されますか？

A: デフォルトでは1時間ごとに自動更新されます。また、ブロックエディタの「商品データを更新」ボタンで手動更新も可能です。

## ライセンス

このプラグインは、[GPLv2以降](https://www.gnu.org/licenses/gpl-2.0.html)のライセンスで配布されています。

## クレジット

**開発者**: HASEGAWA Yoshihiro

## サポート

バグ報告や機能リクエストは、[GitHubのIssueページ](https://github.com/yourusername/tools-for-makeshop-wp-option/issues)でお願いします。

## 貢献

プルリクエストを歓迎します！大きな変更の場合は、まずIssueで変更内容について議論してください。

## 変更履歴

### 0.0.1 (2026-01-04)

- 初回リリース
- ブロックエディタプレビュー・保存機能
- 商品表示ブロックの追加
- CSSセレクタによる商品情報取得
- 表示項目の個別制御
- 商品名・説明の行数制限（line-clamp）
- レスポンシブグリッドレイアウト
- 1時間キャッシュ機能
- 日本語完全対応

---

Made with ❤️ for makeshop users
