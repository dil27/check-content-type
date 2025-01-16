<?php

namespace Maxximum\CheckContentType;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;

class CheckContentTypeServiceProvider extends ServiceProvider
{
    public function boot(Filesystem $filesystem)
    {
        $this->publishMiddleware($filesystem);
        $this->registerMiddlewareInKernel();
    }

    public function register()
    {
        //
    }

    protected function publishMiddleware(Filesystem $filesystem)
    {
        $source = __DIR__ . '/Middleware/CheckContentType.php';
        $destination = app_path('Http/Middleware/CheckContentType.php');

        // Salin file middleware jika belum ada
        if (!$filesystem->exists($destination)) {
            $filesystem->copy($source, $destination);
        }
    }

    protected function registerMiddlewareInKernel()
    {
        $kernelPath = app_path('Http/Kernel.php');
        $middlewareClass = "\\App\\Http\\Middleware\\CheckContentType::class";

        if (!file_exists($kernelPath)) {
            return; // Jika Kernel.php tidak ditemukan
        }

        $this->addMiddlewareToGroup($kernelPath, 'api', $middlewareClass);
    }

    protected function addMiddlewareToGroup($kernelPath, $group, $middleware)
    {
        $kernelContent = file_get_contents($kernelPath);
        $pattern = "/'{$group}'\s*=>\s*\[(.*?)\]/s";

        // Cek apakah middleware sudah ada
        if (preg_match($pattern, $kernelContent, $matches)) {
            $existingMiddleware = $matches[1];
            if (!str_contains($existingMiddleware, $middleware)) {
                // Tambahkan middleware ke grup
                $newMiddleware = $existingMiddleware . "\n            " . $middleware . ',';
                $updatedKernel = preg_replace($pattern, "'{$group}' => [{$newMiddleware}]", $kernelContent);

                // Tulis ulang Kernel.php
                file_put_contents($kernelPath, $updatedKernel);
            }
        }
    }
}
