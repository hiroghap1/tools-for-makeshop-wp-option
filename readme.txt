=== Preview and Tools for makeshop WordPress option ===
Contributors: hasegawayoshihiro
Tags: makeshop, ecommerce, product-display, gutenberg, block
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 0.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

makeshopのWordPress連携オプション用のプレビュー機能と商品表示ブロックを提供します。

== Description ==

Preview and Tools for makeshop WordPress optionは、makeshopのWordPress連携オプションを強化するプラグインです。ブロックエディタでのプレビュー・保存機能を有効化し、makeshop商品を美しく表示するカスタムブロックを提供します。

= 主な機能 =

* **ブロックエディタプレビュー**: WordPress連携オプション上でブロックエディタのプレビューと保存機能を有効化
* **商品表示ブロック**: makeshop商品をグリッドレイアウトで表示するGutenbergブロック
* **カスタマイズ可能なセレクタ**: CSSセレクタを使用して商品情報を柔軟に取得
* **表示制御**: 画像、カテゴリ、価格、説明の表示/非表示を個別に制御
* **行数制限**: 商品名と説明の表示行数をCSS line-clampで制御
* **レスポンシブグリッド**: デスクトップ、タブレット、モバイルで異なる列数を設定可能
* **キャッシュ機能**: 1時間のキャッシュで高速表示、手動更新も可能
* **完全日本語対応**: すべてのUI要素が日本語で表示

= 技術的特徴 =

* HTML scraping による商品情報取得
* CSS to XPath 変換による柔軟なセレクタ指定
* WordPress Transient API によるキャッシング
* REST API による非同期商品データ取得
* SEOとアクセシビリティを考慮したDOM構造

= 使用方法 =

1. プラグインを有効化
2. WordPress管理画面の「makeshopツール設定」で設定を行う
3. 投稿またはページの編集画面で「makeshop Product Display」ブロックを追加
4. サイドバーに商品URLを入力（1行に1つ）
5. グリッドレイアウトや表示項目をカスタマイズ

= 必要要件 =

* WordPress 5.0以上
* PHP 7.4以上
* makeshopのアカウントと商品ページ

== Installation ==

= 自動インストール =

1. WordPress管理画面の「プラグイン」→「新規追加」を開く
2. 「Preview and Tools for makeshop WordPress option」を検索
3. 「今すぐインストール」をクリック
4. 「有効化」をクリック

= 手動インストール =

1. プラグインファイルをダウンロード
2. `/wp-content/plugins/tools-for-makeshop-wp-option/` ディレクトリにアップロード
3. WordPress管理画面の「プラグイン」メニューからプラグインを有効化

= 初期設定 =

1. WordPress管理画面の「makeshopツール」→「makeshopツール設定」を開く
2. 「プレビュー設定」でプレビュー機能を有効化（オプション）
3. 「ブロック設定」で商品表示ブロックを有効化
4. 各商品情報のCSSセレクタを設定（デフォルトのまま使用も可能）
5. 表示項目の有効/無効を設定
6. 行数制限を設定（0で制限なし）

== Frequently Asked Questions ==

= makeshopとは何ですか？ =

makeshopは、GMOメイクショップ株式会社が提供するECサイト構築サービスです。WordPress連携オプションを使用することで、WordPressとmakeshopを連携できます。

= どのmakeshopサイトでも動作しますか？ =

基本的にはすべてのmakeshopサイトで動作しますが、サイトによってHTML構造が異なる場合があります。その場合はCSSセレクタを調整することで対応できます。

= CSSセレクタの設定方法を教えてください =

管理画面の「ブロック設定」セクションに、各商品情報（商品名、画像、カテゴリ、価格、説明）のCSSセレクタを入力できます。例：
* `h1.product-title` - クラスを持つ要素
* `.price` - 任意の要素のクラス
* `#product-name` - IDを持つ要素
* `meta[property=og:image]::attr(content)` - 属性値を取得
* `.price, .product-price` - 複数セレクタ（OR）

= キャッシュはどのように機能しますか？ =

商品情報は1時間キャッシュされます。ブロックエディタの「商品データを更新」ボタンで手動更新も可能です。フロントエンドでは、最終取得時刻から1時間以上経過している場合、自動的に再取得されます。

= 商品が表示されない場合は？ =

1. 商品URLが正しいか確認
2. CSSセレクタが商品ページのHTML構造と一致しているか確認
3. ブラウザの開発者ツールで商品ページのHTML構造を確認
4. 管理画面でCSSセレクタを調整

= レスポンシブ対応していますか？ =

はい。デスクトップ、タブレット、モバイルで異なる列数を設定できます。デフォルトでは、デスクトップ4列、タブレット3列、モバイル2列です。

== Screenshots ==

1. 商品表示ブロックのフロントエンド表示
2. ブロックエディタでの編集画面
3. 管理画面の設定ページ - ブロック設定
4. 商品グリッドのレスポンシブ表示

== Changelog ==

= 0.0.1 =
* 初回リリース
* ブロックエディタプレビュー・保存機能
* 商品表示ブロックの追加
* CSSセレクタによる商品情報取得
* 表示項目の個別制御
* 商品名・説明の行数制限（line-clamp）
* レスポンシブグリッドレイアウト
* 1時間キャッシュ機能
* 日本語完全対応

== Upgrade Notice ==

= 0.0.1 =
初回リリースです。

== Additional Information ==

= サポート =

バグ報告や機能リクエストは、GitHubのIssueページでお願いします。

= 開発 =

このプラグインの開発にご興味がある方は、GitHubリポジトリをご覧ください。プルリクエストを歓迎します。

= ライセンス =

このプラグインはGPLv2以降のライセンスで配布されています。

= クレジット =

開発者: HASEGAWA Yoshihiro
