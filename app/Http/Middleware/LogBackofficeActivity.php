<?php

namespace App\Http\Middleware;

use App\Support\AuditLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogBackofficeActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && ! $request->isMethod('GET')) {
            AuditLogger::activity('backoffice.request', $this->description($request), [
                'status' => $response->getStatusCode(),
                'route_parameters' => $request->route()?->parameters() ?: [],
            ]);
        }

        return $response;
    }

    private function description(Request $request): string
    {
        $name = $request->route()?->getName();

        return $name
            ? 'Performed action on ' . str($name)->replace('.', ' ')->headline()
            : 'Performed backoffice request';
    }
}
