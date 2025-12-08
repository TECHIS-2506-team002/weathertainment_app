#!/bin/bash

# 1. PHP (8.3) をダウンロードして解凍
curl -sL https://github.com/shivammathur/php-builder-builds/releases/latest/download/php-8.3-micro-linux-x86_64.tar.xz | tar -xJ

# 2. Composerをセットアップ
./php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
./php composer-setup.php

# 3. Laravelの依存関係をインストール
./php composer.phar install --no-dev --optimize-autoloader

# 4. フロントエンドのビルド
npm install
npm run build

# デプロイのたびに毎回リセット(キャッシュを強制削除）
./php artisan config:clear
./php artisan cache:clear