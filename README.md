# Logbook

This Package helps keeping track of what happens in your Laravel Application when it gets used
Exceptions, Model events, incoming requests - all kind of information to help understand how users are using the application and beeing able to act accordingly.

## Basic Usage

```php
use AwStudio\Logbook\Facades\Logbook;

class Action
{
    public function execute()
    {
        Logbook::log(['action_executed' => static::class]);
    }
}
```

### Logging Model Changes

To track changes made on a model, just add the `LogsEvents` trait to your model:

```php
class Post extends Model
{
    use LogsEvents;
}
```

### Logging requests

```php
class PostController extends Model
{
    public function store(Request $request) {
        Logbook::request();
    }
}
```

### Grouping Logs in a Batch

Using `Logbook::open()` you can group multiple logs into one batch. All logs performed, while a batch was opened
will receive the same unique batch identifier and optionally batch name:

```php
class PostController extends Model
{
    public function store(Request $request) {
        Logbook::open(); // or Logbook::open('Store new Post')
        Logbook::request();

        // Do your magic

        Logbook::close();
    }
}
```

### Logging Exceptions

Easily keep track of exceptions thrown in your application:

```php
// App\Exceptions
class Handler extends ExceptionHandler
{
    use LogExceptions;

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Logbook::exception($e);
        });
    }
}
```

As Laravel doesn't report all Exceptions, like AuthenticationExceptions, ValidationExceptions and many more,
this trat provides a propety `shouldLog` which you may use to define Exceptions that should still be logged, event if
they are not reported:

```php
protected $shouldLog = [
    \Illuminate\Validation\ValidationException::class,
];
```

### Logging Outgoing Mails

```php
// App/Providers/EventServiceProvider
class EventServiceProvider {
    use LogsOutgoingMessagesog;
    public function boot(){
        $this->logOutGoingMessages();
    }
}
```

When the channel is Set to `api`, this will only log the `messageID` and `subject` to the API to
keep the user details secret but will also create a local log copy with which will also contain the messageID for better traceability.

## Using the API Channel

By default, this package provides two different channels `file` and `api`.
When using the API Channel the logs are sent to the central Logbook API.
For this to work, you need to provide a `LOGBOOK_PROJEKT_TOKEN` in your `.env` file.
