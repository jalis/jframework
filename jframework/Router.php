<?php
namespace JFramework;

use FilesystemIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class Router {
	private string $basedir = '';
	private array $routes = [];

	public function __construct(string $dir) {
		$this->basedir = $dir;
		$this->scanRoutes($dir);
		$this->sortRoutes();
	}
	
	public function scanRoutes(string $dir) {
		if(!is_dir($dir)) throw new \Exception("'$dir' is not a valid routes directory");

		$iter = new RegexIterator(new RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY), "/.*\/(?:page|server).php$/i", RecursiveRegexIterator::GET_MATCH);

		foreach($iter as [$filepath]) {
			$this->addRoute($filepath);
		}
	}

	private function addRoute(string $filepath) {
		$route = new Router\Route;
		$route->path = $filepath;
		$route->uri = str_replace(DIRECTORY_SEPARATOR, '/', dirname(str_replace($this->basedir, '', $filepath)));
		$route->weight = 0;
		$route->depth = substr_count($route->uri, '/');

		$escaped_uri = preg_quote(rtrim($route->uri, '/'), '/');
		$escaped_uri = str_replace(['\[', '\]'], ['[', ']'], $escaped_uri);
		$route->regex = '/^' . preg_replace('/\[([^\/]*?)\]/', '(?<$1>[^\/]*)', $escaped_uri, -1, $route->weight) . '\/?$/';

		$this->routes[] = $route;
	}

	private function sortRoutes() {
		usort($this->routes, function($a, $b) {
			return match(true) {
				$a->depth !== $b->depth		=> $a->depth - $b->depth,
				default						=> $a->weight - $b->weight
			};
		});
	}

	/**
	 * Adds route data to context at key 'route' and route params at key 'params'
	 * 
	 * @param string $path		The requested path, usually equal to $_SERVER['REQUEST_URI']
	 * @param array &$_CONTEXT	App context array
	 */
	public function route(string $path, array &$context): bool {
		$matches = [];
		$matched_route = null;

		$path = rtrim($path, '/');
		$depth = substr_count($path, '/');
		foreach(array_filter($this->routes, fn($route) => $route->depth === $depth) as $route) {
			if(preg_match($route->regex, $path, $matches)) {
				$matched_route = $route;
				break;
			}
		}
		
		if(!$matched_route) {
			$context['route'] = new \Exception('No route', 1);
			$context['params'] = false;

			return false;
		} else {
			unset($matches[0]);
			$context['route'] = $matched_route;
			$context['params'] = $matches;

			return true;
		}
	}
}