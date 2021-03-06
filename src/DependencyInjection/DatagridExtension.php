<?php

namespace Rollerworks\Component\DatagridBundle\DependencyInjection;

use Rollerworks\Component\Datagrid\Twig\Extension\DatagridExtension as TwigDatagridExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class DatagridExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($config, $container);
        $config = $this->processConfiguration($configuration, $config);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/services'));
        $loader->load('core.xml');
        $loader->load('type.xml');

        $this->configureTwig($container, $loader, $config['twig']);
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        $configuration = new Configuration($this->getAlias());

        $r = new \ReflectionObject($configuration);
        $container->addResource(new FileResource($r->getFileName()));

        return $configuration;
    }

    public function getAlias()
    {
        return 'rollerworks_datagrid';
    }

    private function configureTwig(ContainerBuilder $container, XmlFileLoader $loader, array $config)
    {
        $loader->load('twig.xml');

        $container->setParameter('rollerworks_datagrid.twig.themes', $config['themes']);
    }
}
