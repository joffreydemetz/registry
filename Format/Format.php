<?php
/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JDZ\Registry\Format;

/**
 * Registry Abstract Formatter
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
abstract class Format implements FormatInterface
{
	/**
	 * @var    array  Format instances container.
	 */
	protected static $instances = [];
  
	/**
	 * Returns a reference to a Format object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param 	string  $type  The format to load
	 * @return 	Format  Registry format handler
	 */
	public static function getInstance($type)
	{
		$type = strtolower(preg_replace('/[^A-Z0-9_]/i', '', $type));

		if ( !isset(self::$instances[$type]) ){
			$class = __NAMESPACE__ . '\\'.ucfirst($type).'Format';
      
			if ( !class_exists($class) ){
				throw new \RuntimeException('Unable to read Registry/Format class ('.$type.')');
			}
      
			self::$instances[$type] = new $class;
		}
    
		return self::$instances[$type];
	}
}
