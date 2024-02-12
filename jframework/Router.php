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

	public function route(string $path, array &$_CONTEXT) {
		$matches = [];
		$matched_route = null;
		foreach($this->routes as $route) {
			if(preg_match($route->regex, $path, $matches)) {
				$matched_route = $route;
				break;
			}
		}
		
		if(!$matched_route) {
			http_response_code(404);
		} else {
			array_slice($matches, 1);
			$_CONTEXT['route'] = $matched_route;
			$_CONTEXT['params'] = $matches;

			(function() use($_CONTEXT) {
				extract($_CONTEXT['params']);

				require $_CONTEXT['route']->path;
			})();

			http_response_code(200);
		}
	}
}