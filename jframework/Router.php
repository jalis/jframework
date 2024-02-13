<?php
namespace JFramework;

class Router {
	private array $routes = [];

	public function __construct() {

	}
	
	public function scandir(string $dir) {
		if(!is_dir($dir)) throw new \Exception("'$dir' is not a valid routes directory");

		$this->__scandir($dir);
	}

	private function __scandir(string $dir, string $cur_path = DIRECTORY_SEPARATOR) {
		foreach(scandir($dir . $cur_path) as $file) {
			if(str_starts_with($file, '.')) continue;

			$filepath = $cur_path . $file;

			if(is_dir($dir . $filepath)) {
				$this->__scandir($dir, $filepath . DIRECTORY_SEPARATOR);
			} elseif(in_array($file, ['page.php', 'server.php'])) {
				$route = new Router\Route;
				$route->path = $dir . $filepath;
				$route->uri = str_replace(DIRECTORY_SEPARATOR, '/', dirname($filepath));
				$route->weight = 0;

				$escaped_uri = preg_quote(rtrim($route->uri, '/'), '/');
				$escaped_uri = str_replace(['\[', '\]'], ['[', ']'], $escaped_uri);
				$route->regex = '/^' . preg_replace('/\[([^\/]*?)\]/', '(?<$1>[^\/]*)', $escaped_uri, -1, $route->weight) . '\/?$/';

				$this->routes[] = $route;
			}
		}
	}

	/**
	 * Adds route data to context at key 'route' and route params at key 'params'
	 * 
	 * @param string $path		The requested path, usually equal to $_SERVER['REQUEST_URI']
	 * @param array &$_CONTEXT	App context array
	 */
	public function route(string $path, array &$context) {
		$matches = [];
		$matched_route = null;

		foreach($this->routes as $route) {
			if(preg_match($route->regex, $path, $matches)) {
				$matched_route = $route;
				break;
			}
		}
		
		if(!$matched_route) {
			$context['route'] = new \Exception('No route', 1);
			$context['params'] = false;
		} else {
			unset($matches[0]);
			$context['route'] = $matched_route;
			$context['params'] = $matches;
		}
	}
}