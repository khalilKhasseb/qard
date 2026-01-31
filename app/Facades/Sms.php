<?php

namespace App\Facades;

use App\Contracts\Sms\SmsProvider;
use App\Contracts\Sms\SmsResult;
use App\Services\Sms\SmsManager;
use Illuminate\Support\Facades\Facade;

/**
 * SMS Facade
 *
 * @method static SmsResult send(string $to, string $message)
 * @method static SmsProvider provider(?string $name = null)
 * @method static string getDefaultProvider()
 * @method static SmsManager setDefaultProvider(string $name)
 * @method static SmsManager extend(string $name, string $class)
 * @method static array getAvailableProviders()
 * @method static bool hasProvider(string $name)
 *
 * @see \App\Services\Sms\SmsManager
 */
class Sms extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SmsManager::class;
    }
}
