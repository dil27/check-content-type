<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use App\Models\User;

class CheckContentType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!in_array(strtoupper($request->method()), ['GET', 'POST'])) {
            return response()->json([
                'metadata' => [
                    'status' => 'error',
                    'message' => 'Method Not Allowed!',
                    'errors' => [],
                ],
                'response' => [],
            ], 405);
        }

        if ($request->method() !== 'GET') {
            $allowedContentTypes = ['application/json', 'application/x-www-form-urlencoded', 'multipart/form-data'];
            $requestContentType = $request->header('Content-Type');

            $cleanContentType = explode(';', $requestContentType)[0];

            if (!in_array($cleanContentType, $allowedContentTypes)) {
                return response()->json(['error' => 'Unsupported Content-Type'], 415);
            }

            $allFiles = $request->allFiles();
            if (!empty($allFiles)) {
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'xls', 'xlsx'];
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/ms-excel', 'application/msexcel'];
                foreach ($allFiles as $file) {
                    if (is_array($file)) {
                        foreach ($file as $key2 => $item_file) {
                            $mime = $item_file->getMimeType();
                            $extension = $item_file->getClientOriginalExtension();
                            
                            $originalExtensions = [$item_file->extension()];
                            if (in_array($item_file->extension(), ['jpg', 'jpeg'])) {
                                $originalExtensions = ['jpg', 'jpeg'];
                            } elseif (in_array($item_file->extension(), ['xls', 'xlsx'])) {
                                $originalExtensions = ['xls', 'xlsx'];
                            }
                                
                            if (!in_array($mime, $allowedMimeTypes) || !in_array(strtolower($extension), $allowedExtensions) || !in_array(strtolower($extension), $originalExtensions)) {
                                return response()->json([
                                    'metadata' => [
                                        'status' => 'error',
                                        'message' => 'Unsupported File Upload!',
                                        'errors' => [],
                                    ],
                                    'response' => [],
                                ], 422);
                            }
                        }
                    }else{
                        $mime = $file->getMimeType();
                        $extension = $file->getClientOriginalExtension();
                        
                        $originalExtensions = [$file->extension()];
                        if (in_array($file->extension(), ['jpg', 'jpeg'])) {
                            $originalExtensions = ['jpg', 'jpeg'];
                        } elseif (in_array($file->extension(), ['xls', 'xlsx'])) {
                            $originalExtensions = ['xls', 'xlsx'];
                        }
                            
                        if (!in_array($mime, $allowedMimeTypes) || !in_array(strtolower($extension), $allowedExtensions) || !in_array(strtolower($extension), $originalExtensions)) {
                            return response()->json([
                                'metadata' => [
                                    'status' => 'error',
                                    'message' => 'Unsupported File Upload!',
                                    'errors' => [],
                                ],
                                'response' => [],
                            ], 422);
                        }
                    }
                }
            }
        }

        if (auth()->check()) {
            // $user_agent = $request->header('user-agent');
            // $device_identifier = $request->header('DeviceIdentifier');

            // $user = auth()->user();

            // if ($user->is_lock_device == 1) {
            //     if ($user->last_user_agent != $user_agent || $user->device_identifier != $device_identifier) {
            //         auth()->user()->currentAccessToken()->delete(); // Revoke current access token
            //         return response()->json([
            //             'metadata' => [
            //                 'status' => 'error',
            //                 'message' => 'Anda terdeteksi login pada device lain! Atau silakan coba login ulang.',
            //                 'errors' => [],
            //             ],
            //             'response' => [
            //                 'linked_device' => User::getLastDevice($user),
            //             ],
            //         ], 400);
            //     }
            // }

            // Set Last Acitivy
            User::setLastActivity();
        }

        return $next($request);
    }
}
