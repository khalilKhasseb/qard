<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Language;

class HealthController extends Controller
{
    public function check()
    {
        $health = [
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'checks' => []
        ];

        try {
            // Database check
            DB::connection()->getPdo();
            $health['checks']['database'] = 'ok';
        } catch (\Exception $e) {
            $health['checks']['database'] = 'error: ' . $e->getMessage();
            $health['status'] = 'error';
        }

        try {
            // Cache check
            $cacheKey = 'health_check_' . time();
            Cache::put($cacheKey, 'test', 60);
            Cache::get($cacheKey);
            Cache::forget($cacheKey);
            $health['checks']['cache'] = 'ok';
        } catch (\Exception $e) {
            $health['checks']['cache'] = 'error: ' . $e->getMessage();
            $health['status'] = 'error';
        }

        try {
            // Basic model check
            Language::count();
            $health['checks']['models'] = 'ok';
        } catch (\Exception $e) {
            $health['checks']['models'] = 'error: ' . $e->getMessage();
            $health['status'] = 'error';
        }

        // Storage check
        try {
            $storageWritable = is_writable(storage_path());
            $health['checks']['storage'] = $storageWritable ? 'ok' : 'error: not writable';
            if (!$storageWritable) {
                $health['status'] = 'error';
            }
        } catch (\Exception $e) {
            $health['checks']['storage'] = 'error: ' . $e->getMessage();
            $health['status'] = 'error';
        }

        $statusCode = $health['status'] === 'ok' ? 200 : 503;

        return response()->json($health, $statusCode);
    }

    public function version()
    {
        return response()->json([
            'app_name' => config('app.name'),
            'app_version' => config('app.version', '1.0.0'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => config('app.env'),
        ]);
    }
}