<?php

namespace Cron;

use Cron\Descriptor\SecondDescriptor;
use Cron\Descriptor\MinuteDescriptor;
use Cron\Descriptor\HourDescriptor;
use Cron\Descriptor\DaymDescriptor;
use Cron\Descriptor\MouthDescriptor;
use Cron\Descriptor\DaywDescriptor;
use Cron\Descriptor\YearDescriptor;
use Cron\Descriptor\Descriptor;

class CronexprParse {



	public function secondFieldHandler(string $part)
	{
		$descriptor = new SecondDescriptor();
		return $this->genericFieldHandler($part, $descriptor);
	}

	public function minuteFieldHandler(string $part)
	{
		$descriptor = new MinuteDescriptor();
		return $this->genericFieldHandler($part, $descriptor);
	}

	public function hourFieldHandler(string $part)
	{
		$descriptor = new HourDescriptor();
		return $this->genericFieldHandler($part, $descriptor);
	}

	public function daymFieldHandler(string $part)
	{
		$descriptor = new DaymDescriptor();
		return $this->genericFieldHandler($part, $descriptor);
	}

	public function mouthFieldHandler(string $part)
	{
		$descriptor = new MouthDescriptor();
		return $this->genericFieldHandler($part, $descriptor);
	}

	public function daywFieldHandler(string $part)
	{
		$descriptor = new DaywDescriptor();
		return $this->genericFieldHandler($part, $descriptor);
	}

	public function yearFieldHandler(string $part)
	{
		$descriptor = new YearDescriptor();
		return $this->genericFieldHandler($part, $descriptor);
	}



	//通用的处理器
	public function genericFieldHandler(string $part, Descriptor $desc): Descriptor
	{
		// *
		if($part === '*'){
			$desc->kind = $desc::ALL;
			$desc->defaultList = range($desc->min,$desc->max,$desc->step);
			return $desc;
		}
		// 1,2,3
		if(strpos($part, ',')){
			$fieldArr = explode(',', $part);
			sort($fieldArr);
			foreach ($fieldArr as $value) {
				$checkResutl = $desc->checkRange($value);
				if(!$checkResutl){
					throw new Exception($value .'超出合理范围');
				}
			}
			$desc->kind = $desc::ONE;
			$desc->defaultList = $fieldArr;
			return $desc;
		}
		// 6-18 and 6-18/3
		if(strpos($part,'-')){
			$fieldArr = explode('-', $part);
			if(count($fieldArr) > 2){
				throw new Exception("不合法");
			}
			
			//判断0-23/2
			if(strpos($fieldArr[1], '/')){
				$temp = explode('/', $fieldArr[1]);
				$desc->kind = $desc::ONE;
				$desc->step = $temp[1];
				$desc->defaultList = range($fieldArr[0], $temp[0],$desc->step);
			}else{
				$desc->kind = $desc::ONE;
				$desc->defaultList = range($fieldArr[0], $fieldArr[1],$desc->step);
			}
			return $desc;
		}
		// */6
		if(strpos($part, '/')){
			$fieldArr = explode('/', $part);
			if($fieldArr[0] !== '*'){
				throw new Exception("不合法");
			}

			$desc->kind = $desc::SPAN;
			$desc->step = $fieldArr[1];
			$desc->defaultList = range($desc->min,$desc->max,$desc->step);
			return $desc;
		}
		// 6
		$desc->kind = $desc::ONE;
		$desc->defaultList = [$part];
		return $desc;
	}

}