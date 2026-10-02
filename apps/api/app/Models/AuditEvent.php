<?php

namespace App\Models;

class AuditEvent extends DomainModel
{
    // Audit events are immutable; the table has only created_at.
    public const UPDATED_AT = null;
}
