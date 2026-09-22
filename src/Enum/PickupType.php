<?php

namespace App\Enum;

enum PickupType: string 
{
    case SingleCoil = 'single-coil';
    case Humbucker = 'humbucker';
    case P90 = 'p90';
}