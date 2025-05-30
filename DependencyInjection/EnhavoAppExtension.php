<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\AppBundle\DependencyInjection;

use Enhavo\Bundle\ResourceBundle\DependencyInjection\PrependExtensionTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class EnhavoAppExtension extends Extension implements PrependExtensionInterface
{
    use PrependExtensionTrait;

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('enhavo_app.mailer.mails', $config['mailer']['mails']);
        $container->setParameter('enhavo_app.mailer.defaults', $config['mailer']['defaults']);
        $container->setParameter('enhavo_app.mailer.model', $config['mailer']['model']);
        $container->setParameter('enhavo_app.menu', $config['menu']);
        $container->setParameter('enhavo_app.branding', $config['branding']);
        $container->setParameter('enhavo_app.login.route', $config['login']['route']);
        $container->setParameter('enhavo_app.login.route_parameters', $config['login']['route_parameters']);
        $container->setParameter('enhavo_app.template_paths', $config['template_paths']);
        $container->setParameter('enhavo_app.roles', $config['roles']);
        $container->setParameter('enhavo_app.form_themes', $config['form_themes'] ?? []);
        $container->setParameter('enhavo_app.locale', $config['locale']);
        $container->setParameter('enhavo_app.locale_resolver', $config['locale_resolver']);
        $container->setParameter('enhavo_app.toolbar_widget.primary', $config['toolbar_widget']['primary']);
        $container->setParameter('enhavo_app.toolbar_widget.secondary', $config['toolbar_widget']['secondary']);
        $container->setParameter('enhavo_app.vue.route_providers', $config['vue']['route_providers'] ?? []);
        $container->setParameter('enhavo_app.endpoint.template_url_prefix', $config['endpoint']['template_url_prefix'] ?? null);
        $container->setParameter('enhavo_app.areas', $config['area'] ?? []);
        $container->setParameter('enhavo_app.vite.builds', $config['vite']['builds'] ?? []);
        $container->setParameter('enhavo_app.form_mapping', ['admin' => $config['admin']['form_mapping'] ?? []]);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services/action.yaml');
        $loader->load('services/area.yaml');
        $loader->load('services/services.yaml');
        $loader->load('services/endpoint.yaml');
        $loader->load('services/twig.yaml');
        $loader->load('services/init.yaml');
        $loader->load('services/locale.yaml');
        $loader->load('services/command.yaml');
        $loader->load('services/menu.yaml');
        $loader->load('services/maker.yaml');
        $loader->load('services/widget.yaml');
        $loader->load('services/toolbar.yaml');
        $loader->load('services/preview.yaml');
    }

    protected function prependFiles(): array
    {
        return [
            __DIR__.'/../Resources/config/app/config.yaml',
        ];
    }
}
