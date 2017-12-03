<?php
/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JDZ\Registry;

use JDZ\Registry\Format\Format;
use JDZ\Helpers\ArrayHelper;
use JDZ\Filesystem\File;
use Exception;

/**
 * Registry Object
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Registry implements \JsonSerializable 
{
  /**
   * Registry Object
   *
   * @var    object
   */
  protected $data;

  /**
   * Registry instances container
   * 
   * @var    array  
   */
  protected static $instances = [];
  
  /**
   * Returns a reference to a global Registry object, only creating it
   * if it doesn't already exist.
   *
   * This method must be invoked as:
   * <pre>$registry = Registry::getInstance($id);</pre>
   *
   * @param   string  $id  An ID for the registry instance
   * @return   object  The Registry object.
   */
  public static function getInstance($id)
  {
    if ( empty(self::$instances[$id]) ){
      self::$instances[$id] = new Registry;
    }

    return self::$instances[$id];
  }

  /**
   * Constructor
   *
   * @param   mixed  $data  The data to bind to the new Registry object
   */
  public function __construct($data=null)
  {
    $this->data = new \stdClass;

    if ( $data instanceof Registry ){
      $this->merge($data);
    }
    elseif ( is_array($data) || is_object($data) ){
      $this->bindData($this->data, $data);
    }
    elseif ( !empty($data) && is_string($data) ){
      $this->loadString($data);
    }
  }
  
  /**
   * Magic function to clone the registry object
   * 
   * @return   Registry
   */
  public function __clone()
  {
    $this->data = unserialize(serialize($this->data));
  }

  /**
   * Magic method to get the string representation of this object
   * 
   * @return   string
   */
  public function __toString()
  {
    return $this->toString();
  }
  
  /**
   * Implementation for the JsonSerializable interface.
   * Allows us to pass Registry objects to json_encode.
   *
   * @return  object
   * @see     JsonSerializable::jsonSerialize()
   */
  public function jsonSerialize()
  {
    return $this->data;
  }
  
  /**
   * Sets a default value if not already assigned
   *
   * @param   string  $key      The name of the parameter
   * @param   string  $default  An optional value for the parameter
   * @return   string  The value set, or the default if the value was not previously set (or null)
   */
  public function def($key, $default='')
  {
    $value = $this->get($key, (string) $default);
    $this->set($key, $value);
    return $value;
  }
  
  /**
   * Set a registry value
   *
   * @param   string  $path   Registry Path (e.g. joomla.content.showauthor)
   * @param   mixed   $value  Value of entry
   * @return   mixed  The value of the that has been set
   */
  public function set($path, $value)
  {
    $old = null;
    if ( $nodes = explode('.', $path) ){
      $node = $this->data;

      for($i=0, $n=count($nodes)-1; $i<$n; $i++){
        if ( !isset($node->{$nodes[$i]}) && ($i != $n) ){
          $node->{$nodes[$i]} = new \stdClass;
        }
        
        $node = $node->{$nodes[$i]};
      }
      
      if ( isset($node->{$nodes[$i]}) ){
        $old = $node->{$nodes[$i]};
      }
      $node->{$nodes[$i]} = $value;
    }
    return $old;
  }

  /**
   * Get a registry value.
   *
   * @param   string  $path     Registry path (e.g. joomla.content.showauthor)
   * @param   mixed   $default  Optional default value, returned if the internal value is null
   * @return   mixed  Value of entry or null
   */
  public function get($path, $default=null)
  {
    $result = $default;

    if ( !strpos($path, '.') ){
      return (isset($this->data->$path) && $this->data->$path !== null && $this->data->$path !== '') ? $this->data->$path : $default;
    }

    $nodes = explode('.', $path);

    $node  = $this->data;
    $found = false;
    
    // Traverse the registry to find the correct node for the result
    foreach ($nodes as $n){
      if ( isset($node->$n) ){
        $node = $node->$n;
        $found = true;
      }
      else {
        $found = false;
        break;
      }
    }
    
    if ( $found && $node !== null && $node !== '' ){
      $result = $node;
    }
    
    return $result;
  }
  
  /**
   * Merge a Registry object into this one
   *
   * @param   Registry  &$source  Source Registry object to merge
   * @return   boolean  True on success
   */
  public function merge(&$source)
  {
    if ( $source instanceof Registry ){
      // Load the variables into the registry's default namespace
      foreach($source->toArray() as $k => $v){
        if ( $v !== null && $v !== '' ){
          $this->data->{$k} = $v;
        }
      }
      return true;
    }
    return false;
  }
  
  /**
   * Check if a registry path exists
   *
   * @param   string  $path  Registry path (e.g. joomla.content.showauthor)
   * @return   boolean
   */
  public function exists($path)
  {
    if ( empty($path) ){
      return false;
    }
    
    if ( $nodes = explode('.', $path) ){
      $node = $this->data;

      for($i=0, $n=count($nodes); $i<$n; $i++){
        if ( isset($node->{$nodes[$i]}) ){
          $node = $node->{$nodes[$i]};
        }
        else {
          break;
        }

        if ( $i + 1 == $n ){
          return true;
        }
      }
    }

    return false;
  }
  
  /**
   * Recursively bind data to a parent object
   *
   * @param   object  &$parent  The parent object on which to attach the data values
   * @param   mixed   $data     An array or object of data to bind to the parent object
   * @return   void
   */
  protected function bindData(&$parent, $data)
  {
    if ( is_object($data) ){
      $data = get_object_vars($data);
    }
    else {
      $data = (array) $data;
    }

    foreach($data as $k => $v){
      if ( (is_array($v) && ArrayHelper::isAssociative($v)) || is_object($v) ){
        $parent->$k = new \stdClass;
        $this->bindData($parent->$k, $v);
      }
      else {
        $parent->$k = $v;
      }
    }
  }

  /**
   * Load a associative array of values into the default namespace
   *
   * @param   array  $array  Associative array of value to load
   * @return   boolean  True on success
   */
  public function loadArray($array)
  {
    $this->bindData($this->data, $array);
    return true;
  }

  /**
   * Load the public variables of the object into the default namespace.
   *
   * @param   object  $object  The object holding the publics to load
   * @return   boolean  True on success
   */
  public function loadObject($object)
  {
    $this->bindData($this->data, $object);
    return true;
  }
  
  /**
   * Load the contents of a file into the registry
   *
   * @param   string  $file     Path to file to load
   * @param   string  $format   Format of the file [optional: defaults to Json]
   * @param   mixed   $options  Options used by the formatter
   * @return   boolean  True on success
   */
  public function loadFile($file, $format='Json', array $options=[])
  {
    try {
      $data = File::read($file);
    }
    catch(Exception $e){
      $data = '';
    }
    
    return $this->loadString($data, $format, $options);
  }
  
  /**
   * Load a string into the registry
   *
   * @param   string  $data     String to load into the registry
   * @param   string  $format   Format of the string
   * @param   mixed   $options  Options used by the formatter
   * @return   boolean  True on success
   */
  public function loadString($data, $format='Json', array $options=[])
  {
    $handler = Format::getInstance($format);
    
    $obj = $handler->stringToObject($data, $options);
    $this->loadObject($obj);

    return true;
  }

  /**
   * Transforms a namespace to an array
   * @return   array  An associative array holding the namespace data
   */
  public function toArray()
  {
    return (array) $this->asArray($this->data);
  }

  /**
   * Transforms a namespace to an object
   * @return   object   An an object holding the namespace data
   */
  public function toObject()
  {
    return $this->data;
  }

  /**
   * Get a namespace in a given string format
   *
   * @param   string  $format   Format to return the string in
   * @param   mixed   $options  Parameters used by the formatter, see formatters for more info
   * @return   string   Namespace in string format
   */
  public function toString($format='Json', array $options=[])
  {
    $handler = Format::getInstance($format);
    return $handler->objectToString($this->data, $options);
  }

  /**
   * Recursively convert an object of data to an array.
   *
   * @param   object  $data  An object of data to return as an array.
   * @return   array  Array representation of the input object.
   */
  protected function asArray($data)
  {
    $array = [];
    
    foreach(get_object_vars((object)$data) as $k => $v){
      if ( is_object($v) ){
        $array[$k] = $this->asArray($v);
      }
      else {
        $array[$k] = $v;
      }
    }
    
    return $array;
  }
}
