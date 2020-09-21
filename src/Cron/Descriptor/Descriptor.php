<?php

namespace Cron\Descriptor;

class Descriptor {

	public const NONE = 0;
	public const ONE = 1;
	public const SPAN = 2;
	public const ALL = 3;

	public $parseError = '';

	public function getMin()
	{
		return $this->defaultList ? $this->defaultList[0] : $this->min ;
	}

	public function getDefaultList():array
	{
		return $this->defaultList;
	}

	public function getIndexDefaultList($i){

		return $this->defaultList[$i];
	}

	public function getLen():int
	{
		return count($this->defaultList);
	}

	public function getLast(): int
	{	
		return $this->getDefaultList()[$this->getLen() - 1];
	}

	public function searchInts($filed){
		
		if($filed > $this->getLast()){
			return $this->getLen();
		}
		/**todo	*/
		if($this->getLen() === 1){
			if($filed <= $this->defaultList[0]){
				return 0;
			}else{
				return 1;
			}
		}
		foreach ($this->defaultList as $key => $value) {
			if($filed == $value){
				return $key;
			}elseif($filed > $value && $filed < $this->defaultList[$key+1]){
				return $key + 1;
			}
		}
	}

	public function checkRange($value):bool
	{
		if($this->min <= $value && $this->max >= $value){
			return true;
		}else{
			return false;
		}
	}

	public function getParseError():string
	{
		return $this->parseError;
	}

}