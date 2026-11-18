<?php

namespace App\Enums;

enum CommentStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case HIDDEN = 'HIDDEN';
}
