<?php
/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JDZ\Registry\Format;

/**
 * XML format adapter for the Registry Format Class
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class XmlFormat extends Format
{
	/**
	 * Converts an object into an XML formatted string
	 *
	 * @param 	object  $object   Data source object
	 * @param 	array   $options  Options used by the formatter
	 * @return 	string  XML formatted string
	 */
	public function objectToString($object, array $options=[])
	{
		$rootName = (isset($options['name'])) ? $options['name'] : 'registry';
		$nodeName = (isset($options['nodeName'])) ? $options['nodeName'] : 'node';
    
		$root = simplexml_load_string('<' . $rootName . ' />');

		$this->getXmlChildren($root, $object, $nodeName);

		return $root->asXML();
	}

	/**
	 * Parse a XML formatted string and convert it into an object
	 *
	 * @param 	string  $data     XML formatted string to convert
	 * @param 	array   $options  Options used by the formatter
	 * @return 	object  Data object
	 */
	public function stringToObject($data, array $options=[])
	{
		$obj = new \stdClass;

		$xml = simplexml_load_string($data);

		foreach($xml->children() as $node){
			$obj->$node['name'] = $this->getValueFromNode($node);
		}

		return $obj;
	}

	/**
	 * Get a PHP native value for a \SimpleXMLElement object. -- called recursively
	 *
	 * @param 	object  $node  \SimpleXMLElement object for which to get the native value
	 * @return 	mixed  Native value of the \SimpleXMLElement object
	 */
	protected function getValueFromNode($node)
	{
		switch($node['type']){
			case 'integer':
				$value = (string) $node;
				return (int) $value;
				break;
			case 'string':
				return (string) $node;
				break;
			case 'boolean':
				$value = (string) $node;
				return (bool) $value;
				break;
			case 'double':
				$value = (string) $node;
				return (float) $value;
				break;
			case 'array':
				$value = [];
				foreach($node->children() as $child){
					$value[(string)$child['name']] = $this->getValueFromNode($child);
				}
				break;
			default:
				$value = new \stdClass;
				foreach($node->children() as $child){
					$value->{$child['name']} = $this->getValueFromNode($child);
				}
				break;
		}

		return $value;
	}

	/**
	 * Build a level of the XML string -- called recursively
	 *
	 * @param 	\SimpleXMLElement   &$node     \SimpleXMLElement object to attach children
	 * @param 	object              $var       Object that represents a node of the XML document
	 * @param 	string              $nodeName  The name to use for node elements
	 * @return 	void
	 */
	protected function getXmlChildren(&$node, $var, $nodeName)
	{
		foreach((array)$var as $k => $v){
			if ( is_scalar($v) ){
				$n = $node->addChild($nodeName, $v);
				$n->addAttribute('name', $k);
				$n->addAttribute('type', gettype($v));
			}
			else {
				$n = $node->addChild($nodeName);
				$n->addAttribute('name', $k);
				$n->addAttribute('type', gettype($v));

				$this->getXmlChildren($n, $v, $nodeName);
			}
		}
	}
}
