<?php

namespace App\Support\TransactionalMailConfiguration\Drivers;

use App\Support\TransactionalMailConfiguration\Drivers\TransactionalMailConfigurationDriver;
use Illuminate\Contracts\Config\Repository;

class MandrillConfigurationDriver extends TransactionalMailConfigurationDriver
{

    public function name(): string
    {
        return 'mandrill';
    }

    public function validationRules(): array
    {
        return [
            'mandrill_username' => ['required', 'string'],
            'mandrill_key' => ['required', 'string'],
            'track_opens' => ['boolean'],
            'track_clicks' => ['boolean'],
            'tracking_domain' => ['sometimes', 'required']
            'return_path_domain' => ['sometimes', 'required']

        ];
    }

    public function registerConfigValues(Repository $config, array $values)
    {
        $config->set('mail.mailers.mailcoach-transactional.transport', $this->name());
        $config->set('mail.mailers.mailcoach-transactional.host', $values['smtp_host']);
        $config->set('mail.mailers.mailcoach-transactional.port', $values['smtp_port']);
        $config->set('mail.mailers.mailcoach-transactional.username', $values['smtp_username']);
        $config->set('mail.mailers.mailcoach-transactional.password', $values['smtp_password']);
    }
}