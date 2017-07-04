<?php
// https://www.getinstance.com/a-php-enum-class/
abstract class EnumType {
	
	private static $fields;//not used
	private static $values;//not used
	private $name;
	private $attrs = null;
 	
    protected function __construct( $name ) {
        if (!isset(static::$fields[$name])) {
        	$className = get_class($this);
            throw new \Exception('Unknown '.$className.'::'.$name.' reference in EnumType constructor ('.$className.' not initialized?).');
        }   
        $this->name = $name;
		$this->attrs =  static::$fields[$name];
		if ($this->llAttr==null){
			$this->llAttr=$this->llAttr();
		}
		/*vd(array(
				'$this->name'=>$name,
				'$this->attrs'=>$this->attrs
		));*/
    } 	
    
    protected function addAttr($name,$value){
    	if (isset($this->attrs[$name])){
    		throw new \Exception('Canot overload attr with name:'.$name.' - already defined.');
    	}
    	$this->attrs[$name]=$value;
    }
 
    public static function __callStatic( $name, $args ) {
		if (!isset(static::$values[$name])){
			static::$values[$name]=new static( $name ); 
		} 
		/*vd(array(
				'$calledClassName'=>get_called_class(),
				'trace'=>debug_backtrace(),
				'static::$values[name]->name()'=>static::$values[$name]->name()
		));*/
		return static::$values[$name];
    }
    
    public static function hasName($name){
    	return (isset(static::$fields[$name]));
    }
    
    public static function initValues(){
    	$result = array();
    	$className = get_called_class();
    	foreach(static::$fields as $k=>$v){
    		call_user_func($className.'::'.$k);
    	}
    }
 
    public function name() {
        return $this->name;
    }   
 
    public function __toString() {
        return $this->name();
    }
	
	public function getAttr($atrName){
		if ('name'==$atrName) return $this->name();
		//lazy load
		$result = (isset($this->attrs[$atrName]))?$this->attrs[$atrName]:null;
		if (array_key_exists($atrName,$this->llAttr['attr']) && !is_array($result)){
			$nn=$this->name().ucfirst($atrName);
			$file = $this->llAttr['path'].$nn.'.php';
			/*vd(array(
					'$nn'=>$nn,
					'$file'=>$file ,
			));*/
			if (file_exists($file)){
		
				include($file);
				/*vd(array(
						'$nn'=>$nn,
						'$$nn'=>$$nn,
				));*/
				$va = $$nn;
				$this->attrs[$atrName]=$va;
				return $va;
			}
		}
		return $result;
	}
	

	
	public function description(){
		return $this->getAttr('desc');
	}
	
	public static function values(){
		return static::$values;
	}
	
	public static function valueOf($name){
		return static::$values[$name];
	}
	
	private $llAttr = null;
	
	protected  function llAttr(){
		return array(
				'attr'=>array(),
				'path'=>'',
		);
	}
	
	public function __get($name) {
		return $this->getAttr($name);
	}	
}
?>