<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogService
{
    public function record(?User $actor, string $action, object|string $entity, array $metadata = []): void
    {
        AuditLog::create([
            'actor_id' => $actor?->id,
            'action' => $action,
            'entity_type' => is_object($entity) ? class_basename($entity) : $entity,
            'entity_id' => is_object($entity) && method_exists($entity, 'getKey') ? (string) $entity->getKey() : null,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
