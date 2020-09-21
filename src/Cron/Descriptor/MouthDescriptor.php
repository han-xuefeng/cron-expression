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

	//字段处理
	public function fieldHandler(&$date)
	{
		$currentMouth = $date->format('m');
		switch ($this->kind) {
			case self::NONE:
				throw new Exception("Error Processing Request", 1);
				break;
			
			case self::ONE:
			case self::SPAN:
				if(in_array($currentMouth, $this->defaultList)){

				}else{
					$this->nextYear($date);
				}
			case self::ALL:
				
				break;
		}

	}

	/**
	 * 月份不合法  下一年从新计算月份
	 * @author woods
	 * @DateTime 2020-09-18T15:31:19+0800
	 * @return   [type]                   [description]
	 */
	public function nextYear(&$date)
	{
		$date->modify('+1 year');
		$date->setDate((int)$date->format('Y'),(int)$this->getMinMouth(),(int)$date->format('d'));
	}

	
	
}