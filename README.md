# laravel-mailcoach-mandrill-integration

## Installation

Install the package using composer:

````shell script
composer require "aaronschmied/laravel-mailcoach-mandrill-integration"
````

Publish the configuration drivers:

````shell script
php artisan vendor:publish --tag=mailcoach-mandrill-mail-configuration-drivers
````

And the configuration views:
````shell script
php artisan vendor:publish --tag=mailcoach-mandrill-mail-configuration-driver-views
````

### Required manual modifications:

Since I don't want to modify your files, you have to do it manually:

- Update your route service provider

    Update your `App\Providers\RouteServiceProvider`
    ```php
    class RouteServiceProvider extends ServiceProvider
    {
        public function map(Router $router)
        {
            // Add the following line:
            Route::mandrillFeedback('mandrill-feedback');
    ```

- As well as the MailConfigurationDriverRepositories:
    
    In the file `App\Support\MailConfiguration\MailConfigurationDriverRepository`, add the new driver:
        
    ```php
    use App\Support\MailConfiguration\Drivers\MandrillConfigurationDriver;
    
    class MailConfigurationDriverRepository
    {
        protected array $drivers = [
            // ...
            'mandrill' => MandrillConfigurationDriver::class,
            // ...
        ];
    ```
    
    The same has to be added to the transactional mail driver repository `App\Support\TransactionalMailConfiguration\TransactionalMailConfigurationDriverRepository`:
        
    ```php
    use App\Support\TransactionalMailConfiguration\Drivers\MandrillConfigurationDriver;
    
    class TransactionalMailConfigurationDriverRepository
    {
        protected array $drivers = [
            // ...
            'mandrill' => MandrillConfigurationDriver::class,
            // ...
        ];
    ```
- To be able to modify the new settings in the UI, you have to update BOTH the views `resources/views/app/settings/mailConfiguration/edit.blade.php` AND `resources/views/app/settings/transactionalMailConfiguration/edit.blade.php`:

    ```blade
    ....
          'sendgrid' => 'SendGrid',
          'mailgun' => 'Mailgun',
          'postmark' => 'Postmark',
          'mandrill' => 'Mandrill', // <- Add this line to the driver array
          'smtp' => 'SMTP',
      ]"
      data-conditional="driver"
  ...

  ...
    </div>
  
    <!-- Add this block to the other drivers -->
    <div class="form-grid" data-conditional-driver="mandrill">
        @include('app.settings.transactionalMailConfiguration.partials.mandrill')
    </div>

    <div class="form-grid" data-conditional-driver="smtp">
  ...

  ```

