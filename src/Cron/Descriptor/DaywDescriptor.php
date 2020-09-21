<?php

namespace Cron\Descriptor;

use Cron\Descriptor\Descriptor;


class DaywDescriptor extends Descriptor {


	public $name = 'dayw';

	public $min = 0;

	public $max = 6;

	public $defaultList = [];

	public $kind = self::NONE;

	public $step = 1;
	
}