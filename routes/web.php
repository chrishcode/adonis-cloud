<?php

use Illuminate\Support\Facades\Route;
use Spatie\Ssh\Ssh;
use MysqlStreamDriver;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/createsite', function (Request $request) {
    $databaseName = 'happy9';
    $domainName = 'happy9.com';
    $gitRepo = 'chrishcode/adoniscasts-flare';

    $process = Ssh::create('root', '139.59.128.156')->execute([
        'mysql -u root -p -e "create database ' . $databaseName . ';"',
        'mysql -u root -p -e "GRANT ALL ON ' . $databaseName . '.* TO "root"@"localhost"";',
        'mysql -u root -p -e "ALTER USER "root"@"localhost" IDENTIFIED WITH mysql_native_password BY """;',
        'mysql -u root -p -e "FLUSH PRIVILEGES;"',
    ]);

    $process = Ssh::create('root', '139.59.128.156')->execute([
        'mkdir -p /var/www/' . $domainName . '/public_html',
        'touch /etc/nginx/sites-available/' . $domainName . '.conf',
        'echo "server {
            listen 80;
            listen [::]:80;
            server_name ' . $domainName . ' www.' . $domainName . ';
       
            root /var/www/' . $domainName . '/public_html;
       
            index index.html index.htm;
       
            location / {
           proxy_pass http://localhost:3333;
           proxy_http_version 1.1;
           proxy_set_header Connection ”upgrade”;
           proxy_set_header Host $host;
           proxy_set_header Upgrade $http_upgrade;
           proxy_set_header X-Real-IP $remote_addr;
           proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
           }
       }" >> /etc/nginx/sites-available/' . $domainName . '.conf',
       'cd /var/www/' . $domainName . '/public_html && git clone https://github.com/' . $gitRepo . ' ' . $domainName . '',
       'cd ' . $domainName . ' && npm install && npm i --save @adonisjs/ace && node ace build && touch .env',
       'echo "PORT=3333
       HOST=127.0.0.1
       NODE_ENV=production
       APP_KEY=RZL2B9WjpfleHfRawMSHEXW5ZRcDi46O
       DB_CONNECTION=mysql
       DB_HOST=localhost
       DB_NAME=' . $databaseName . '
       DB_USER=root
       DB_PASSWORD=" >> /var/www/' . $domainName . '/public_html/' . $domainName . '/.env',
       'node ace build && node ace migration:run --force && pm2 start build/server.js',
    ]);

    dd($process->isSuccessful());
    // dd($process->getOutput());
});
