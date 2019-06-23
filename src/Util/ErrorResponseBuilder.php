<?php
namespace Oka\RESTRequestValidatorBundle\Util;

use Oka\RESTRequestValidatorBundle\Model\ErrorResponseBuilderInterface;
use Oka\RESTRequestValidatorBundle\Serializer\Encoder\HtmlEncoder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class ErrorResponseBuilder implements ErrorResponseBuilderInterface
{
	/**
	 * @var array $error
	 */
	protected $error;
	
	/**
	 * @var array $childErrors
	 */
	protected $childErrors;
	
	/**
	 * @var int $httpStatusCode
	 */
	protected $httpStatusCode;
	
	/**
	 * @var array $httpHeaders
	 */
	protected $httpHeaders;
	
	/**
	 * @var string $format
	 */
	protected $format;
	
	/**
	 * Constructor.
	 */
	protected function __construct()
	{
		$this->format = 'json';
		$this->childErrors = [];
		$this->httpHeaders = [];
		$this->httpStatusCode = 500;
	}
	
	/**
	 * @return ErrorResponseBuilderInterface
	 */
	public static function getInstance() :ErrorResponseBuilderInterface
	{
		return new self();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\RESTRequestValidatorBundle\Model\ErrorResponseBuilderInterface::setError()
	 */
	public function setError(string $message, int $code, string $propertyPath = null, array $extras = []) :ErrorResponseBuilderInterface
	{
		$this->error = $this->createError($message, $code, $propertyPath, $extras);
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\RESTRequestValidatorBundle\Model\ErrorResponseBuilderInterface::addChildError()
	 */
	public function addChildError(string $message, int $code, string $propertyPath = null, array $extras = []) :ErrorResponseBuilderInterface
	{
		$this->childErrors[] = $this->createError($message, $code, $propertyPath, $extras);		
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\RESTRequestValidatorBundle\Model\ErrorResponseBuilderInterface::setHttpStatusCode()
	 */
	public function setHttpStatusCode(int $httpStatusCode = 500) :ErrorResponseBuilderInterface
	{
		$this->httpStatusCode = $httpStatusCode;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\RESTRequestValidatorBundle\Model\ErrorResponseBuilderInterface::setHttpHeaders()
	 */
	public function setHttpHeaders(array $httpHeaders = []) :ErrorResponseBuilderInterface
	{
		$this->httpHeaders = $httpHeaders;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\RESTRequestValidatorBundle\Model\ErrorResponseBuilderInterface::setFormat()
	 */
	public function setFormat(string $format) :ErrorResponseBuilderInterface
	{
		if (!in_array($format, self::DEFAULT_FORMATS)) {
			throw new \UnexpectedValueException(sprintf('The format must be a value between "%s", "%s" given.', implode(', ', self::DEFAULT_FORMATS), $format));
		}
		
		$this->format = $format;
		return $this;
	}
	
	/**
	 * @throws \LogicException
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function build()
	{
		if (!is_array($this->error)) {
			throw new \LogicException('ErrorResponseBuilder::$error property must be defined.');
		}
		
		$data = ['error' => $this->error];
		
		if (!empty($this->childErrors)) {
			$data['errors'] = $this->childErrors;
		}
		
		if ($this->format === 'html' || $this->format === 'xml') {
			$this->httpHeaders['Content-Type'] = $this->format === 'html' ? 'text/html' : 'application/xml';
			$serializer = new Serializer([new ObjectNormalizer()], [new HtmlEncoder(), new XmlEncoder()]);
			$response = new Response($serializer->encode($data, $this->format), $this->httpStatusCode, $this->httpHeaders);
		} else {
			$response = new JsonResponse($data, $this->httpStatusCode, $this->httpHeaders);
		}
		
		return $response;
	}
	
	/**
	 * Create an error
	 * 
	 * @param string $message
	 * @param int $code
	 * @param string $propertyPath
	 * @param array $extras
	 * @return array
	 */
	protected function createError(string $message, int $code, string $propertyPath = null, array $extras = []) :array
	{
		$item = ['message' => $message, 'code' => $code];
		
		if (null !== $propertyPath) {
			$item['propertyPath'] = $propertyPath;
		}
		
		if (false === empty($extras)) {
			$item['extras'] = $extras;
		}
		
		return $item;
	}
}
