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
	
}