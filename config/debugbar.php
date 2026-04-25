<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Debugbar Settings
     |--------------------------------------------------------------------------
     |
     | Debugbar is enabled by default, when debug is set to true in app.php.
     | You can override the value by setting enable to true or false instead of null.
     |
     | You can provide an array of URI's that must be ignored (eg. 'api/*')
     |
     */

    'enabled' => env('DEBUGBAR_ENABLED'),
    'hide_empty_tabs' => env('DEBUGBAR_HIDE_EMPTY_TABS', true), // Hide tabs until they have content
    'except' => [
        'telescope*',
        'horizon*',
        '_boost/browser-logs',
    ],

    /*
     |--------------------------------------------------------------------------
     | Storage settings
     |--------------------------------------------------------------------------
     |
     | Debugbar stores data for session/ajax requests.
     | You can disable this, so the debugbar stores data in headers/session,
     | but this can cause problems with large data collectors.
     | By default, file storage (in the storage folder) is used. Redis and PDO
     | can also be used. For PDO, run the package migrations first.
     |
     | Warning: Enabling storage.open will allow everyone to access previous
     | request, do not enable open storage in publicly available environments!
     | Specify a callback if you want to limit based on IP or authentication.
     | Leaving it to null will allow localhost only.
     */
    'storage' => [
        'enabled'    => env('DEBUGBAR_STORAGE_ENABLED', true),
        'open'       => env('DEBUGBAR_OPEN_STORAGE'), // bool/callback.
        'driver'     => env('DEBUGBAR_STORAGE_DRIVER', 'file'), // redis, file, pdo, socket, custom
        'path'       => env('DEBUGBAR_STORAGE_PATH', storage_path('debugbar')), // For file driver
        'connection' => env('DEBUGBAR_STORAGE_CONNECTION'), // Leave null for default connection (Redis/PDO)
        'provider'   => env('DEBUGBAR_STORAGE_PROVIDER', ''), // Instance of StorageInterface for custom driver
        'hostname'   => env('DEBUGBAR_STORAGE_HOSTNAME', '127.0.0.1'), // Hostname to use with the "socket" driver
        'port'       => env('DEBUGBAR_STORAGE_PORT', 2304), // Port to use with the "socket" driver
    ],

    /*
    |--------------------------------------------------------------------------
    | Editor
    |--------------------------------------------------------------------------
    |
    | Choose your preferred editor to use when clicking file name.
    |
    | Supported: "sublime", "textmate", "emacs", "macvim", "codelite",
    |            "phpstorm", "phpstorm-remote", "idea", "idea-remote",
    |            "vscode", "vscode-insiders", "vscode-remote", "vscode-insiders-remote",
    |            "vscodium", "nova", "xdebug", "atom", "espresso",
    |            "netbeans", "cursor", "windsurf", "zed", "antigravity"
    |
    */

    'editor' => env('DEBUGBAR_EDITOR') ?: env('IGNITION_EDITOR', 'phpstorm'),

    /*
    |--------------------------------------------------------------------------
    | Remote Path Mapping
    |--------------------------------------------------------------------------
    |
    | If you are using a remote dev server, like Laravel Homestead, Docker, or
    | even a remote VPS, it will be necessary to specify your path mapping.
    |
    | Leaving one, or both of these, empty or null will not trigger the remote
    | URL changes and Debugbar will treat your editor links as local files.
    |
    | "remote_sites_path" is an absolute base path for your sites or projects
    | in Homestead, Vagrant, Docker, or another remote development server.
    |
    | Example value: "/home/vagrant/Code"
    |
    | "local_sites_path" is an absolute base path for your sites or projects
    | on your local computer where your IDE or code editor is running on.
    |
    | Example values: "/Users/<name>/Code", "C:\Users\<name>\Documents\Code"
    |
    */

    'remote_sites_path' => env('DEBUGBAR_REMOTE_SITES_PATH'),
    'local_sites_path' => env('DEBUGBAR_LOCAL_SITES_PATH', env('IGNITION_LOCAL_SITES_PATH')),

    /*
     |--------------------------------------------------------------------------
     | Vendors
     |--------------------------------------------------------------------------
     |
     | Vendor files are included by default, but can be set to false.
     | This can also be set to 'js' or 'css', to only include javascript or css vendor files.
     | Vendor files are for css: font-awesome (include <css_url?>) and highlight.js (js and css)
     | and for js: jquery and and highlight.js
     | So if you want syntax highlighting, set it to true.
     | jQuery is set to not conflict with existing jQuery scripts.
     |
     */

    'include_vendors' => env('DEBUGBAR_INCLUDE_VENDORS', true),

    /*
     |--------------------------------------------------------------------------
     | Capture Ajax Requests
     |--------------------------------------------------------------------------
     |
     | The Debugbar can capture Ajax requests and display them. If you don't want this (ie. because of errors),
     | you can use this option to disable sending the data through the headers.
     |
     | Optionally, you can also send ServerTiming headers on ajax requests for the Chrome DevTools.
     |
     | Note for your request to be identified as ajax requests they must either send the header
     | X-Requested-With: XMLHttpRequest or have the dataType parameter set to jsonp in the ajax call.
     |
     */

    'capture_ajax' => env('DEBUGBAR_CAPTURE_AJAX', true),
    'add_ajax_timing' => env('DEBUGBAR_AJAX_TIMING', false),

    /*
     |--------------------------------------------------------------------------
     | Custom Error Handler for Deprecated warnings
     |--------------------------------------------------------------------------
     |
     | When enabled, the Debugbar shows deprecated warnings for Symfony components
     | in the Messages tab.
     |
     */
    'error_handler' => env('DEBUGBAR_ERROR_HANDLER', false),

    /*
     |--------------------------------------------------------------------------
     | Clockwork integration
     |--------------------------------------------------------------------------
     |
     | The Debugbar can emulate the Clockwork headers, so you can use the Chrome
     | Extension, Clockwork Dev Tools.
     |
     */
    'clockwork' => env('DEBUGBAR_CLOCKWORK', false),

    /*
     |--------------------------------------------------------------------------
     | DataCollectors
     |--------------------------------------------------------------------------
     |
     | Enable/disable DataCollectors
     |
     */

    'collectors' => [
        'phpinfo'         => env('DEBUGBAR_COLLECTOR_PHPINFO', true),         // Php version
        'messages'        => env('DEBUGBAR_COLLECTOR_MESSAGES', true),        // Messages
        'time'            => env('DEBUGBAR_COLLECTOR_TIME', true),            // Time Datalogger
        'memory'          => env('DEBUGBAR_COLLECTOR_MEMORY', true),          // Memory usage
        'exceptions'      => env('DEBUGBAR_COLLECTOR_EXCEPTIONS', true),      // Exception displayer
        'log'             => env('DEBUGBAR_COLLECTOR_LOG', true),             // Logs from Monolog (merged in messages if enabled)
        'db'              => env('DEBUGBAR_COLLECTOR_DB', true),              // Show database (PDO) queries and bindings
        'views'           => env('DEBUGBAR_COLLECTOR_VIEWS', true),           // Views with their data
        'route'           => env('DEBUGBAR_COLLECTOR_ROUTE', true),           // Current route information
        'auth'            => env('DEBUGBAR_COLLECTOR_AUTH', false),           // Display Laravel authentication status
        'gate'            => env('DEBUGBAR_COLLECTOR_GATE', false),           // Display Laravel gates check
        'session'         => env('DEBUGBAR_COLLECTOR_SESSION', false),        // Display session data
        'symfony_request' => env('DEBUGBAR_COLLECTOR_SYMFONY_REQUEST', true), // Only one can be enabled..
        'mail'            => env('DEBUGBAR_COLLECTOR_MAIL', true),            // Catch mail messages
        'laravel'         => env('DEBUGBAR_COLLECTOR_LARAVEL', false),        // Laravel version and environment
        'events'          => env('DEBUGBAR_COLLECTOR_EVENTS', false),         // All events fired
        'default_request' => env('DEBUGBAR_COLLECTOR_DEFAULT_REQUEST', false), // Regular or special Symfony request logger
        'logs'            => env('DEBUGBAR_COLLECTOR_LOGS', false),           // Add the latest log messages
        'files'           => env('DEBUGBAR_COLLECTOR_FILES', false),          // Show the included files
        'config'          => env('DEBUGBAR_COLLECTOR_CONFIG', false),         // Display config values
        'cache'           => env('DEBUGBAR_COLLECTOR_CACHE', false),          // Display cache events
        'models'          => env('DEBUGBAR_COLLECTOR_MODELS', true),          // Display models
        'livewire'        => env('DEBUGBAR_COLLECTOR_LIVEWIRE', true),        // Display Livewire (when available)
        'jobs'            => env('DEBUGBAR_COLLECTOR_JOBS', false),           // Display dispatched jobs
    ],

    /*
     |--------------------------------------------------------------------------
     | Extra options
     |--------------------------------------------------------------------------
     |
     | Configure some DataCollectors
     |
     */

    'options' => [
        'auth' => [
            'show_name' => env('DEBUGBAR_AUTH_SHOW_NAME', true),   // Also show the users name/email in the debugbar
        ],
        'db' => [
            'with_params'       => env('DEBUGBAR_DB_WITH_PARAMS', true),   // Render SQL with the parameters substituted
            'backtrace'         => env('DEBUGBAR_DB_BACKTRACE', true),   // Use Symfony VarDumper for parameters
            'backtrace_exclude_paths' => env('DEBUGBAR_DB_BACKTRACE_EXCLUDE_PATHS', []), // Paths to exclude from backtrace
            'timeline'          => env('DEBUGBAR_DB_TIMELINE', false),  // Add the queries to the timeline
            'duration_background'  => env('DEBUGBAR_DB_DURATION_BACKGROUND', true),   // Show shaded background on SQL timeline
            'explain' => [                 // Show EXPLAIN output on queries
                'enabled' => env('DEBUGBAR_DB_EXPLAIN_ENABLED', false),
                'types' => ['SELECT'],     // Deprecated setting, is always only SELECT
            ],
            'hints'             => env('DEBUGBAR_DB_HINTS', true),    // Show hints for common mistakes
            'show_copy'         => env('DEBUGBAR_DB_SHOW_COPY', false),  // Show copy button next to queries
            'slow_threshold'    => env('DEBUGBAR_DB_SLOW_THRESHOLD', false),   // Only show queries slower than this time in ms, false = show all
            'memory_usage'      => env('DEBUGBAR_DB_MEMORY_USAGE', false),   // Show memory usage for queries
            'soft_limit'        => env('DEBUGBAR_DB_SOFT_LIMIT', 100),   // After this number, no parameters/backtrace are shown
            'hard_limit'        => env('DEBUGBAR_DB_HARD_LIMIT', 500),   // After this number, queries are not registered
        ],
        'mail' => [
            'timeline' => env('DEBUGBAR_MAIL_TIMELINE', false),  // Add mails to the timeline
            'show_body' => env('DEBUGBAR_MAIL_SHOW_BODY', true),
        ],
        'views' => [
            'timeline' => env('DEBUGBAR_VIEWS_TIMELINE', false),  // Add the views to the timeline (Experimental)
            'data' => env('DEBUGBAR_VIEWS_DATA', false),    //true for all data, 'keys' for only names, false for no parameters.
            'group' => env('DEBUGBAR_VIEWS_GROUP', 50),  // Group views that are called more than this amount in the same file
            'exclude_paths' => env('DEBUGBAR_VIEWS_EXCLUDE_PATHS', []), // Add the paths which you don't want to appear in the views
        ],
        'route' => [
            'label' => env('DEBUGBAR_ROUTE_LABEL', true),  // show complete route on bar
        ],
        'logs' => [
            'file' => env('DEBUGBAR_LOGS_FILE', null),
        ],
        'cache' => [
            'values' => env('DEBUGBAR_CACHE_VALUES', true), // collect cache values
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Inject Debugbar in Response
     |--------------------------------------------------------------------------
     |
     | Usually, the debugbar is added just before </body>, by listening to the
     | Response after the App is done. If you disable this, you have to add them
     | in your template yourself. See http://phpdebugbar.com/docs/rendering.html
     |
     */

    'inject' => env('DEBUGBAR_INJECT', true),

    /*
     |--------------------------------------------------------------------------
     | Debugbar route prefix
     |--------------------------------------------------------------------------
     |
     | Sometimes you want to set route prefix to be used by Debugbar to load
     | its resources from. Usually the need comes from misconfigured web server or
     | from trying to overcome bugs like this: http://trac.nginx.org/nginx/ticket/97
     |
     */
    'route_prefix' => env('DEBUGBAR_ROUTE_PREFIX', '_debugbar'),

    /*
     |--------------------------------------------------------------------------
     | Debugbar route domain
     |--------------------------------------------------------------------------
     |
     | By default Debugbar will load its resources from the same domain that the
     | request was made. If you want to serve its resources from a different domain,
     | like a CDN, you can set a domain here. This will also add CORS headers.
     |
     */
    'route_domain' => env('DEBUGBAR_ROUTE_DOMAIN', null),

    /*
     |--------------------------------------------------------------------------
     | Debugbar theme
     |--------------------------------------------------------------------------
     |
     | The debugbar comes with two themes: 'light' and 'dark'.
     | You can change the theme here.
     |
     */
    'theme' => env('DEBUGBAR_THEME', 'auto'),

];
