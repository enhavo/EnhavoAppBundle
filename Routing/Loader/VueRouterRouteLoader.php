<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\AppBundle\Routing\Loader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

class VueRouterRouteLoader extends FileLoader
{
    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $collection = new RouteCollection();

        $resource = new FileResource($this->locator->locate($resource));
        $collection->addResource($resource);

        $content = $this->getContent($resource);
        $routes = $this->getRoutes($content);

        foreach ($routes as $name => $path) {
            $collection->add($name, new Route($path));
        }

        return $collection;
    }

    private function getContent(FileResource $resource)
    {
        $ext = pathinfo($resource->getResource(), PATHINFO_EXTENSION);

        if ('yaml' === $ext) {
            return Yaml::parseFile($resource->getResource());
        } elseif ('json' === $ext) {
            return json_decode(file_get_contents($resource->getResource()), true);
        }

        throw new \Exception(sprintf('VueRouterRouteLoader can only read yaml or json formats. Trying to read "%s"', $resource->getResource()));
    }

    private function getRoutes(mixed $content): array
    {
        $routes = [];

        if (is_array($content)) {
            foreach ($content as $route) {
                if (isset($route['path'], $route['name'])) {
                    $routes[$route['name']] = $this->convertPath($route['path']);
                }
                if (isset($route['children'])) {
                    $childRoutes = $this->getRoutes($route['children']);
                    foreach ($childRoutes as $name => $path) {
                        $routes[$name] = $path;
                    }
                }
            }
        }

        return $routes;
    }

    private function convertPath($path)
    {
        $path = preg_replace_callback('/:([0-9A-Za-z_-]+)/', function ($matches) {
            return '{'.$matches[1].'}';
        }, $path);

        return $path;
    }

    public function supports(mixed $resource, ?string $type = null)
    {
        if ('vue' === $type) {
            return true;
        }

        return false;
    }
}
