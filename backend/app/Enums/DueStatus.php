<?php

namespace App\Enums;

enum DueStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
}
