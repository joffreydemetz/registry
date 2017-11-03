<?php
/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JDZ\Registry\Format;
  
use Symfony\Component\Yaml\Parser as SymfonyYamlParser;
use Symfony\Component\Yaml\Dumper as SymfonyYamlDumper;

/**
 * Yaml format adapter for the Registry Format Class
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class YamlFormat extends Format
{
	/**
	 * The YAML parser class
	 *
	 * @var    \Symfony\Component\Yaml\Parser
	 */
	private $parser;
  
	/**
	 * The YAML dumper class
	 *
	 * @var    \Symfony\Component\Yaml\Dumper
	 */
	private $dumper;
  
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->parser = new SymfonyYamlParser();
		$this->dumper = new SymfonyYamlDumper();
	}
  
	/**
	 * Converts an object into a YAML formatted string
	 * 
	 * @param   object  $object   Data Source Object
	 * @param   array   $options  An array of options for the formatter
	 * 
	 * @return  string  YAML formatted string
	 */
	public function objectToString($object, array $options=[])
	{
		$array = json_decode(json_encode($object), true);
		return $this->dumper->dump($array, 2, 0);
	}
  
	/**
	 * Parse a YAML formatted string and convert it into an object
	 * 
	 * @param   string  $data     YAML formatted string to convert
	 * @param   array   $options  Options used by the formatter
	 *
	 * @return  object  Data object
	 */
	public function stringToObject($data, array $options=[])
	{
		$array = $this->parser->parse(trim($data));
		return (object)json_decode(json_encode($array));
	}
}
