<?php
namespace Oka\RESTRequestValidatorBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as BaseExceptionListener;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class ExceptionListener extends BaseExceptionListener
{
	/**
	 * @param GetResponseForExceptionEvent $event
	 */
	public function onKernelException(GetResponseForExceptionEvent $event)
	{
		if (true === $this->debug) {
			return;
		}
		
		parent::onKernelException($event);
	}
}
