<?php
namespace Oka\RESTRequestValidatorBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class ResponseListener
{
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
}
