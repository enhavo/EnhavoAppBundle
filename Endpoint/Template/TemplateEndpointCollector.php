<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\AppBundle\Endpoint\Template;

use Enhavo\Bundle\AppBundle\Endpoint\Type\TemplateEndpointType;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class TemplateEndpointCollector
{
    public function __construct(
        private RouterInterface $router,
    ) {
    }

    /** @return TemplateEndpointEntry[] */
    public function collect(TemplateEndpointFilter $filter): array
    {
        $entries = [];
        $routes = $this->router->getRouteCollection();

        foreach ($routes as $routeName => $route) {
            $defaults = $route->getDefaults();
            if (isset($defaults['_endpoint']['type']) && in_array($defaults['_endpoint']['type'], ['template', TemplateEndpointType::class])) {
                if ($this->filter($filter, $routeName, $route, $defaults['_endpoint'])) {
                    $entries[] = $this->createEntry($route, $routeName, $defaults['_endpoint']);
                }
            }
        }

        return $entries;
    }

    private function filter(TemplateEndpointFilter $filter, ?string $routeName, Route $route, $endpointConfig): bool
    {
        if ($filter->getFulltext()) {
            if ($this->contains($filter->getFulltext(), $routeName)) {
                return true;
            } elseif ($this->contains($filter->getFulltext(), $endpointConfig['template'])) {
                return true;
            } elseif ($this->contains($filter->getFulltext(), $route->getPath())) {
                return true;
            } elseif ($this->contains($filter->getFulltext(), $endpointConfig['description'])) {
                return true;
            }

            return false;
        }

        if ($filter->getRouteName() && !$this->contains($filter->getRouteName(), $routeName)) {
            return false;
        }

        if ($filter->getPath() && !$this->contains($filter->getPath(), $route->getPath())) {
            return false;
        }

        if ($filter->getTemplate() && !$this->contains($filter->getTemplate(), $endpointConfig['template'])) {
            return false;
        }

        if ($filter->getDescription() && !$this->contains($filter->getDescription(), $endpointConfig['description'])) {
            return false;
        }

        return true;
    }

    private function createEntry(Route $route, $routeName, $endpointConfig)
    {
        return new TemplateEndpointEntry(
            $endpointConfig['template'] ?? null,
            $route->getPath(),
            $routeName,
            $endpointConfig['description'] ?? null,
            $endpointConfig['variants'] ?? null,
        );
    }

    private function contains(?string $needle, ?string $haystack): bool
    {
        if (is_string($haystack) && is_string($needle)) {
            return str_contains($haystack, $needle);
        }

        return false;
    }
}
