# Weathertainment

<p align="center">
  <!-- ここに後でアプリのスクリーンショットなどを挿入すると、より魅力的になります -->
  <img src="" alt="アプリのスクリーンショット" width="700">
</p>

<p align="center">
  <strong>毎日天気を見る退屈さを、楽しみに変える。</strong><br>
  今日の「生活あるある」をユーモラスな確率で可視化する、新感覚のお天気エンターテイメントアプリです。
</p>

---

## チーム開発メンバー

*   **[あなたの名前]** - [GitHubアカウントへのリンク]
*   **[メンバーBの名前]** - [GitHubアカウントへのリンク]
*   **[メンバーCの名前]** - [GitHubアカウントへのリンク]

## 1. プロジェクトのコンセプト

**「天気予報をエンターテイメントにする」**

毎日の天気予報チェックという「作業」を、思わず誰かにシェアしたくなる「楽しみ」に変えることを目指しています。ユーザーの一日を、ほんの少しポジティブにするきっかけを提供します。

## 2. 使用技術（技術スタック）

*   **バックエンド:** Laravel (PHP)
*   **フロントエンド:** Laravel Blade, CSS, JavaScript
*   **データベース:** MySQL
*   **外部API:** OpenWeatherMap API
*   **開発環境:** XAMPP, VS Code
*   **バージョン管理:** Git / GitHub
*   **デプロイ先（予定）:** Heroku

## 3. 開発環境の構築手順

1.  このリポジトリをクローンします。
    ```bash
    git clone https://github.com/codingTaisey/weathertainment.git
    ```
2.  プロジェクトディレクトリに移動します。
    ```bash
    cd weathertainment
    ```
3.  `.env.example` ファイルをコピーして `.env` ファイルを作成します。
    ```bash
    # Windowsの場合
    copy .env.example .env
    # Mac/Linuxの場合
    # cp .env.example .env
    ```
4.  `.env` ファイルを編集し、データベース接続情報とOpenWeatherMapのAPIキーを設定します。
    ```env
    DB_DATABASE=weathertainment
    DB_USERNAME=root
    DB_PASSWORD=

    OPENWEATHERMAP_API_KEY="あなたのAPIキーをここに設定"
    ```
5.  Composerの依存関係をインストールします。
    ```bash
    composer install
    ```
6.  アプリケーションキーを生成します。
    ```bash
    php artisan key:generate
    ```
7.  XAMPP等で `weathertainment` という名前のデータベースを作成します。
8.  データベースのマイグレーションを実行します。
    ```bash
    php artisan migrate
    ```
9.  開発サーバーを起動します。
    ```bash
    php artisan serve
    ```
10. ブラウザで `http://127.0.0.1:8000` にアクセスします。

## 4. チーム開発のルール（ブランチ戦略）

*   `main`ブランチは常に正常に動作する安定版とします。**`main`ブランチへの直接のpushは禁止**です。
*   新機能の開発やバグ修正を行う際は、必ず`main`ブランチから新しいブランチを作成します。
    *   ブランチ名の例: `feature/ranking-function`, `fix/display-bug`
*   作業が完了したら、GitHubにブランチをpushし、**プルリクエスト（Pull Request）**を作成します。
*   他のメンバーによる**コードレビュー**でLGTM（Looks Good To Me）が出たら、`main`ブランチにマージします。