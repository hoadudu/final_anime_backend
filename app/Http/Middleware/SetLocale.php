<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra query parameter 'lang'
        if ($request->has('lang') && in_array($request->query('lang'), ['vi', 'en'])) {  // Thêm các ngôn ngữ hỗ trợ vào mảng
            $locale = $request->query('lang');
            Session::put('locale', $locale);  // Lưu vào session để giữ ngôn ngữ cho các request sau
        }

        // Set locale: ưu tiên session > mặc định từ config
        $locale = Session::get('locale', config('app.locale'));
        App::setLocale($locale);

        return $next($request);
    }
}