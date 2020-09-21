<?php

namespace Cron\Descriptor;

use Cron\Descriptor\Descriptor;


class HourDescriptor extends Descriptor {


	public $name = 'hour';

	public $min = 0;

	public $max = 23;

	public $defaultList = [];

	public $kind = self::NONE;

	public $step = 1;
	
}