<?php

namespace App\Services;

enum PinResult
{
    case Correct;
    case Wrong;
    case Expired;
    case TooManyAttempts;
    case NoPin;
}
