<?php

namespace App\Jobs\Middleware;

use Illuminate\Support\Facades\Config;

class SetEnvironment
{

    public function __construct(private string $env)
    {
    }

    public function handle($job, $next)
    {
        Config::set('app.env', $this->env);
        $next($job);
    }
}