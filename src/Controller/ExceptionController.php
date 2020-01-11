<?php
namespace Oka\RESTRequestValidatorBundle\Controller;

use Oka\RESTRequestValidatorBundle\Service\ErrorResponseFactory;
use Oka\RESTRequestValidatorBundle\Util\RequestUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
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
    private $translator;
    private $errorResponseFactory;
    
    public function __construct(TranslatorInterface $translator, ErrorResponseFactory $errorResponseFactory) {
        $this->translator = $translator;
        $this->errorResponseFactory = $errorResponseFactory;
    }
    
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
				return $this->errorResponseFactory->create($exception->getMessage(), 403, null, [], 403, [], $format);
				
			case $exception->getClass() === NotFoundHttpException::class:
				return $this->errorResponseFactory->create(
					$this->translator->trans('request.resource.not_found', ['%resource%' => $request->getRequestUri()], 'OkaRESTRequestValidatorBundle'),
					$exception->getStatusCode(),
					null,
					[],
					$exception->getStatusCode(),
					[],
					$format
				);
				
			default:
				return $this->errorResponseFactory->create(
					$this->translator->trans($exception->getMessage(), [], 'OkaRESTRequestValidatorBundle'),
					$exception->getStatusCode(),
					null,
					[],
					$exception->getStatusCode(),
					[],
					$format
				);
		}
	}
}
