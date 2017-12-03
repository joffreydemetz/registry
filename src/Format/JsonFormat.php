<?php
/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JDZ\Registry\Format;

/**
 * JSON format adapter for the Registry Format Class
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class JsonFormat extends Format
{
  /**
   * Converts an object into a JSON formatted string
   *
   * @param   object  $object   Data source object
   * @param   array   $options  Options used by the formatter
   * @return   string  JSON formatted string
   */
  public function objectToString($object, array $options=[])
  {
    return json_encode($object);
  }

  /**
   * Parse a JSON formatted string and convert it into an object
   *
   * If not JSON tries YML.
   *
   * @param   string  $data     JSON formatted string to convert
   * @param   array   $options  Options used by the formatter
   * @return   object   Data object
   */
  public function stringToObject($data, array $options=[])
  {
    $data = trim($data);
    if ( substr($data, 0, 1) !== '{' && substr($data, -1, 1) !== '}' ){
      return Format::getInstance('Yaml')->stringToObject($data, $options);
    }
    
    $decoded = json_decode($data);
    
    if ( $decoded === null ){
      throw new \RuntimeException(sprintf('Error decoding JSON data: %s', json_last_error_msg()));
    }
    
    return (object)$decoded;
  }
}
