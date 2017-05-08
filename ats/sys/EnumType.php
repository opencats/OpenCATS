<?php
// https://www.getinstance.com/a-php-enum-class/
abstract class EnumType {
	private static $enumCache = array();
    private $name;
	private static $fields = array();
	protected $attrs = null;
 
    public abstract function getFields();
	
    final function __construct( $name ) { 
		$className = get_class($this);
		if (!isset(EnumType::$fields[$className])){
			EnumType::$fields[$className] = $this->getFields();
		}
        if (!array_key_exists( $name, EnumType::$fields[$className])) { 
            throw new \Exception("Unknown EnumType::name reference in $className : $name");
        }   
        $this->name = $name;
		$this->attrs =  EnumType::$fields[$className][$name];
    } 	
 
    public static function __callStatic( $func, $args ) {
		$className = get_called_class();		
		if (!isset(EnumType::$enumCache[$className][$func])){
			EnumType::$enumCache[$className][$func]=new static( $func ); 
		} 
		return EnumType::$enumCache[$className][$func];
    }
 
    public function name() {
        return $this->name;
    }   
 
    public function __toString() {
        return $this->name();
    }
	
	public function getEnumCache(){
		return EnumType::$enumCache;
	}
	
	public function getAttr($atrName){
		if (isset($this->attrs[$atrName])){
			return $this->attrs[$atrName];
		}
		return null;
	}
	
}

?>