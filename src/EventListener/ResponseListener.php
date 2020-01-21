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
    private $config;
    
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }
    
	/**
	 * @param FilterResponseEvent $event
	 */
	public function onKernelResponse(FilterResponseEvent $event)
	{
		$responseHeaders = $event->getResponse()->headers;
		
		// Security headers
		if (null !== $this->config['content_type_options']) {
		    $responseHeaders->set('X-Content-Type-Options', $this->config['content_type_options']);
		}
		
		if (null !== $this->config['frame_options']) {
		    $responseHeaders->set('X-Frame-Options', $this->config['frame_options']);
		}
		
		// Utils Server
		if (true === $this->config['server_time']) {
		    $responseHeaders->set('X-Server-Time', date('c'));
		}
	}
}
