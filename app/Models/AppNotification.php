<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AppNotification extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    // Tipe notifikasi
    const TYPE_SURAT_MASUK = 'surat_masuk';           // Surat jalan dikirim ke gudang
    const TYPE_SURAT_SIAP_TERIMA = 'surat_siap_terima'; // Surat sudah diperiksa security
    const TYPE_SURAT_DITERIMA = 'surat_diterima';     // Surat sudah diterima
    const TYPE_SURAT_DITOLAK = 'surat_ditolak';       // Surat ditolak
    const TYPE_SURAT_DIAJUKAN = 'surat_diajukan';     // Surat jalan menunggu persetujuan manager

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function suratJalan()
    {
        return $this->belongsTo(SuratJalan::class);
    }

    // Scope untuk notifikasi yang belum dibaca
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    // Scope untuk notifikasi user tertentu
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Mark as read
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    // Check if read
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    // Get icon based on type
    public function getIconAttribute($value): string
    {
        if ($value) return $value;

        return match($this->type) {
            self::TYPE_SURAT_MASUK => 'truck',
            self::TYPE_SURAT_SIAP_TERIMA => 'check-circle',
            self::TYPE_SURAT_DITERIMA => 'inbox',
            self::TYPE_SURAT_DITOLAK => 'x-circle',
            self::TYPE_SURAT_DIAJUKAN => 'clipboard-check',
            default => 'bell',
        };
    }

    // Get color based on type
    public function getColorAttribute(): string
    {
        return match($this->type) {
            self::TYPE_SURAT_MASUK => 'blue',
            self::TYPE_SURAT_SIAP_TERIMA => 'green',
            self::TYPE_SURAT_DITERIMA => 'teal',
            self::TYPE_SURAT_DITOLAK => 'red',
            self::TYPE_SURAT_DIAJUKAN => 'amber',
            default => 'gray',
        };
    }

    // Accessor to support both 'url' and 'link' columns
    public function getUrlAttribute($value)
    {
        return $value ?? $this->attributes['link'] ?? null;
    }

    // Create notification for operators of a gudang
    public static function notifyGudangOperators(
        int $gudangId,
        string $type,
        string $title,
        string $message,
        ?int $suratJalanId = null,
        ?string $url = null,
        ?array $roles = null,
        ?string $penerimaJabatan = null
    ): void
    {
        $roles = $roles ?: ['operator_gudang', 'penerima'];
        if (empty($roles)) {
            return;
        }

        $operators = User::where('gudang_id', $gudangId)
            ->whereIn('role', $roles)
            ->when($penerimaJabatan !== null && in_array('penerima', $roles, true), function ($query) use ($penerimaJabatan) {
                $jabatan = strtolower(trim($penerimaJabatan));
                if ($jabatan === '') {
                    $query->where('role', '!=', 'penerima');
                    return;
                }
                $query->where(function ($subQuery) use ($jabatan) {
                    $subQuery->where('role', '!=', 'penerima')
                        ->orWhere(function ($penerimaQuery) use ($jabatan) {
                            $penerimaQuery->where('role', 'penerima')
                                ->whereRaw('LOWER(jabatan) = ?', [$jabatan]);
                        });
                });
            })
            ->get();

        foreach ($operators as $operator) {
            self::create([
                'user_id' => $operator->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'surat_jalan_id' => $suratJalanId,
                'url' => $url,
                'link' => $url, // Support both columns
            ]);
            self::pushToUser($operator->id, $title, $message, $url, $suratJalanId);
        }
    }

    public static function notifyUser(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?int $suratJalanId = null,
        ?string $url = null
    ): void {
        $exists = self::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('surat_jalan_id', $suratJalanId)
            ->exists();
        if ($exists) {
            return;
        }

        self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'surat_jalan_id' => $suratJalanId,
            'url' => $url,
            'link' => $url, // Support both columns
        ]);
        self::pushToUser($userId, $title, $message, $url, $suratJalanId);
    }

    public static function notifyGudangManagers(
        int $gudangId,
        string $type,
        string $title,
        string $message,
        ?int $suratJalanId = null,
        ?string $url = null
    ): void {
        $managers = User::where('role', 'manager')
            ->whereHas('managedGudangs', function ($query) use ($gudangId) {
                $query->where('gudangs.id', $gudangId);
            })
            ->get();

        foreach ($managers as $manager) {
            self::create([
                'user_id' => $manager->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'surat_jalan_id' => $suratJalanId,
                'url' => $url,
                'link' => $url, // Support both columns
            ]);
            self::pushToUser($manager->id, $title, $message, $url, $suratJalanId);
        }
    }

    private static function pushToUser(
        int $userId,
        string $title,
        string $message,
        ?string $url = null,
        ?int $suratJalanId = null
    ): void {
        if (!config('services.onesignal.enabled')) {
            return;
        }

        $appId = config('services.onesignal.app_id');
        $apiKey = config('services.onesignal.rest_api_key');
        if (!$appId || !$apiKey) {
            return;
        }

        $payload = [
            'app_id' => $appId,
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
        ];

        if (str_starts_with($apiKey, 'os_v2_')) {
            $payload['include_aliases'] = [
                'external_id' => [(string) $userId],
            ];
            $payload['target_channel'] = 'push';
        } else {
            $payload['include_external_user_ids'] = [(string) $userId];
            $payload['channel_for_external_user_ids'] = 'push';
        }

        if ($url) {
            $payload['url'] = $url;
        }

        if ($suratJalanId) {
            $payload['data'] = ['surat_jalan_id' => $suratJalanId];
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => 'Basic ' . $apiKey,
                    'Accept' => 'application/json',
                ])
                ->post('https://onesignal.com/api/v1/notifications', $payload);

            if (!$response->successful()) {
                Log::warning('OneSignal push failed', [
                    'user_id' => $userId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('OneSignal push error', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
