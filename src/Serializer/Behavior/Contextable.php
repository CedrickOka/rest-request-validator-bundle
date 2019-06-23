<?php
namespace Oka\RESTRequestValidatorBundle\Serializer\Behavior;

use JMS\Serializer\SerializationContext;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
trait Contextable
{
	/**
	 * @param array $groups
	 * @param array $fields
	 * @return \JMS\Serializer\SerializationContext
	 */
	public static function createSerializationContext(array $groups = ['details'], array $fields = []) {
		$context = new SerializationContext();
		$context->setGroups($groups);

		if (false === empty($fields)) {
			$context->addExclusionStrategy(new FieldsExclusionStrategyInterface($fields));
		}

		return $context;
	}
}
