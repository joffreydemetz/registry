<?php
/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JDZ\Registry\Format;

/**
 * Interface defining a format object
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
interface FormatInterface
{
	/**
	 * Converts an object into a formatted string
	 *
	 * @param   object  $object   Data Source Object
	 * @param   array   $options  An array of options for the formatter
	 * @return  string  Formatted string
	 */
	public function objectToString($object, array $options=[]);
  
	/**
	 * Converts a formatted string into an object
	 *
	 * @param   string  $data     Formatted string
	 * @param   array   $options  An array of options for the formatter
	 * @return  object  Data Object
	 */
	public function stringToObject($data, array $options=[]);
}
