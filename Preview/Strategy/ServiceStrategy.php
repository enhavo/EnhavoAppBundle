<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\AppBundle\Preview\Strategy;

use Enhavo\Bundle\AppBundle\Exception\PreviewException;
use Enhavo\Bundle\AppBundle\Preview\ArgumentResolver\ContentDocumentValueArgumentResolver;
use Enhavo\Bundle\AppBundle\Preview\PreviewManager;
use Enhavo\Bundle\AppBundle\Preview\StrategyInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;

/**
 * @author gseidel
 */
class ServiceStrategy implements StrategyInterface
{
    use ContainerAwareTrait;

    /** @var ArgumentResolverInterface */
    private $argumentResolver;

    /** @var RequestStack */
    private $requestStack;

    /** @var PreviewManager */
    private $previewManager;

    /**
     * ServiceStrategy constructor.
     */
    public function __construct(ArgumentResolverInterface $argumentResolver, RequestStack $requestStack, PreviewManager $previewManager)
    {
        $this->argumentResolver = $argumentResolver;
        $this->requestStack = $requestStack;
        $this->previewManager = $previewManager;
    }

    public function getPreviewResponse($resource, $options = [])
    {
        $service = $options['service'];
        if (null === $service) {
            throw new PreviewException('You choose the strategy service, but you didn\'t pass any service to call. Please use the service parameter with "service_id:functionName" notation');
        }
        $parts = preg_split('#:#', $service);
        if (2 !== count($parts)) {
            throw new PreviewException(sprintf('The service parameter need a notation like "service_id:functionName" but you got "%s"', $service));
        }

        $serviceName = $parts[0];
        $invokeService = null;
        try {
            $invokeService = $this->container->get($serviceName);
        } catch (ServiceNotFoundException $e) {
            throw new PreviewException(sprintf('The service parameter in preview route should be an existing service, got "%s"', $serviceName));
        }

        $invokeFunction = $parts[1];
        if (!method_exists($invokeService, $invokeFunction)) {
            throw new PreviewException(sprintf('The defined function "%s " in service "%s" for preview route does not exist, check your service config "%s"', $invokeFunction, $serviceName, $service));
        }

        $controller = [$invokeService, $invokeFunction];

        $this->previewManager->enablePreview();
        $request = $this->requestStack->getCurrentRequest();
        $arguments = $this->argumentResolver->getArguments($request, $controller);

        foreach ($arguments as &$argument) {
            if (ContentDocumentValueArgumentResolver::PLACEHOLDER === $argument) {
                $argument = $resource;
            }
        }

        return call_user_func($controller, ...$arguments);
    }
}
