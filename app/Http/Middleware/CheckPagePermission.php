<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Page;

class CheckPagePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        
        // Lấy page từ route parameter
        $page = $request->route('page');
        
        if (!$page instanceof Page) {
            // Nếu page là ID hoặc identifier, load model
            $page = Page::where('id', $page)
                ->orWhere('username', $page)
                ->firstOrFail();
        }
        
        // Kiểm tra quyền
        if (!$page->hasPermission($user->id, $permission)) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }
        
        return $next($request);
    }
}
