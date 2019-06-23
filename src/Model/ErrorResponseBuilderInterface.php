<?php
namespace Oka\RESTRequestValidatorBundle\Model;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
interface ErrorResponseBuilderInterface
{
	const DEFAULT_FORMATS = ['html', 'json', 'xml'];
	
	public static function getInstance() :self;
	
	/**
	 * Sets error
	 * 
	 * @param string $message
	 * @param int $code
	 * @param string $propertyPath
	 * @param array $extras
	 * @return self
	 */
	public function setError(string $message, int $code, string $propertyPath = null, array $extras = []) :self;
	
	/**
	 * Add child error
	 * 
	 * @param string $message
	 * @param int $code
	 * @param string $propertyPath
	 * @param array $extras
	 * @return self
	 */
	public function addChildError(string $message, int $code, string $propertyPath = null, array $extras = []) :self;
	
	/**
	 * @param int $httpStatusCode
	 * @return self
	 */
	public function setHttpStatusCode(int $httpStatusCode = 500) :self;
	
	/**
	 * @param array $httpHeaders
	 * @return self
	 */
	public function setHttpHeaders(array $httpHeaders = []) :self;
	
	/**
	 * @param string $format
	 * @return self
	 */
	public function setFormat(string $format) :self;
	
	/**
	 * @throws \LogicException
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function build();
}
