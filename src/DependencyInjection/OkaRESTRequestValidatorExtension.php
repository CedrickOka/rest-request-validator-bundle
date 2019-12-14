<?php
namespace Oka\RESTRequestValidatorBundle\DependencyInjection;

use Oka\RESTRequestValidatorBundle\EventListener\ExceptionListener;
use Oka\RESTRequestValidatorBundle\Service\ErrorResponseFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class OkaRESTRequestValidatorExtension extends Extension
{
	/**
	 * {@inheritdoc}
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);
		
		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.yml');
		
		$definition = $container->getDefinition('oka_rest_request_validator.error_response.factory');
		$definition->replaceArgument(1, $config['response']['error_builder_class']);
		
		if (true === $config['exception']['enabled']) {
			$exceptionListener = $container->setDefinition('oka_rest_request_validator.exception.event_listener', new Definition(ExceptionListener::class));
			$exceptionListener->addArgument($config['exception']['controller']);
			$exceptionListener->addArgument(new Reference('logger'));
			$exceptionListener->addArgument(new Parameter('kernel.debug'));
			$exceptionListener->addTag('kernel.event_subscriber');
			$exceptionListener->addTag('monolog.logger', ['channel' => 'request']);
		}
	}
}
