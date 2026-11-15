<?php

if (!function_exists('activity_log')) {
    function activity_log(string $module, int $subjectId, string $action, string $description = ''): void
    {
        try {
            \App\Models\ActivityLog::create([
                'user_id'     => auth()->id(),
                'module'      => $module,
                'subject_id'  => $subjectId,
                'action'      => $action,
                'description' => $description,
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Jangan sampai activity log gagal dan merusak transaksi utama
            \Illuminate\Support\Facades\Log::warning('activity_log failed: '.$e->getMessage());
        }
    }
}