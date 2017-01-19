# Mocca

Mocca is a PHP 5.4+ micro-router for RESTful web services and APIs.

## Usage

Routes are defined by `Mocca\route($metod, $mask, $action)` method. Each route has callable action and can be limited to specific HTTP method(s) and URI mask.

`Mocca\run()` starts the routing. Routes are matched in the order as they were defined. Nested routes are also supported. At each nested level the first route that matches the request is invoked.

### Routing example
```php
# GET or PUT /books/42
Mocca\route(['get', 'put'], '/books/:id', function ($id) { });

# any /books[/novels]
Mocca\route(null, '/books/:string?', function ($category) {

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
