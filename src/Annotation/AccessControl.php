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
final class AccessControl
{
	/**
	 * @Attribute(name="protocol", type="string", required=true)
	 * @var string $version
	 */
	protected $protocol;
	
	/**
	 * @Attribute(name="version", type="string", required=true)
	 * @var string $version
	 */
	protected $version;
	
	/**
	 * @Attribute(name="formats", type="string", required=true)
	 * @var array $formats
	 */
	protected $formats;
	
	/**
	 * @var string
	 */
	private $versionNumber;

	/**
	 * @var string
	 */
	private $versionOperator;
	
	public function __construct(array $data)
	{
		$this->versionOperator = '==';
		
		if (false === isset($data['protocol'])) {
			throw new \InvalidArgumentException('You must define a "protocol" attribute for each @AccessControl annotation.');
		}
		
		if (false === isset($data['version'])) {
			throw new \InvalidArgumentException('You must define a "version" attribute for each @AccessControl annotation.');
		}
		
		if (false === isset($data['formats'])) {
			throw new \InvalidArgumentException('You must define a "formats" attribute for each @AccessControl annotation.');
		}
		
		if (true === is_array($data['version'])) {
			if (false === isset($data['version']['name'])) {
				throw new \InvalidArgumentException('You must define attribute "name" in "version" parameters for each @AccessControl annotation.');
			}
			
			$this->version = strtolower(trim($data['version']['name']));
			
			if (true === isset($data['version']['operator'])) {
				$this->versionOperator = trim($data['version']['operator']);
			}
		} else {
			$this->version = strtolower(trim($data['version']));
		}
		
		$this->protocol = strtolower(trim($data['protocol']));
		$this->versionNumber = self::findVersionNumber($this->version);
		$this->formats = array_map('trim', array_map('strtolower', explode(',', $data['formats'])));
	}
	
	public function getProtocol() :string
	{
		return $this->protocol;
	}
	
	public function getVersion() :string
	{
		return $this->version;
	}
	
	public function getVersionNumber() :int
	{
		return $this->versionNumber;
	}
	
	/**
	 * @return string
	 */
	public function getVersionOperator() :string
	{
		return $this->versionOperator;
	}
	
	public function getFormats() :array
	{
		return $this->formats;
	}
	
	private static function findVersionNumber(string $versionName) :int {
		return (int) preg_replace('#[^0-9]#', '', $versionName);
	}
}
