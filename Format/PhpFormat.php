<?php
/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JDZ\Registry\Format;

/**
 * PHP format adapter for the Registry Format Class
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class PhpFormat extends Format
{
	/**
	 * Converts an object into a php class string.
	 * - NOTE: Only one depth level is supported.
	 *
	 * @param 	object  $object  Data Source Object
	 * @param 	array   $params  Parameters used by the formatter
	 * @return 	string  Config class formatted string
	 */
	public function objectToString($object, array $options=[])
	{
		$vars = '';
		foreach(get_object_vars($object) as $k => $v){
			if ( is_scalar($v) ){
				$vars .= "\tpublic $" . $k . " = '" . addcslashes($v, '\\\'') . "';\n";
			}
			elseif ( is_array($v) || is_object($v) ){
				$vars .= "\tpublic $" . $k . " = " . $this->getArrayString((array) $v) . ";\n";
			}
		}
    
		$str = "<?php\nclass " . $options['class'] . " {\n";
		$str .= $vars;
		$str .= "}";

		if ( !isset($options['closingtag']) || $options['closingtag'] !== false ){
			$str .= "\n?>";
		}

		return $str;
	}

	/**
	 * Parse a PHP class formatted string and convert it into an object
	 *
	 * @param 	string  $data     PHP Class formatted string to convert
	 * @param 	array   $options  Options used by the formatter
	 * @return 	object  Data object
	 */
	public function stringToObject($data, array $options=[])
	{
		return true;
	}

	/**
	 * Get an array as an exported string.
	 *
	 * @param 	array  $a  The array to get as a string.
	 * @return 	array
	 */
	protected function getArrayString($a)
	{
		$s = '[';
		$i = 0;
		foreach ($a as $k => $v){
			$s .= ($i) ? ', ' : '';
			$s .= '"'.$k.'" => ';
			if ( is_array($v) || is_object($v) ){
				$s .= $this->getArrayString((array)$v);
			}
			else {
				$s .= '"'.addslashes($v).'"';
			}
			$i++;
		}
		$s .= ']';
		return $s;
	}
}
