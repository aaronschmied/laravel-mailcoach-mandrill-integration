<?php

namespace SchmiedDev\MailcoachMandrillIntegration;

use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider;
use SchmiedDev\MailcoachMandrillIntegration\Drivers\MandrillTransportDriver;

class MailcoachMandrillServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this
            ->registerTransportDriver();
    }

    /**
     * Register the mandrill transport driver.
     *
     * @return $this
     */
    protected function registerTransportDriver()
    {
        $this->app['mail.manager']->extend('mandrill', function () {
            $config = $this->app['config']->get('services.mandrill', []);

            return new MandrillTransportDriver(
                new \GuzzleHttp\Client($config),
                new Repository($config)
            );
        });

        return $this;
    }
}
