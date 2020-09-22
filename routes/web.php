<?php

use Illuminate\Support\Facades\Route;
use Spatie\Ssh\Ssh;
use Illuminate\Http\Request;

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
    $databaseName = $request->db;
    $domainName = $request->domain;
    $gitRepo = $request->git;

    $process = Ssh::create('root', '139.59.128.156')->execute([
        'mysql -u root -p -e "create database ' . $databaseName . ';"',
        'mysql -u root -p -e "GRANT ALL ON ' . $databaseName . '.* TO "root"@"localhost"";',
        'mysql -u root -p -e "ALTER USER "root"@"localhost" IDENTIFIED WITH mysql_native_password BY """;',
        'mysql -u root -p -e "FLUSH PRIVILEGES;"',
    ]);

    $port = shell_exec('CHECK="do while"
    while [[ ! -z $CHECK ]]; do
        PORT=$(( ( RANDOM % 9999 )  + 1025 ))
        CHECK=$(sudo netstat -ap | grep $PORT)
    done

    echo $PORT');

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
                proxy_pass http://localhost:' . $port . ';
                proxy_http_version 1.1;
                proxy_set_header Connection ”upgrade”;
            }
       }" >> /etc/nginx/sites-available/' . $domainName . '.conf',
       'service nginx restart',
       'cd /var/www/' . $domainName . '/public_html && git clone https://github.com/' . $gitRepo . ' ' . $domainName . '',
       'cd ' . $domainName . ' && npm install && npm i --save @adonisjs/ace && node ace build && touch .env',
       'echo "PORT=' . $port . '
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
        
    $url = 'https://api.digitalocean.com/v2/domains';
    $data = array('name' => $domainName, 'ip_address' => '139.59.128.156');
    $options = array(
            'http' => array(
            'header'  => ["Content-type: application/json", "Authorization: Bearer 04a556e6667576f19c6c290cae7f8c2887c2c2981506a0c5fb6b51abbe11d168"],
            'method'  => 'POST',
            'content' => json_encode($data),
        )
    );

    $context  = stream_context_create($options);
    $result = file_get_contents( $url, false, $context );
    $response = json_decode( $result );

    return 'Your site was created!';
    // dd($process->isSuccessful());
    // dd($process->getOutput());
});
