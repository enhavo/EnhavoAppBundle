<?php

namespace Enhavo\Bundle\AppBundle\Endpoint\Extension;

use Enhavo\Bundle\ApiBundle\Data\Data;
use Enhavo\Bundle\ApiBundle\Endpoint\AbstractEndpointTypeExtension;
use Enhavo\Bundle\ApiBundle\Endpoint\Context;
use Enhavo\Bundle\AppBundle\Endpoint\Type\AreaEndpointType;
use Enhavo\Bundle\AppBundle\Endpoint\Type\TemplateEndpointType;
use Enhavo\Bundle\AppBundle\Vue\RouteProvider\VueRouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VueRouteEndpointExtensionType extends AbstractEndpointTypeExtension
{
    public function __construct(
        private readonly VueRouteProviderInterface $provider,
    )
    {
    }

    public function handleRequest($options, Request $request, Data $data, Context $context)
    {
        if ($options['vue_routes_enabled']) {
            $routes = $this->provider->getRoutes($options['vue_routes_groups']);
            $context->set('vue_routes', $routes);
            if (count($routes) > 0) {
                $data['vue_routes'] = $this->normalize($routes);
            }
        }
    }

    public static function getExtendedTypes(): array
    {
        return [TemplateEndpointType::class, AreaEndpointType::class];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'vue_routes_groups' => null,
            'vue_routes_enabled' => false,
        ]);
    }
}
