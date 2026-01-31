<?php

namespace App\Services\Sms;

use App\Contracts\Sms\SmsProvider;
use App\Contracts\Sms\SmsResult;
use App\Services\Sms\Providers\LogSmsProvider;
use App\Services\Sms\Providers\TwilioSmsProvider;
use App\Services\Sms\Providers\VonageSmsProvider;
use InvalidArgumentException;

/**
 * SMS Manager - Manages SMS providers and handles sending.
 *
 * This class acts as a factory and manager for SMS providers.
 * Add new providers by extending the $providers array and creating the provider class.
 */
class SmsManager
{
    /**
     * Available provider mappings.
     *
     * Add new providers here: 'name' => ProviderClass::class
     *
     * @var array<string, class-string<SmsProvider>>
     */
    protected array $providers = [
        'log' => LogSmsProvider::class,
        'twilio' => TwilioSmsProvider::class,
        'vonage' => VonageSmsProvider::class,
    ];

    /**
     * Resolved provider instances.
     *
     * @var array<string, SmsProvider>
     */
    protected array $resolved = [];

    /**
     * The default provider name.
     */
    protected string $defaultProvider;

    public function __construct()
    {
        $this->defaultProvider = config('sms.default', 'log');
    }

    /**
     * Get a provider instance by name.
     */
    public function provider(?string $name = null): SmsProvider
    {
        $name = $name ?? $this->defaultProvider;

        if (! isset($this->resolved[$name])) {
            $this->resolved[$name] = $this->resolve($name);
        }

        return $this->resolved[$name];
    }

    /**
     * Resolve a provider instance.
     */
    protected function resolve(string $name): SmsProvider
    {
        if (! isset($this->providers[$name])) {
            throw new InvalidArgumentException("SMS provider [{$name}] is not supported.");
        }

        $class = $this->providers[$name];

        return app($class);
    }

    /**
     * Send an SMS using the default provider.
     */
    public function send(string $to, string $message): SmsResult
    {
        return $this->provider()->send($to, $message);
    }

    /**
     * Get the default provider name.
     */
    public function getDefaultProvider(): string
    {
        return $this->defaultProvider;
    }

    /**
     * Set the default provider.
     */
    public function setDefaultProvider(string $name): self
    {
        if (! isset($this->providers[$name])) {
            throw new InvalidArgumentException("SMS provider [{$name}] is not supported.");
        }

        $this->defaultProvider = $name;

        return $this;
    }

    /**
     * Register a custom provider.
     *
     * @param  class-string<SmsProvider>  $class
     */
    public function extend(string $name, string $class): self
    {
        $this->providers[$name] = $class;

        return $this;
    }

    /**
     * Get all available provider names.
     *
     * @return array<string>
     */
    public function getAvailableProviders(): array
    {
        return array_keys($this->providers);
    }

    /**
     * Check if a provider is available.
     */
    public function hasProvider(string $name): bool
    {
        return isset($this->providers[$name]);
    }
}
