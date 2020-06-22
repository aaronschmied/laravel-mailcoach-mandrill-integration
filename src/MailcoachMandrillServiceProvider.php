<?php

namespace SchmiedDev\MailcoachMandrillIntegration;

use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use SchmiedDev\MailcoachMandrillIntegration\Drivers\MandrillTransportDriver;
use SchmiedDev\MailcoachMandrillIntegration\Feedback\MandrillWebhookController;
use SchmiedDev\MailcoachMandrillIntegration\Jobs\StoreTransportMessageId;

class MailcoachMandrillServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this
            ->registerTransportDriver()
            ->registerPublishedConfigurationDriver();
    }

    /**
     * Register the route macro and the events.
     */
    public function register()
    {
        Route::macro('mandrillFeedback', function (string $url) {
            Route::any($url, '\\' . MandrillWebhookController::class)
                ->name('mandrillFeedback');
        });

        Event::listen(MessageSent::class, StoreTransportMessageId::class);
    }

    /**
     * Register the mandrill transport driver.
     *
     * @return $this
     */
    protected function registerTransportDriver()
    {
        $this->app['mail.manager']->extend('mandrill', function ($config) {

            return new MandrillTransportDriver(
                new Client([
                               'base_uri' => 'https://mandrillapp.com/api/1.0/',
                           ]),
                new Repository($config)
            );
        });

        return $this;
    }

    /**
     * Publish the configuration driver stubs.
     * @return $this
     */
    protected function registerPublishedConfigurationDriver()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../stubs/MailConfigurationDriver.php.stub'
                    => app_path('Support/MailConfiguration/Drivers/MandrillConfigurationDriver.php'),
                    __DIR__ . '/../stubs/TransactionalMailConfigurationDriver.php.stub'
                    => app_path('Support/TransactionalMailConfiguration/Drivers/MandrillConfigurationDriver.php'),
                ],
                'mailcoach-mandrill-mail-configuration-drivers'
            );

            $this->publishes(
                [
                    __DIR__ . '/../stubs/mail-configuration_mandrill.blade.php.stub'
                    => resource_path('views/app/settings/mailConfiguration/partials/mandrill.blade.php'),
                    __DIR__ . '/../stubs/transactional-mail-configuration_mandrill.blade.php.stub'
                    => app_path('views/app/settings/transactionalMailConfiguration/partials/mandrill.blade.php'),
                ],
                'mailcoach-mandrill-mail-configuration-driver-views'
            );
        }
        return $this;
    }
}
