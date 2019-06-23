<?php
namespace Oka\RESTRequestValidatorBundle\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class HtmlEncoder implements EncoderInterface
{
	const FORMAT = 'html';
	const HTML_LIST_ROOT = 'ul';
	const HTML_LIST_ITEM = 'li';
	const HTML_TAG_LIST_ROOT = '<ul>';
	const HTML_END_TAG_LIST_ROOT = '</ul>';
	const HTML_TAG_LIST_ITEM = '<li>';
	const HTML_END_TAG_LIST_ITEM = '</li>';
	
	/**
	 * {@inheritdoc}
	 * @see \Symfony\Component\Serializer\Encoder\EncoderInterface::supportsEncoding()
	 */
	public function supportsEncoding($format)
	{
		return $format === HtmlEncoder::FORMAT;
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Symfony\Component\Serializer\Encoder\EncoderInterface::encode()
	 */
	public function encode($data, $format, array $context = [])
	{
		if (false === is_string($data) && false === is_array($data)) {
			throw new UnexpectedValueException(sprintf('The HtmlEncoder data must be a array or string or object implementing __toString(), "%s" given.', gettype($data)));
		}
		
		$encodedHtml = '<html>';
		$encodedHtml .= '<body>';
		$encodedHtml .= $this->encodeBlock($data);
		$encodedHtml .= '</body>';
		$encodedHtml .= '</html>';
		
		return $encodedHtml;
	}
	
	/**
	 * @param mixed $data
	 * @throws UnexpectedValueException
	 * @return string
	 */
	protected function encodeBlock($data)
	{
		$block = self::HTML_TAG_LIST_ROOT;
		
		if (is_string($data) || is_numeric($data)) {
			$block .= self::HTML_TAG_LIST_ITEM . ((string) $data) . self::HTML_END_TAG_LIST_ITEM;
			
		} else {
			foreach ($data as $key => $value) {
				if (is_array($value)) {
					$block .= self::HTML_TAG_LIST_ITEM . ((string) $key) . ' => ' . self::HTML_END_TAG_LIST_ITEM;
					$block .= self::HTML_TAG_LIST_ITEM;
					$block .= $this->encodeBlock($value);
					$block .= self::HTML_END_TAG_LIST_ITEM;
				} else {
					if (false === is_string($value) && false === is_numeric($value) && false === (is_object($value) && method_exists($value, '__toString'))) {
						throw new UnexpectedValueException(sprintf('The HtmlEncoder $value must be number or string or object implementing __toString(), "%s" given.', gettype($value)));
					}
					
					$block .= self::HTML_TAG_LIST_ITEM . ((string) $key) . ' => ' . ((string) $value) . self::HTML_END_TAG_LIST_ITEM;
				}
			}
		}
		
		$block .= self::HTML_END_TAG_LIST_ROOT;
		
		return $block;
	}
}
