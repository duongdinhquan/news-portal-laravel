<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case DRAFT = 'DRAFT';
    case PENDING_REVIEW = 'PENDING_REVIEW';
    case REJECTED = 'REJECTED';
    case PUBLISHED = 'PUBLISHED';
    case SUPERSEDED = 'SUPERSEDED';
}
