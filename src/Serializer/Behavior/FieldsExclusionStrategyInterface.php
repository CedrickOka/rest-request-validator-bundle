<?php
namespace Oka\RESTRequestValidatorBundle\Serializer\Behavior;

use JMS\Serializer\Context;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class FieldsExclusionStrategyInterface implements ExclusionStrategyInterface
{
	private $fields = [];

	public function __construct(array $fields) {
		$this->fields = $fields;
	}
	
	public function shouldSkipClass(ClassMetadata $metadata, Context $context) {
		return false;
	}
	
	public function shouldSkipProperty(PropertyMetadata $property, Context $context) {
		return true === empty($this->fields) ? false : in_array($property->serializedName ?: $property->name, $this->fields);
	}
}
