<?php

namespace Cron\Descriptor;

use Cron\Descriptor\Descriptor;


class YearDescriptor extends Descriptor {


	public $name = 'year';

	public $min = 1970;

	public $max = 2099;

	public $defaultList = [];

	public $kind = self::NONE;

	public $step = 1;

	public function __construct(){
		$this->defaultList = range($this->min, $this->max,$this->step);
	}
	
}