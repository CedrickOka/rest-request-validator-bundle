<?php
namespace Oka\RESTRequestValidatorBundle\Util;

use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
final class RequestUtil
{
	/**
	 * Parse request query
	 * 
	 * @param Request $request
	 * @param string $key
	 * @param string $delimiter
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public static function parseQueryStringToArray(Request $request, string $key, string $delimiter = null, $defaultValue = null)
	{
		$value = $request->query->get($key, $defaultValue);
		
		if ($value && $delimiter !== null) {
			$value = array_map(function($value){
				return self::sanitizeQueryString($value);
			}, explode($delimiter, $value));
		}
		
		return $value;
	}
	
	/**
	 * Sanitize request query
	 * 
	 * @param string $query
	 * @return string
	 */
	public static function sanitizeQueryString(string $query)
	{
		return trim(rawurldecode($query));
	}
	
	/**
	 * Get first acceptable response format
	 *
	 * @param Request $request
	 * @return string|NULL
	 */
	public static function getFirstAcceptableFormat(Request $request, string $defaultFormat = null)
	{
		$format = null;
		$acceptableContentTypes = $request->getAcceptableContentTypes();
		
		if (false === empty($acceptableContentTypes)) {
			$format = $request->getFormat($acceptableContentTypes[0]);
		}
		
		return $format ?: $defaultFormat;
	}
	
	/**
	 * Get content from request
	 * 
	 * @param Request $request
	 * @param string $format
	 * @return mixed
	 */
	public static function getContentFromFormat(Request $request, string $format)
	{
		switch ($format) {
			case 'json':
				return json_decode($request->getContent(), true);
				
			case 'xml':
				return simplexml_load_string($request->getContent(), true);
				break;
				
			case 'form':
				return $request->request->all();
				break;
				
			default;
				return null;
		}
	}
	
	/**
	 * Get content from request
	 * 
	 * @param Request $request
	 * @return array
	 */
	public static function getContent(Request $request)
	{
		switch ($request->getContentType()) {
			case 'json':
				return json_decode($request->getContent(), true);
				
			case 'xml':
				return simplexml_load_string($request->getContent(), true);
				break;
				
			case 'form':
				return $request->request->all();
				break;
				
			default;
				return null;
		}
	}
	
	/**
	 * Get content from request
	 * 
	 * @param Request $request
	 * @return array
	 */
	public static function getContentLikeArray(Request $request) :array
	{
		switch ($request->getContentType()) {
			case 'json':
				$data = json_decode($request->getContent(), true);
				return $data ?: [];
				
			case 'form':
				return $request->request->all();
				
			default;
				return [];
		}
	}
}
