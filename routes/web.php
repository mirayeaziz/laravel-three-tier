<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tiers', function () {
    $app = gethostname();
    $web = request()->server('HTTP_X_WEB_POD', 'unknown');
    \Illuminate\Support\Facades\DB::table('visits')->insert([
        'app_pod' => $app, 'web_pod' => $web,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $db = \Illuminate\Support\Facades\DB::selectOne('select version() as v, database() as d');
    $count = \Illuminate\Support\Facades\DB::table('visits')->count();
    $recent = \Illuminate\Support\Facades\DB::table('visits')->orderByDesc('id')->limit(5)->get();
    $rows = '';
    foreach ($recent as $r) {
        $rows .= "<tr><td>{$r->id}</td><td>{$r->web_pod}</td><td>{$r->app_pod}</td><td>{$r->created_at}</td></tr>";
    }
    return response("<html><body style='font-family:sans-serif;max-width:800px;margin:40px auto'>
    <h1>Laravel Three-Tier on Kubernetes</h1>
    <p><b>1. Presentation tier (Nginx pod):</b> {$web}</p>
    <p><b>2. Application tier (Laravel pod):</b> {$app}</p>
    <p><b>3. Database tier (MySQL):</b> version {$db->v}, database {$db->d}</p>
    <p><b>Total visits stored in MySQL:</b> {$count}</p>
    <table border='1' cellpadding='6'><tr><th>ID</th><th>Nginx pod</th><th>Laravel pod</th><th>Time</th></tr>{$rows}</table>
    <p>Refresh the page: the Laravel pod name changes as Kubernetes load-balances.</p>
    </body></html>");
});
