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
    $process = Ssh::create('root', '139.59.128.156')->execute([
    //     'mysql -u root -p -e "create database db4;"',
    //     'mysql -u root -p -e "GRANT ALL ON db4.* TO "chris"@"localhost"";',
    //     'mysql -u root -p -e "FLUSH PRIVILEGES;"',
        'mkdir -p /var/www/domain-two.com/public_html',
    //     'touch /etc/nginx/sites-available/domain-two.com.conf',
    //     'echo "server {
    //         listen 80;
    //         listen [::]:80;
    //         server_name domain-two.com www.domain-two.com;
       
    //         root /var/www/domain-two.com/public_html;
       
    //         index index.html index.htm;
       
    //         location / {
    //        proxy_pass http://localhost:3333;
    //        proxy_http_version 1.1;
    //        proxy_set_header Connection ”upgrade”;
    //        proxy_set_header Host $host;
    //        proxy_set_header Upgrade $http_upgrade;
    //        proxy_set_header X-Real-IP $remote_addr;
    //        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    //        }
    //    }" >> /etc/nginx/sites-available/domain-two.com.conf',
    //    'cd /var/www/domain-two.com/public_html && git clone https://github.com/chrishcode/adoniscasts-flare',
    //    'cd adoniscasts-flare && npm install && node ace build',
    ]);;

    dd($process->isSuccessful());
    dd($process->getOutput());
});
