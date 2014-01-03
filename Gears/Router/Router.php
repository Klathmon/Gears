<?php
namespace Gears\Router;

use Gears\Exceptions\FileNotReadableException;
use Gears\Exceptions\InvalidFileFormatException;
use InvalidArgumentException;

/*
 * TODO: Full DocBlock annotations
 * TODO: Reverse route matching
 * TODO: Calling errors manually
 */

class Router
{
    const ROUTE_SEPARATOR        = '/';
    const NAMED_PARAMETER_PREFIX = ':';
    protected $routesFileArray;
    protected $baseRoute;


    public function __construct($routesFile)
    {
        if (!is_string($routesFile) || $routesFile == '') {
            throw new InvalidArgumentException('Parameter $routesFile must be a valid string!');
        } elseif (!is_readable($routesFile) || ($fileContents = file_get_contents($routesFile)) === false) {
            throw new FileNotReadableException('Routes JSON file is not readable!');
        } elseif (is_null($routes = json_decode($fileContents, true))) {
            throw new InvalidFileFormatException('Routes JSON file cannot be parsed!');
        } elseif (!isset($routes['errors']['404'])) {
            throw new InvalidFileFormatException('Routes JSON file must have 404 error route!');
        } else {
            $this->routesFileArray = $routes;
            $this->baseRoute       = trim($this->routesFileArray['baseUrl'], self::ROUTE_SEPARATOR);
        }
    }

    public function parseRoute($request)
    {
        $requestParts = self::partOutRoute(str_replace($this->baseRoute, '', $request));

        foreach ($this->routesFileArray['routes'] as $name => $route) {
            $routeParts = self::partOutRoute($route['route']);

            if (($namedParameters = $this->routeMatchesRequest($routeParts, $requestParts)) !== false) {
                return array_merge($this->getRouteInfo($route, $namedParameters), ['name' => $name]);
            }
        }

        //No routes matched... Return the 404 error stuff...
        return array_merge($this->getRouteInfo($this->routesFileArray['errors']['404']), ['name' => '404']);
    }

    private function routeMatchesRequest($routeParts, $requestParts)
    {
        if (count($routeParts) != count($requestParts)) {
            return false; //Return false if the number of parts don't match.
        }

        $namedParameters = [];
        foreach ($requestParts as $partNumber => $requestPart) {
            $routePart = $routeParts[$partNumber];

            if ($routePart[0] == self::NAMED_PARAMETER_PREFIX) {
                $namedParameters[$routePart] = $requestPart;
            } elseif ($routePart != $requestPart) {
                return false; //At the first non-matching thing, return false.
            }
        }

        //If we are here, that means the route matches! return the named parameters.
        return $namedParameters;
    }

    private function getRouteInfo($route, $namedParameters = [])
    {
        //Route matches the request, get the values.
        $returnVal['controller'] = $this->getValue($route['controller'], $namedParameters);
        $returnVal['action']     = $this->getValue($route['action'], $namedParameters);
        $returnVal['parameters'] = [];
        foreach ($route['parameters'] as $parameter) {
            $key                           = ($parameter[0] == self::NAMED_PARAMETER_PREFIX ? substr($parameter, 1) : $parameter);
            $returnVal['parameters'][$key] = $this->getValue($parameter, $namedParameters);
        }

        return $returnVal;
    }

    private function getValue($command, $namedParameters)
    {
        if ($command[0] == self::NAMED_PARAMETER_PREFIX && array_key_exists($command, $namedParameters)) {
            return $namedParameters[$command];
        } else {
            return $command;
        }
    }

    private static function partOutRoute($route)
    {
        return explode(self::ROUTE_SEPARATOR, trim($route, self::ROUTE_SEPARATOR));
    }
}