<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateImageUpload
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra nếu có upload files
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            
            foreach ($images as $image) {
                // Kiểm tra mime type
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($image->getMimeType(), $allowedMimes)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Định dạng file không được hỗ trợ. Chỉ chấp nhận JPEG, PNG, GIF, WebP.'
                    ], 422);
                }
                
                // Kiểm tra kích thước file (10MB max)
                if ($image->getSize() > 10 * 1024 * 1024) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kích thước file quá lớn. Tối đa 10MB mỗi file.'
                    ], 422);
                }
                
                // Kiểm tra extension
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $extension = strtolower($image->getClientOriginalExtension());
                if (!in_array($extension, $allowedExtensions)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Phần mở rộng file không được hỗ trợ.'
                    ], 422);
                }
                
                // Kiểm tra file có phải là ảnh thực sự không
                $imageInfo = getimagesize($image->getPathname());
                if ($imageInfo === false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'File không phải là ảnh hợp lệ.'
                    ], 422);
                }
                
                // Kiểm tra kích thước ảnh (tránh ảnh quá lớn)
                $maxWidth = 5000;
                $maxHeight = 5000;
                if ($imageInfo[0] > $maxWidth || $imageInfo[1] > $maxHeight) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kích thước ảnh quá lớn. Tối đa 5000x5000 pixels.'
                    ], 422);
                }
            }
        }
        
        return $next($request);
    }
}
