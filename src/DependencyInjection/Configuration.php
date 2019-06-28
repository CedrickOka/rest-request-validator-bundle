<?php
namespace Oka\RESTRequestValidatorBundle\DependencyInjection;

use Oka\RESTRequestValidatorBundle\Model\ErrorResponseBuilderInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder('oka_rest_request_validator');
		
		if (true === method_exists($treeBuilder, 'getRootNode')) {
			$rootNode = $treeBuilder->getRootNode();
		} else {
			// BC layer for symfony/config 4.1 and older
			$rootNode = $treeBuilder->root('oka_rest_request_validator');
		}
		
		/** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
		$rootNode
				->addDefaultsIfNotSet()
				->children()
					->arrayNode('exception')
						->canBeDisabled()
						->addDefaultsIfNotSet()
						->children()
							->scalarNode('controller')
								->cannotBeEmpty()
								->defaultValue('OkaRESTRequestValidatorBundle:Exception:show')
							->end()
						->end()
					->end()
					->arrayNode('response')
						->addDefaultsIfNotSet()
						->children()
							->scalarNode('error_builder_class')
								->validate()
									->ifTrue(function($class){
										return null !== $class && !(new \ReflectionClass($class))->implementsInterface(ErrorResponseBuilderInterface::class);
									})
									->thenInvalid('The %s class must implement '.ErrorResponseBuilderInterface::class.'.')
								->end()
								->defaultNull()
							->end()
						->end()
					->end()
				->end();
		
		return $treeBuilder;
	}
}
