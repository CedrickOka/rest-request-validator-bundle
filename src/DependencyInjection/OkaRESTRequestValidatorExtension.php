<?php
namespace Oka\RESTRequestValidatorBundle\DependencyInjection;

use Oka\RESTRequestValidatorBundle\EventListener\ExceptionListener;
use Oka\RESTRequestValidatorBundle\Service\ErrorResponseFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;

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
		
		$definition = $container->getDefinition(ErrorResponseFactory::class);
		$definition->replaceArgument(0, $config['response']['error_builder_class']);
		
		if (true === $config['enabled_exception_listener']) {
// 			public function __construct($controller, LoggerInterface $logger = null, $debug = false)
			$exceptionListener = $container->setDefinition('oka_rest_request_validator.exception.event_listener', new Definition(ExceptionListener::class));
			$exceptionListener->addArgument($config['exception_controller']);
			$exceptionListener->addArgument(new Reference('logger'));
			$exceptionListener->addArgument(new Parameter('kernel.debug'));
			$exceptionListener->addTag('kernel.event_subscriber');
			$exceptionListener->addTag('monolog.logger', ['channel' => 'request']);
			
// 			$exceptionListener->addArgument(new Reference('translator'));
// 			$exceptionListener->addArgument(new Reference('oka_rest_request_validator.error_response.factory'));
// 			$exceptionListener->addArgument(new Reference('logger'));
// 			$exceptionListener->addArgument(new Parameter('kernel.environment'));
// 			$exceptionListener->addTag('kernel.event_listener', ['event' => 'kernel.exception', 'method' => 'onKernelException']);
// 			$exceptionListener->addTag('monolog.logger', ['channel' => 'request']);
		}
	}
}
