# Mocca

Mocca is a PHP 5.4+ micro-router for RESTful web services and APIs.

## How it works

Routes are defined by `Mocca\route($metod, $mask, $action)` method. Each route has callable action and can be limited to specific HTTP method(s) and URI mask. By default routes are matched against `$_SERVER['REQUEST_METHOD']` and `$_SERVER['REQUEST_URI']` so these two global variables are required for proper work of the router.

URI masks are transformed into regular expressions. Mask may contain parameters specified using subpatterns. For convinience is also possible to use tokens `:int` (any integer), `:any` (any string), `:all` (full path). All matched parameters will be passed to the action callback as its arguments.

`Mocca\run()` starts the routing. Routes are matched in the order as they were defined. Nested routes are also supported. At each nested level the first route that matches the request is invoked.

### Usage
```php
# GET or PUT /books/42
Mocca\route(['get', 'put'], '/books/:int', function ($id) { });

# any /books[/novels]
Mocca\route(null, '/books/:any?', function ($category) {

    # GET /books[/novels]
    Mocca\route('get', null, function () use ($category) { });

    # any /books[/novels]
    Mocca\route(null, null, function () {
        http_response_code(405); // not allowed
    });
});

# any
Mocca\route(null, null, function () {
    http_response_code(404); // not found
});


Mocca\run();
```

## License
The MIT License (MIT)
