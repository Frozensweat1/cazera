<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuditLogger
{
    public static function activity(string $event, ?string $description = null, array $properties = []): void
    {
        if (! Schema::hasTable('activity_logs')) {
            return;
        }

        $request = request();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'branch_id' => session('branch_id'),
            'module_id' => $properties['module_id'] ?? null,
            'event' => $event,
            'description' => $description,
            'method' => $request?->method(),
            'route_name' => $request?->route()?->getName(),
            'url' => $request?->fullUrl(),
            'ip_address' => $request?->ip(),
            'user_agent' => Str::limit((string) $request?->userAgent(), 1000, ''),
            'properties' => $properties ?: null,
            'logged_at' => now(),
        ]);
    }

    public static function audit(Model $model, string $event, array $oldValues = [], array $newValues = []): void
    {
        if (! Schema::hasTable('audit_logs') || $model instanceof AuditLog || $model instanceof ActivityLog) {
            return;
        }

        $request = request();
        $oldValues = self::cleanValues($model, $oldValues);
        $newValues = self::cleanValues($model, $newValues);
        $changedFields = array_values(array_unique(array_merge(array_keys($oldValues), array_keys($newValues))));

        AuditLog::create([
            'user_id' => auth()->id(),
            'branch_id' => self::modelValue($model, 'branch_id') ?: session('branch_id'),
            'module_id' => self::modelValue($model, 'module_id'),
            'event' => $event,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'auditable_label' => self::label($model),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'changed_fields' => $changedFields ?: null,
            'url' => $request?->fullUrl(),
            'ip_address' => $request?->ip(),
            'user_agent' => Str::limit((string) $request?->userAgent(), 1000, ''),
            'logged_at' => now(),
        ]);
    }

    private static function cleanValues(Model $model, array $values): array
    {
        $hidden = array_flip(array_merge($model->getHidden(), [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'updated_at',
            'created_at',
        ]));

        return collect($values)
            ->reject(fn ($value, $key) => isset($hidden[$key]))
            ->map(fn ($value) => is_scalar($value) || is_null($value) ? $value : json_decode(json_encode($value), true))
            ->all();
    }

    private static function modelValue(Model $model, string $key): mixed
    {
        return in_array($key, $model->getFillable(), true) || array_key_exists($key, $model->getAttributes())
            ? $model->getAttribute($key)
            : null;
    }

    private static function label(Model $model): string
    {
        foreach (['name', 'title', 'role', 'sale_number', 'reference_no', 'email', 'item_name'] as $field) {
            if ($model->getAttribute($field)) {
                return (string) $model->getAttribute($field);
            }
        }

        return class_basename($model) . ' #' . ($model->getKey() ?: 'new');
    }
}
