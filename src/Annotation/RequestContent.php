<?php
namespace Oka\RESTRequestValidatorBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 * @Annotation
 * @Target("METHOD")
 */
final class RequestContent
{
	/**
	 * Content type list
	 * Available values are: `form`, `json`, `xml`
	 * 
	 * @Attribute(name="formats", type="array", required=false)
	 * @var array $formats
	 */
	protected $formats;
	
	/**
	 * @Attribute(name="constraints", type="string", required=false)
	 * @var string $constraints
	 */
	protected $constraints;
	
	/**
	 * @Attribute(name="can_be_empty", type="boolean", required=false)
	 * @var boolean $canBeEmpty
	 */
	protected $canBeEmpty;
	
	/**
	 * @Attribute(name="enable_validation", type="boolean", required=false)
	 * @var boolean $enableValidation
	 */
	protected $enableValidation;
	
	/**
	 * @Attribute(name="validation_error_message", type="string", required=false)
	 * @var string $validationErrorMessage
	 */
	protected $validationErrorMessage;
	
	/**
	 * @Attribute(name="translation", type="array", required=false)
	 * @var array $translation
	 */
	protected $translation;
	
	/**
	 * @var string $translationDomain
	 */
	private $translationDomain;
	
	/**
	 * @var string $translationParameters
	 */
	private $translationParameters;
	
	/**
	 * @param array $data
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $data)
	{
		$this->constraints = $data['constraints'] ?? null;
		$this->canBeEmpty = (bool) ($data['can_be_empty'] ?? false);
		$this->enableValidation = (bool) ($data['enable_validation'] ?? true);
		
		if (null === $this->constraints && true === $this->enableValidation) {
			throw new \InvalidArgumentException('You must define a "constraints" attribute for each @RequestContent annotation while request validation is enabled.');
		}
		
		if (false === isset($data['translation'])) {
			$this->translationDomain = 'OkaRESTRequestValidatorBundle';
			$this->translationParameters = [];
		} else {
			$this->translationDomain = (string) ($data['translation']['domain'] ?? 'OkaRESTRequestValidatorBundle');
			$this->translationParameters = $data['translation']['parameters'] ?? [];
			
			if (false === is_array($this->translationParameters)) {
				throw new \InvalidArgumentException('You must define a "constraints" attribute for each @RequestContent annotation while request validation is enabled.');
			}
		}
		
		$this->formats = $data['formats'] ?? [];
		$this->validationErrorMessage = (string) ($data['validation_error_message'] ?? 'request.format.invalid');
		
		if (false === is_array($this->formats)) {
			$this->formats = [$this->formats];
		}
	}
	
	public function getFormats() :array
	{
		return $this->formats;
	}
	
	public function getConstraints() :?string
	{
		return $this->constraints;
	}
	
	public function isCanBeEmpty():bool
	{
		return $this->canBeEmpty;
	}
	
	public function isEnableValidation() :bool
	{
		return $this->enableValidation;
	}
	
	public function getValidationErrorMessage() :string
	{
		return $this->validationErrorMessage;
	}
	
	public function getTranslationDomain() :string
	{
		return $this->translationDomain;
	}
	
	public function getTranslationParameters() :array
	{
		return $this->translationParameters;
	}
}
