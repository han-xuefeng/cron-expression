<?php

namespace Cron\Descriptor;

use Cron\Descriptor\Descriptor;


class MinuteDescriptor extends Descriptor {


	public $name = 'minute';

	public $min = 0;

	public $max = 59;

	public $defaultList = [];

	public $kind = self::NONE;

	public $step = 1;
	
}