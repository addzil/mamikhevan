<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ActivityLogger
{
    /** @param array<string, mixed> $properties */
    public function record(
        string $action,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?User $actor = null,
        ?string $subjectLabel = null,
    ): void {
        $request = app()->bound('request') ? request() : null;
        $actor ??= Auth::user() ?? $request?->user();
        $sessionHash = $request?->hasSession()
            ? hash_hmac('sha256', $request->session()->getId(), (string) config('app.key'))
            : null;

        ActivityLog::query()->create([
            'actor_user_id' => $actor?->getKey(),
            'actor_name' => $actor?->name,
            'actor_email' => $actor?->email,
            'actor_role' => $actor?->role,
            'session_hash' => $sessionHash,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'subject_label' => $subjectLabel ?? ($subject === null ? null : $this->subjectLabel($subject)),
            'description' => $description,
            'properties' => $this->sanitize($properties),
            'ip_address' => $request?->ip(),
            'user_agent' => Str::limit((string) $request?->userAgent(), 1000, ''),
        ]);
    }

    private function subjectLabel(Model $subject): ?string
    {
        foreach (['code', 'name', 'product_name', 'subject', 'email'] as $attribute) {
            $value = $subject->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return Str::limit($value, 255);
            }
        }

        return class_basename($subject).' #'.$subject->getKey();
    }

    /** @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    private function sanitize(array $values): array
    {
        $sanitized = [];

        foreach ($values as $key => $value) {
            if (preg_match('/password|token|secret|remember|proof_path|payment_destination|account_number|qris_image|(^|_)image($|_)|customer_(email|phone|address)|(^|_)address($|_)|(^|_)phone($|_)|^note$|message|comment|admin_reply|reply_body/i', (string) $key) === 1) {
                $sanitized[$key] = '[redacted]';

                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize($value);
            } elseif ($value instanceof Model) {
                $sanitized[$key] = class_basename($value).' #'.$value->getKey();
            } elseif (is_object($value) && method_exists($value, 'toIso8601String')) {
                $sanitized[$key] = $value->toIso8601String();
            } elseif (is_scalar($value) || $value === null) {
                $sanitized[$key] = $value;
            } else {
                $sanitized[$key] = Arr::wrap($value);
            }
        }

        return $sanitized;
    }
}
