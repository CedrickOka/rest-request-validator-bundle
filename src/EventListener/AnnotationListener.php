<?php
namespace Oka\RESTRequestValidatorBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Oka\RESTRequestValidatorBundle\Annotation\AccessControl;
use Oka\RESTRequestValidatorBundle\Annotation\RequestContent;
use Oka\RESTRequestValidatorBundle\Service\ErrorResponseFactory;
use Oka\RESTRequestValidatorBundle\Util\RequestUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class AnnotationListener
{
	/**
	 * @var Reader $reader
	 */
	protected $reader;
	
	/**
	 * @var ValidatorInterface $validator
	 */
	protected $validator;
	
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
	 * @param Reader $reader
	 * @param ValidatorInterface $validator
	 * @param TranslatorInterface $translator
	 * @param ErrorResponseFactory $errorFactory
	 * @param LoggerInterface $logger
	 */
	public function __construct(Reader $reader, ValidatorInterface $validator, TranslatorInterface $translator, ErrorResponseFactory $errorFactory, LoggerInterface $logger)
	{
		$this->reader = $reader;
		$this->validator = $validator;
		$this->translator = $translator;
		$this->errorFactory = $errorFactory;
		$this->logger = $logger;
		
	}
	
	/**
	 * @param FilterControllerEvent $event
	 */
	public function onController(FilterControllerEvent $event)
	{
		if (false === $event->isMasterRequest() || false === is_array($controller = $event->getController())) {
			return;
		}
		
		$listeners = ['onAccessControlAnnotation', 'onRequestContentAnnotation'];
		$reflMethod = new \ReflectionMethod($controller[0], $controller[1]);
		
		foreach ($listeners as $listener) {
			$this->$listener($event, $reflMethod);
			
			if (true === $event->isPropagationStopped()) {
				return;
			}
		}
	}
	
	/**
	 * @param FilterControllerEvent $event
	 * @param \ReflectionMethod $reflMethod
	 */
	private function onAccessControlAnnotation(FilterControllerEvent $event, \ReflectionMethod $reflMethod)
	{
		/** @var \Oka\RESTRequestValidatorBundle\Annotation\AccessControl $annotation */
		if (!$annotation = $this->reader->getMethodAnnotation($reflMethod, AccessControl::class)) {
			return;
		}
		
		$request = $event->getRequest();
		$acceptablesContentTypes = $request->getAcceptableContentTypes();
		
		if (false === empty($acceptablesContentTypes)) {
			foreach ($acceptablesContentTypes as $contentType) {
				$format = $request->getFormat($contentType);
				
				if (true === in_array($format, $annotation->getFormats(), true)) {
					$request->attributes->set('format', $format);
					break;
				}
			}
			
			if (false === $request->attributes->has('format') && true === in_array('*/*', $acceptablesContentTypes, true)) {
				$request->attributes->set('format', $annotation->getFormats()[0]);
			}
		} else {
			$request->attributes->set('format', $annotation->getFormats()[0]);
		}
		
		$response = null;
		$version = $request->attributes->get('version');
		$protocol = $request->attributes->get('protocol');
		$format = RequestUtil::getFirstAcceptableFormat($request, $annotation->getFormats()[0]);
		
		switch (false) {
			case version_compare($version, $annotation->getVersion(), $annotation->getVersionOperator()):
				$response = $this->errorFactory->create($this->translator->trans('request.version.not_acceptable', ['%version%' => $version], 'OkaRESTRequestValidatorBundle'), 405, null, [], 405, [], $format);
				break;
				
			case strtolower($protocol) === $annotation->getProtocol():
				$response = $this->errorFactory->create($this->translator->trans('request.protocol.not_acceptable', ['%version%' => $protocol], 'OkaRESTRequestValidatorBundle'), 405, null, [], 405, [], $format);
				break;
				
			case $request->attributes->has('format'):
				$response = $this->errorFactory->create($this->translator->trans('request.format.unacceptable', ['%formats%' => implode(', ', $acceptablesContentTypes)], 'OkaRESTRequestValidatorBundle'), 406, null, [], 406, [], $format);
				break;
		}
		
		if (null === $response) {
			$version = $request->attributes->set('versionNumber', $annotation->getVersionNumber());
		} else {
			$event->setController(function() use ($response) {
				return $response;
			});
			$event->stopPropagation();
		}
	}
	
	/**
	 * @param FilterControllerEvent $event
	 * @param \ReflectionMethod $reflMethod
	 */
	private function onRequestContentAnnotation(FilterControllerEvent $event, \ReflectionMethod $reflMethod)
	{
		/** @var \Oka\RESTRequestValidatorBundle\Annotation\RequestContent $annotation */
		if (!$annotation = $this->reader->getMethodAnnotation($reflMethod, RequestContent::class)) {
			return;
		}
		
		$request = $event->getRequest();
		$responseFormat = $request->attributes->has('format') ? $request->attributes->get('format') : RequestUtil::getFirstAcceptableFormat($request, 'json');
		
		if (true === $request->isMethodCacheable()) {
			$requestContent = $request->query->all();
		} else {
			// Validate request content types
			if (false === empty($annotation->getFormats())) {
				if (false === in_array($request->getContentType(), $annotation->getFormats())) {
					$event->setController(function(Request $request) use ($annotation, $responseFormat) {
						return $this->errorFactory->create($this->translator->trans('request.format.unsupported', [], 'OkaRESTRequestValidatorBundle'), 415, null, [], 415, [], $responseFormat);
					});
					$event->stopPropagation();
					return;
				}
				
				foreach ($annotation->getFormats() as $format) {
					$requestContent = RequestUtil::getContentFromFormat($request, $format);
				}
			} else {
				$requestContent = RequestUtil::getContent($request);
			}
		}
		
		if (null === $requestContent || false === $requestContent) {
			$event->setController(function(Request $request) use ($annotation, $responseFormat) {
				$message = $this->translator->trans($annotation->getValidationErrorMessage(), $annotation->getTranslationParameters(), $annotation->getTranslationDomain());
				
				return $this->errorFactory->create($message, 400, null, [], 400, [], $responseFormat);
			});
			$event->stopPropagation();
			return;
		}
		
		$errors = null;
		$validationHasFailed = false;
		$controller = $event->getController();
		
		// Input validation
		if (true === $annotation->isEnableValidation()) {
			if (true === empty($requestContent)) {
				$validationHasFailed = !$annotation->isCanBeEmpty();
			} else {
				$constraints = $annotation->getConstraints();
				$reflectionMethod = new \ReflectionMethod($controller[0], $constraints);
				
				if (false === $reflectionMethod->isStatic()) {
					throw new \InvalidArgumentException(sprintf('Invalid option(s) passed to @%s: Constraints method "%s" is not static.', get_class($annotation), $constraints));
				}
				
				if ($reflectionMethod->getNumberOfParameters() > 0) {
					throw new \InvalidArgumentException(sprintf('Invalid option(s) passed to @%s: Constraints method "%s" must not have of arguments.', get_class($annotation), $constraints));
				}
				
				$reflectionMethod->setAccessible(true);
				$errors = $this->validator->validate($requestContent, $reflectionMethod->invoke(null));
				$validationHasFailed = $errors->count() > 0;
			}
		}
		
		if (false === $validationHasFailed) {
			$request->attributes->set('requestContent', $requestContent);
		} else {
			$event->setController(function(Request $request) use ($annotation, $errors, $responseFormat) {
				$message = $this->translator->trans($annotation->getValidationErrorMessage(), $annotation->getTranslationParameters(), $annotation->getTranslationDomain());
				
				if (null === $errors) {
					$response = $this->errorFactory->create($message, 400, null, [], 400, [], $responseFormat);
				} else {
					$response = $this->errorFactory->createFromConstraintViolationList($errors, $message, 400, null, [], 400, [], $responseFormat);
				}
				
				return $response;
			});
			$event->stopPropagation();
		}
	}
}
