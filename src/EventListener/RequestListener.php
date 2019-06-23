<?php
namespace Oka\RESTRequestValidatorBundle\EventListener;

use Oka\RESTRequestValidatorBundle\Service\ErrorResponseFactory;
use Oka\RESTRequestValidatorBundle\Util\RequestUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class RequestListener
{
	const STOP_WATCH_API_EVENT_NAME = 'oka_api.request_duration';
	
	/**
	 * @var TranslatorInterface $translator
	 */
	protected $translator;
	
	/**
	 * @var ErrorResponseFactory $errorFactory
	 */
	protected $errorFactory;
	
	/**
	 * @var LoggerInterface $logger
	 */
	protected $logger;
	
	/**
	 * @var string $environment
	 */
	protected $environment;
	
	public function __construct(TranslatorInterface $translator, ErrorResponseFactory $errorFactory, LoggerInterface $logger, $environment)
	{
		$this->translator = $translator;
		$this->errorFactory = $errorFactory;
		$this->logger = $logger;
		$this->environment = $environment;
	}
	
	/**
	 * @param FilterResponseEvent $event
	 */
	public function onKernelResponse(FilterResponseEvent $event)
	{
		$responseHeaders = $event->getResponse()->headers;
		
		// Security headers
		$responseHeaders->set('X-Content-Type-Options', 'nosniff');
		$responseHeaders->set('X-Frame-Options', 'deny');
		
		// Utils Server
		$responseHeaders->set('X-Server-Time', date('c'));
	}
	
	/**
	 * @param GetResponseForExceptionEvent $event
	 */
	public function onKernelException(GetResponseForExceptionEvent $event)
	{
		if (false === $event->isMasterRequest() || 'dev' === $this->environment) {
			return;
		}
		
		$request = $event->getRequest();
		$exception = $event->getException();
		$format = $request->attributes->has('format') ? $request->attributes->get('format') : RequestUtil::getFirstAcceptableFormat($request, 'json');
		
		if ($exception instanceof UnauthorizedHttpException) {
			$response = $this->errorFactory->createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
		} elseif($exception instanceof AuthenticationException) {
			$response = $this->errorFactory->create($exception->getMessage(), 403, null, [], 403, [], $format);
		} elseif($exception instanceof BadRequestHttpException) {
			$response = $this->errorFactory->createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
		} elseif ($exception instanceof NotFoundHttpException) {
			$response = $this->errorFactory->create($this->translator->trans('request.resource.not_found', ['%resource%' => $request->getRequestUri()], 'OkaRESTRequestValidatorBundle'), 404, null, [], 404, [], $format);
		} elseif ($exception instanceof MethodNotAllowedHttpException) {
			$response = $this->errorFactory->createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
		} elseif ($exception instanceof NotAcceptableHttpException) {
			$response = $this->errorFactory->createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
		} elseif ($exception instanceof HttpException) {
			$response = $this->errorFactory->createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
		} else {
			$response = $this->errorFactory->create($this->translator->trans('request.server_error', [], 'OkaRESTRequestValidatorBundle'), 500, null, [], 500, [], $format);
		}
		
		$event->setResponse($response);
		
		$this->logger->error(sprintf(
				'%s: %s (uncaught exception) at %s line %s',
				get_class($exception),
				$exception->getMessage(),
				$exception->getFile(),
				$exception->getLine()
		));
	}
}
