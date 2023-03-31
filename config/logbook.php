<?php

return [

    /**
     * The channel to use for logbook writes.
     * Valid options:
     * - api
     * - file
     */
    'channel' => env('LOGBOOK_CHANNEL', 'api'),

    /**
     * The amount of days to keep logbook entries within the api?
     * Default: 90 days
     */
    'ttl' => env('LOGBOOK_ENTRY_TTL', 90),

    /**
     * The path to the logbook log file.
     * This will be used when the channel is set to file or api.
     */
    'log_path' => storage_path('logs/logbook.log'),

    /**
     * The endpoint to send the logbook to.
     */
    'api_endpoint' => env('LOGBOOK_ENDPOINT', 'https://api.logbook.aw-studio.de/log'),

    /**
     * The project token to use when sending the logbook to the api.
     */
    'project_token' => env('LOGBOOK_PROJECT_TOKEN'),

    /**
     * Values in these fields will be hidden from the logbook, for models and requests.
     */
    'hidden_fields' => [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'new_password_confirmation',
    ],

    /**
     * The class to use for obfuscating logbook entries.
     * This class must implement the Obfuscator interface.
     */
    'obfuscator_class' => AwStudio\Logbook\Support\AttributeObfuscator::class,

    'obfuscation_strategy' => 'mask',
    /**
     * Values in these fields will be obfuscated for the logbook.
     * This is useful for personal data like email addresses.
     * A model can provide additional obfuscated fields by ... (TODO)
     */
    'obfuscated_fields' => [
        'email',
        'mail',
        'first_name',
        'last_name',
        'full_name',
        'given_name',
        'family_name',
    ],

];
