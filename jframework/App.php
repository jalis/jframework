<?php
namespace JFramework;

class App {
	const VERSION = '0.0.1';

	private string	$app_dir, $config_file;
	private array	$config, $middlewares = [];

	private Router	$router;

	protected array	$context = [];

	/**
	 * Creates a new App. Adds $app_dir to include path.
	 * 
	 * @param string $app_dir		Directory that contains the app files
	 * @param string $config_file	Path to configuration file relative to $app_dir
	 */
	public function __construct(string $app_dir = '', string $config_file = 'config.php') {
		if(!$app_dir) {
			$this->app_dir = $_SERVER['DOCUMENT_ROOT'];
		} else {
			$this->app_dir = $app_dir;
		}

		set_include_path(get_include_path() . PATH_SEPARATOR . $this->app_dir);

		$this->config_file = "$this->app_dir/$config_file";

		if(is_file($this->config_file)) {
			$config = (function($app) {
				return require $this->config_file;
			})($this);

			if(!is_array($config)) {
				throw new \Exception("Config file '$this->config_file' does not return an array");
			}
		}

		if(isset($config)) {
			$this->config = $config + [
				'router'		=> Router::class,
				'routes_dir'	=> 'routes',
				'lib_dir'		=> 'lib'
			];
		} else {
			$this->config = [
				'router'		=> Router::class,
				'routes_dir'	=> 'routes',
				'lib_dir'		=> 'lib'
			];
		}

		$this->router = new $this->config['router']();
		$this->router->scandir($this->app_dir . DIRECTORY_SEPARATOR . $this->config['routes_dir']);
	}

	/**
	 * Registers middleware to be used with the app
	 * 
	 * @param callable(&array):void	$middleware	Callable that takes a reference to the context array
	 */
	public function middleware(callable $middleware) {
		$this->middlewares[] = $middleware;
	}

	/**
	 * Runs the appropriate route with middleware
	 */
	public function run() {
		$this->router->route($_SERVER['REQUEST_URI'], $this->context);

		if($this->context['route'] instanceof \Exception) throw $this->context['route'];

		foreach($this->middlewares as $middleware) $middleware($this->context);

		(function($_CONTEXT) {
			extract($_CONTEXT['params']);

			require $_CONTEXT['route']->path;
		})($this->context);
	}
}