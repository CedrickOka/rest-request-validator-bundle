<?php
namespace Oka\RESTRequestValidatorBundle\Controller;

use Oka\RESTRequestValidatorBundle\Service\ErrorResponseFactory;
use Oka\RESTRequestValidatorBundle\Util\RequestUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
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
		/** @var \Symfony\Component\Translation\TranslatorInterface $translator */
		$translator = $this->get('translator');
		/** @var \Oka\RESTRequestValidatorBundle\Service\ErrorResponseFactory $errorResponseFactory */
		$errorResponseFactory = $this->get('oka_rest_request_validator.error_response.factory');
		$format = $request->attributes->get('format') ?? RequestUtil::getFirstAcceptableFormat($request, 'json');
		
		if ($exception instanceof UnauthorizedHttpException) {
			$response = $errorResponseFactory->createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
		} elseif($exception instanceof AuthenticationException) {
			$response = $errorResponseFactory->create($exception->getMessage(), 403, null, [], 403, [], $format);
		} elseif($exception instanceof BadRequestHttpException) {
			$response = $errorResponseFactory->createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
		} elseif ($exception instanceof NotFoundHttpException) {
			$response = $errorResponseFactory->create($translator->trans('request.resource.not_found', ['%resource%' => $request->getRequestUri()], 'OkaRESTRequestValidatorBundle'), 404, null, [], 404, [], $format);
		} elseif ($exception instanceof MethodNotAllowedHttpException) {
			$response = $errorResponseFactory->createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
		} elseif ($exception instanceof NotAcceptableHttpException) {
			$response = $errorResponseFactory->createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
		} elseif ($exception instanceof HttpException) {
			$response = $errorResponseFactory->createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
		} else {
			$response = $errorResponseFactory->create($translator->trans('request.server_error', [], 'OkaRESTRequestValidatorBundle'), 500, null, [], 500, [], $format);
		}
		
		return $response;
	}
	
	public static function getSubscribedServices() {
		return array_merge(parent::getSubscribedServices(), [
				'translator' => '?'.TranslatorInterface::class,
				'oka_rest_request_validator.error_response.factory' => '?'.ErrorResponseFactory::class
		]);
	}
}
