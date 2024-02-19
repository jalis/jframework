<?php

namespace JFramework\Router;

/**
 * Datastore class for paths, ensuring certain properties
 */
class Route {
	public string	$path, $uri, $regex;
	public int		$weight, $depth;
}