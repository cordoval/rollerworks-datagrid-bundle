<?php

namespace Rollerworks\Component\DatagridBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Rollerworks\Component\DatagridBundle\DependencyInjection\Compiler\ExtensionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ExtensionPassTest extends AbstractCompilerPassTestCase
{
    public function testRegisteringOfColumnTypes()
    {
        $collectingService = new Definition();
        $collectingService->setArguments(array(null, array(), array()));

        $this->setDefinition('rollerworks_datagrid.extension', $collectingService);

        $collectedService = new Definition();
        $collectedService->addTag('rollerworks_datagrid.column_type', array('alias' => 'user_id'));
        $this->setDefinition('acme_user.datagrid.column_type.user_id', $collectedService);

        $this->compile();

        $collectingService = $this->container->findDefinition('rollerworks_datagrid.extension');

        $this->assertNull($collectingService->getArgument(0));
        $this->assertEquals(array('user_id' => 'acme_user.datagrid.column_type.user_id'), $collectingService->getArgument(1));
        $this->assertCount(0, $collectingService->getArgument(2));
    }

    public function testRegisteringOfColumnTypesExtensions()
    {
        $collectingService = new Definition();
        $collectingService->setArguments(array(null, array(), array()));

        $this->setDefinition('rollerworks_datagrid.extension', $collectingService);

        $collectedService = new Definition();
        $collectedService->addTag('rollerworks_datagrid.column_extension', array('alias' => 'field'));
        $this->setDefinition('acme_user.datagrid.column_extension.field', $collectedService);

        $this->compile();

        $collectingService = $this->container->findDefinition('rollerworks_datagrid.extension');

        $this->assertNull($collectingService->getArgument(0));
        $this->assertCount(0, $collectingService->getArgument(1));
        $this->assertEquals(
             array('field' => array('acme_user.datagrid.column_extension.field')),
             $collectingService->getArgument(2)
        );
    }

    public function testRegisteringOfDatagridExtensions()
    {
        $extensionDefinition = new Definition();
        $extensionDefinition->setArguments(array(null, array(), array()));
        $this->setDefinition('rollerworks_datagrid.extension', $extensionDefinition);

        $collectingService = new Definition();
        $collectingService->setArguments(
            array(
                array(new Reference('rollerworks_datagrid.extension')),
            )
        );

        $this->setDefinition('rollerworks_datagrid.registry', $collectingService);

        $collectedService = new Definition('DoctrineOrmExtension');
        $collectedService->addTag('rollerworks_datagrid.extension');
        $this->setDefinition('rollerworks_datagrid.extension.doctrine_orm', $collectedService);

        $this->compile();

        $collectingService = $this->container->findDefinition('rollerworks_datagrid.registry');

        $this->assertEquals(
            $collectingService->getArgument(0),
            array(
                new Reference('rollerworks_datagrid.extension'),
                new Reference('rollerworks_datagrid.extension.doctrine_orm'),
            )
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExtensionPass());
        $container->setDefinition('twig.loader.filesystem', new Definition('stdClass'));
    }
}
