<?php


namespace App\Http\Middleware;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogRequest
{
    private static $elapsedTime;

    private $exceptMethods = ['OPTIONS'];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, \Closure $next, $guard = null)
    {
        if (in_array($request->getMethod(), $this->exceptMethods)) {
            return $next($request);
        }

        $requestInfo = $this->getRequestInfo($request);
        if (config('app.debug')) {
            $requestInfo['requestParams'] = $this->getRequestParams($request);
        }
        Log::info('request', $requestInfo);

        $startTime = microtime(true);

        $response = $next($request);

        self::$elapsedTime = microtime(true) - $startTime;

        return $response;
    }

    public function terminate($request, $response)
    {
        if (!in_array($request->getMethod(), $this->exceptMethods)) {
            Log::info('response', array_merge($this->getRequestInfo($request), [
                'http_status' => $response->status(),
                'elapsed_time' => self::$elapsedTime,
            ]));
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    private function getRequestInfo($request)
    {
        $info = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'ip' => implode(',', $request->ips()),
            'ua' => $request->header('User-Agent', ''),
        ];

        $user = Auth::user();
        if ($user) {
            $info['userId'] = $user->id;
        }
        return $info;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    private function getRequestParams($request)
    {
        if ($request->method() == 'POST') {
            return $request->json()->all();
        } else {
            return $request->input();
        }
    }
}
