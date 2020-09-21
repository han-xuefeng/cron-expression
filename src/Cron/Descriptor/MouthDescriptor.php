<?php

namespace Cron\Descriptor;

use Cron\Descriptor\Descriptor;


class MouthDescriptor extends Descriptor {


	public $name = 'mouth';

	public $min = 1;

	public $max = 12;

	public $defaultList = [];

	public $kind = self::NONE;

	public $step = 1;	
	
}