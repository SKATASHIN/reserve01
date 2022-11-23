#Reserve

#ダウンロード方法

git clone https://github.com/SKATASHIN/reserve01.git

git clone ブランチ指定してダウンロードする場合

git clone -b ブランチ名 https://github.com/SKATASHIN/reserve01.git

もしくはzipファイルでダウンロードして下さい

## インストール方法

- composer install
- npm install
- npm run dev

.env.example をコピーして、.envファイルを作成

.envファイルの中の下記をご利用の環境に合わせて変更してください。

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reserve-a
DB_USERNAME=katashin07
DB_PASSWORD=password123

XMAPP/MAMPまたは他の開発環境でDBを起動した後に、

php artisan migrate:fresh --seed

と実行してください。（データベーステーブルとダミーデータが追加されれば良いです。）

最後に
php artisan key:generate
と入力してキーを生成後、

php artisan serve
で簡易サーバーを立ち上げ、表示確認してください。

## インストール後の実施事項

画像のリンク
php artisan storage:link

 プロフィルページで画像アップロード機能を使う場合は、
 .envのAPP_URLを書きに変更してください。

 # APP_URL=http://localhost
APP_URL=http://127.0.0.1:8000

