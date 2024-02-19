# JFramework
## Usage
Create a new `JFramework\App`, passing it the app directory filepath and optionally the config array.

```php
$app = new JFramework\App(__DIR__);
$app->run();
```
### Config
Defaults for the config array, as well as every key used by the router:
```php
[
	'router'		=> \JFramework\Router::class, // No true checking as of yet, ducktyping will work
	'routes_dir'	=> 'routes', // Relative to app dir, passed to App
	'lib_dir'		=> 'lib' // Same as above
];
```

## Routes
The `routes` directory will use the directory structure to route requests to page.php and server.php files within.

### Parameters
Routes can have named wildcards that will be provided to routed files in scope with their names. In addition, a `$_CONTEXT` array will be provided as well, which hooks (middleware) can populate, however this is not yet implemented.

Example routes directory tree:
```
src/routes/
├── [slug]
│   └── page.php
├── api
│   ├── page
│   └── user
│       ├── [id]
│       │   └── page.php
│       └── server.php
└── page.php
```
For example, if a client requested `/api/user/1/`, the request would be directed to `src/routes/api/page/[id]/page.php`, where its scope would have `$id = "1"`.


## TODO
- Add support for `layout.php` files in routes directory, which will be ran before `page.php`, with `page.php` output being inserted into `layout.php`'s output at some control symbol, for example `{content}`, similarly to SvelteKit's `<slot>` syntax.
- Add support for `hooks.php` at the root of app dir (default, with configurable path), which would get called before all routes, for example to populate `$_CONTEXT`.