# JFramework
## Usage
Create a new `JFramework\App`, passing it the app directory filepath and optionally the config file path relative to the app directory.
If the config file doesn't exist (default relative path: `config.php`) then the App will throw an exception.

```php
$app = new JFramework\App(__DIR__);
$app->run();
```
## Config file
The config file is invoked with the App in scope as `$app`. Register middleware with the `JFramework\App::middleware(callable)` method.
The config file must return an array, and that will be stored in the app as `$config`. Currently this is only used to define the router used by the app, the `routes` directory and the `lib` directory. Directory paths are relative to app directory filepath.

Example config file:
```php
$app->middleware(fn(&$context) => $context['test'] = 'Example middleware');

return [];
```

Defaults for the config array:
```php
[
	'router'		=> \JFramework\Router::class,
	'routes_dir'	=> 'routes',
	'lib_dir'		=> 'lib'
];
```