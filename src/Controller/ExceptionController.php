<?php
namespace Oka\RESTRequestValidatorBundle\Controller;

use Oka\RESTRequestValidatorBundle\Service\ErrorResponseFactory;
use Oka\RESTRequestValidatorBundle\Util\RequestUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class ExceptionController extends AbstractController
{
	/**
	 * Converts an Exception to a Response.
	 *
	 * A "showException" request parameter can be used to force display of an error page (when set to false) or
	 * the exception page (when true). If it is not present, the "debug" value passed into the constructor will
	 * be used.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \InvalidArgumentException When the exception template does not exist
	 */
	public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null)
	{
		$format = $request->attributes->get('format') ?? RequestUtil::getFirstAcceptableFormat($request, 'json');
		
		switch (true) {
			case $exception->getClass() === AuthenticationException::class:
				return $this->get('oka_rest_request_validator.error_response.factory')->create($exception->getMessage(), 403, null, [], 403, [], $format);
				
			case $exception->getClass() === NotFoundHttpException::class:
				return $this->get('oka_rest_request_validator.error_response.factory')->create(
					$this->get('translator')->trans('request.resource.not_found', ['%resource%' => $request->getRequestUri()], 'OkaRESTRequestValidatorBundle'),
					404,
					null,
					[],
					404,
					[],
					$format
				);
				
			case $exception->getClass() === HttpException::class:
				return $this->get('oka_rest_request_validator.error_response.factory')->createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
				
			default:
				return $this->get('oka_rest_request_validator.error_response.factory')->create(
					$this->get('translator')->trans('request.server_error', [], 'OkaRESTRequestValidatorBundle'),
					$exception->getStatusCode(),
					null,
					[],
					$exception->getStatusCode(),
					[],
					$format
				);
		}
	}
	
	public static function getSubscribedServices() {
		return array_merge(parent::getSubscribedServices(), [
				'translator' => '?'.TranslatorInterface::class,
				'oka_rest_request_validator.error_response.factory' => '?'.ErrorResponseFactory::class
		]);
	}
}
