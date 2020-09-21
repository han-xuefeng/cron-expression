<?php

namespace Cron\Descriptor;

use Cron\Descriptor\Descriptor;


class SecondDescriptor extends Descriptor {


	public $name = 'second';

	public $min = 0;

	public $max = 59;

	public $defaultList = [];

	public $kind = self::NONE;

	public $step = 1;

	public function __construct(){
		$this->defaultList = range($this->min, $this->max,$this->step);
	}
	/**
	 * 不精确到秒
	 * @author woods
	 * @DateTime 2020-09-21T16:14:36+0800
	 * @return   [Descriptor]                   [SecondDescriptor]
	 */
	public static function notEnabledFactory(){
		$desc = new static();
		$desc->defaultList = [0];
		return $desc;
	}
	
}