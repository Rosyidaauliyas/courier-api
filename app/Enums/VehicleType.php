<?php

declare(strict_types=1);

namespace App\Enums;

enum VehicleType: string
{
    case Motorcycle = 'motorcycle';
    case Car = 'car';
    case Bicycle = 'bicycle';
    case Van = 'van';
}
