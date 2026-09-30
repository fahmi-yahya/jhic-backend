<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

/**
 * Helper terpusat untuk mencatat aktivitas (create/update/delete) yang
 * dilakukan user — dipakai di controller manapun yang datanya perlu diaudit
 * (UserController, LingkunganController, dst).
 */
class ActivityLogger
{
    public static function log(
        Request $request,
        string $action, // 'created' | 'updated' | 'deleted'
        string $subjectType,
        int|string|null $subjectId,
        string $description,
        array $changes = [],
    ): void {
        $actor = $request->user();

        ActivityLog::create([
            'user_id' => $actor?->id,
            'user_name' => $actor?->name,
            'role' => $actor?->role,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
            'changes' => $changes ?: null,
        ]);
    }

    /**
     * Bandingkan array data lama vs baru, kembalikan hanya field yang
     * benar-benar berubah dalam bentuk { before: {...}, after: {...} }.
     * Dipakai saat update supaya log tidak penuh field yang tidak berubah.
     */
    public static function diff(array $before, array $after): array
    {
        $changedBefore = [];
        $changedAfter = [];

        foreach ($after as $key => $value) {
            $old = $before[$key] ?? null;
            if ($old != $value) {
                $changedBefore[$key] = $old;
                $changedAfter[$key] = $value;
            }
        }

        if (empty($changedAfter)) {
            return [];
        }

        return ['before' => $changedBefore, 'after' => $changedAfter];
    }
}
