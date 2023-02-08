<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    'version' => '3.4.0',

    'demolock' => '0',

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        Intervention\Image\ImageServiceProvider::class,
        DevMarketer\EasyNav\EasyNavServiceProvider::class,
        Laravel\Socialite\SocialiteServiceProvider::class,
        Cartalyst\Stripe\Laravel\StripeServiceProvider::class,
        Unicodeveloper\Paystack\PaystackServiceProvider::class,
        Orangehill\Iseed\IseedServiceProvider::class,
        Anand\LaravelPaytmWallet\PaytmWalletServiceProvider::class,
        Laravolt\Avatar\ServiceProvider::class,
        Revolution\Socialite\Amazon\AmazonServiceProvider::class,
        Anhskohbo\NoCaptcha\NoCaptchaServiceProvider::class,
        SmoDav\Mpesa\Laravel\ServiceProvider::class,
        Jorenvh\Share\Providers\ShareServiceProvider::class,
        RealRashid\SweetAlert\SweetAlertServiceProvider::class,
        KingFlamez\Rave\RaveServiceProvider::class,
        Artesaos\SEOTools\Providers\SEOToolsServiceProvider::class,
        Vimeo\Laravel\VimeoServiceProvider::class,
        Alaouy\Youtube\YoutubeServiceProvider::class,
        niklasravnsborg\LaravelPdf\PdfServiceProvider::class,
        NotificationChannels\OneSignal\OneSignalServiceProvider::class,
        Jackiedo\DotenvEditor\DotenvEditorServiceProvider::class,
        Shipu\Aamarpay\AamarpayServiceProvider::class,
        PragmaRX\Google2FALaravel\ServiceProvider::class,
        Collective\Html\HtmlServiceProvider::class,


        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,
        'Image' => Intervention\Image\Facades\Image::class,
        'Nav' => DevMarketer\EasyNav\EasyNavFacade::class,
        'Socialite' => Laravel\Socialite\Facades\Socialite::class,
        'Stripe' => Cartalyst\Stripe\Laravel\Facades\Stripe::class,
        'Paystack' => Unicodeveloper\Paystack\Facades\Paystack::class,
        'PaytmWallet' => Anand\LaravelPaytmWallet\Facades\PaytmWallet::class,
        'Avatar'    => Laravolt\Avatar\Facade::class,
        'NoCaptcha' => Anhskohbo\NoCaptcha\Facades\NoCaptcha::class,
        'STK'       => SmoDav\Mpesa\Laravel\Facades\STK::class,
        'Simulate'  => SmoDav\Mpesa\Laravel\Facades\Simulate::class,
        'Registrar' => SmoDav\Mpesa\Laravel\Facades\Registrar::class,
        'Identity'  => SmoDav\Mpesa\Laravel\Facades\Identity::class,
        'Share' => Jorenvh\Share\ShareFacade::class,
        'Alert' => RealRashid\SweetAlert\Facades\Alert::class,
        'Rave' => KingFlamez\Rave\Facades\Rave::class,
        'SEO' => Artesaos\SEOTools\Facades\SEOTools::class,
        'SeoHelper' => App\Helpers\SeoHelper::class,
        'Tracker' => App\Helpers\Tracker::class,
        'Vimeo' => Vimeo\Laravel\Facades\Vimeo::class,
        'Youtube' => Alaouy\Youtube\Facades\Youtube::class,
        'PDF' => niklasravnsborg\LaravelPdf\Facades\Pdf::class,
        'DotenvEditor' => Jackiedo\DotenvEditor\Facades\DotenvEditor::class,
        'Aamarpay'   =>  Shipu\Aamarpay\Facades\Aamarpay::class,
        'Google2FA' => PragmaRX\Google2FALaravel\Facade::class,
        'Form' => Collective\Html\FormFacade::class,
        'Html' => Collective\Html\HtmlFacade::class,
        // 'Pusher' => Pusher\Pusher::class,
    ],


    'debug_hide' => [
        '_ENV' => [
            'APP_KEY',
            'DB_USERNAME',
            'DB_DATABASE',
            'DB_PASSWORD',
            'MAIL_FROM_NAME',
            'MAIL_FROM_ADDRESS',
            'MAIL_DRIVER',
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
            'MAIL_ENCRYPTION',
            'FACEBOOK_CLIENT_ID',
            'FACEBOOK_CLIENT_SECRET',
            'FACEBOOK_CALLBACK_URL',
            'GOOGLE_CLIENT_ID',
            'GOOGLE_CLIENT_SECRET',
            'GOOGLE_CALLBACK_URL',
            'GITLAB_CLIENT_ID',
            'GITLAB_CLIENT_SECRET',
            'GITLAB_CALLBACK_URL',
            'PAYPAL_CLIENT_ID',
            'PAYPAL_SECRET',
            'STRIPE_KEY',
            'STRIPE_SECRET',
            'IM_API_KEY',
            'IM_AUTH_TOKEN',
            'RAZORPAY_KEY',
            'RAZORPAY_KEY',
            'PAYSTACK_PUBLIC_KEY',
            'PAYSTACK_SECRET_KEY',
            'PAYSTACK_PAYMENT_URL',
            'PAYSTACK_MERCHANT_EMAIL',
            'PAYTM_ENVIRONMENT',
            'PAYTM_MERCHANT_ID',
            'PAYTM_MERCHANT_KEY',
            'PAYTM_MERCHANT_WEBSITE',
            'PAYTM_CHANNEL',
            'NOCAPTCHA_SITEKEY',
            'NOCAPTCHA_SECRET',
            'AMAZON_LOGIN_ID',
            'AMAZON_LOGIN_SECRET',
            'AMAZON_LOGIN_REDIRECT',
            'BBB_SECURITY_SALT',
            'BBB_SERVER_BASE_URL',
            'LINKEDIN_CLIENT_ID',
            'LINKEDIN_CLIENT_SECRET',
            'LINKEDIN_CALLBACK_URL',
            'TWITTER_CLIENT_ID',
            'TWITTER_CLIENT_SECRET',
            'TWITTER_CALLBACK_URL',
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
            'AWS_DEFAULT_REGION',
            'AWS_BUCKET',
            'AWS_URL',
            'PAYU_METHOD',
            'PAYU_DEFAULT',
            'PAYU_MERCHANT_KEY',
            'PAYU_MERCHANT_SALT',
            'PAYU_MONEY_TRUE',
            'PAYU_AUTH_HEADER',
            'MOLLIE_KEY',
            'CASHFREE_APP_ID',
            'CASHFREE_SECRET_KEY',
            'CASHFREE_END_POINT',
            'SKRILL_MERCHANT_EMAIL',
            'SKRILL_API_PASSWORD',
            'SKRILL_LOGO_URL',
            'RAVE_PUBLIC_KEY',
            'RAVE_LOGO',
            'RAVE_PREFIX',
            'RAVE_COUNTRY',
            'OMISE_PUBLIC_KEY',
            'OMISE_SECRET_KEY',
            'OMISE_API_VERSION',
            'PAYHERE_MERCHANT_ID',
            'PAYHERE_BUISNESS_APP_CODE',
            'PAYHERE_APP_SECRET',
            'PAYHERE_MODE',
            'IYZIPAY_BASE_URL',
            'IYZIPAY_API_KEY',
            'IYZIPAY_SECRET_KEY',
            'STORE_ID',
            'STORE_PASSWORD',
            'YOUTUBE_API_KEY',
            'VIMEO_CLIENT',
            'VIMEO_SECRET',
            'VIMEO_ACCESS',
            'ONESIGNAL_APP_ID',
            'ONESIGNAL_REST_API_KEY',
            'TWILIO_SID',
            'TWILIO_AUTH_TOKEN',
            'TWILIO_NUMBER',
            'AAMARPAY_STORE_ID',
            'AAMARPAY_KEY',
            'AAMARPAY_SANDBOX',
            'BRAINTREE_ENV',
            'BRAINTREE_MERCHANT_ID',
            'BRAINTREE_PUBLIC_KEY',
            'BRAINTREE_PRIVATE_KEY',
            'GOOGLE_TAG_MANAGER_ID',
            'GOOGLE_TAG_MANAGER_ENABLED',

        ],
        '_SERVER' => [
            'APP_KEY',
            'DB_USERNAME',
            'DB_DATABASE',
            'DB_PASSWORD',
            'MAIL_FROM_NAME',
            'MAIL_FROM_ADDRESS',
            'MAIL_DRIVER',
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
            'MAIL_ENCRYPTION',
            'FACEBOOK_CLIENT_ID',
            'FACEBOOK_CLIENT_SECRET',
            'FACEBOOK_CALLBACK_URL',
            'GOOGLE_CLIENT_ID',
            'GOOGLE_CLIENT_SECRET',
            'GOOGLE_CALLBACK_URL',
            'GITLAB_CLIENT_ID',
            'GITLAB_CLIENT_SECRET',
            'GITLAB_CALLBACK_URL',
            'PAYPAL_CLIENT_ID',
            'PAYPAL_SECRET',
            'STRIPE_KEY',
            'STRIPE_SECRET',
            'IM_API_KEY',
            'IM_AUTH_TOKEN',
            'RAZORPAY_KEY',
            'RAZORPAY_KEY',
            'PAYSTACK_PUBLIC_KEY',
            'PAYSTACK_SECRET_KEY',
            'PAYSTACK_PAYMENT_URL',
            'PAYSTACK_MERCHANT_EMAIL',
            'PAYTM_ENVIRONMENT',
            'PAYTM_MERCHANT_ID',
            'PAYTM_MERCHANT_KEY',
            'PAYTM_MERCHANT_WEBSITE',
            'PAYTM_CHANNEL',
            'NOCAPTCHA_SITEKEY',
            'NOCAPTCHA_SECRET',
            'AMAZON_LOGIN_ID',
            'AMAZON_LOGIN_SECRET',
            'AMAZON_LOGIN_REDIRECT',
            'BBB_SECURITY_SALT',
            'BBB_SERVER_BASE_URL',
            'LINKEDIN_CLIENT_ID',
            'LINKEDIN_CLIENT_SECRET',
            'LINKEDIN_CALLBACK_URL',
            'TWITTER_CLIENT_ID',
            'TWITTER_CLIENT_SECRET',
            'TWITTER_CALLBACK_URL',
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
            'AWS_DEFAULT_REGION',
            'AWS_BUCKET',
            'AWS_URL',
            'PAYU_METHOD',
            'PAYU_DEFAULT',
            'PAYU_MERCHANT_KEY',
            'PAYU_MERCHANT_SALT',
            'PAYU_MONEY_TRUE',
            'PAYU_AUTH_HEADER',
            'MOLLIE_KEY',
            'CASHFREE_APP_ID',
            'CASHFREE_SECRET_KEY',
            'CASHFREE_END_POINT',
            'SKRILL_MERCHANT_EMAIL',
            'SKRILL_API_PASSWORD',
            'SKRILL_LOGO_URL',
            'RAVE_PUBLIC_KEY',
            'RAVE_LOGO',
            'RAVE_PREFIX',
            'RAVE_COUNTRY',
            'OMISE_PUBLIC_KEY',
            'OMISE_SECRET_KEY',
            'OMISE_API_VERSION',
            'PAYHERE_MERCHANT_ID',
            'PAYHERE_BUISNESS_APP_CODE',
            'PAYHERE_APP_SECRET',
            'PAYHERE_MODE',
            'IYZIPAY_BASE_URL',
            'IYZIPAY_API_KEY',
            'IYZIPAY_SECRET_KEY',
            'STORE_ID',
            'STORE_PASSWORD',
            'YOUTUBE_API_KEY',
            'VIMEO_CLIENT',
            'VIMEO_SECRET',
            'VIMEO_ACCESS',
            'ONESIGNAL_APP_ID',
            'ONESIGNAL_REST_API_KEY',
            'TWILIO_SID',
            'TWILIO_AUTH_TOKEN',
            'TWILIO_NUMBER',
            'AAMARPAY_STORE_ID',
            'AAMARPAY_KEY',
            'AAMARPAY_SANDBOX',
            'BRAINTREE_ENV',
            'BRAINTREE_MERCHANT_ID',
            'BRAINTREE_PUBLIC_KEY',
            'BRAINTREE_PRIVATE_KEY',
            'GOOGLE_TAG_MANAGER_ID',
            'GOOGLE_TAG_MANAGER_ENABLED',

        ],
        '_POST' => [
            'password',
        ],
    ],

];
