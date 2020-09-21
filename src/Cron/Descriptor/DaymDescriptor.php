<?php

namespace Cron\Descriptor;

use Cron\Descriptor\Descriptor;


class DaymDescriptor extends Descriptor {


	public $name = 'daym';

	public $min = 1;

	public $max = 31;

	public $defaultList = [];

	public $kind = self::NONE;

	public $step = 1;

	public $trueList = [];

	public function setReal($year,$mouth,&$cron){

		$this->defaultList = $this->trueList;

		$this->max = cal_days_in_month(CAL_GREGORIAN, $mouth, $year);
		// == *
		if($this->kind == self::ALL && $cron->desc['dayw']->kind == self::ALL){
			$this->defaultList = range($this->min, $this->max.$this->step);
			$cron->desc[$this->name] = $this;
			return true;
		}

		
		$default = $this->defaultList;
		$this->defaultList = [];
		foreach ($default as $key => $value) {
			if($value <= $this->max){
				$this->defaultList[] = $value;
			}
		}

		if($cron->desc['dayw']->kind != self::ALL){
			
			$default = $this->defaultList;
			$this->defaultList = [];
			foreach ($default as $val) {
				$w = date('w',strtotime($year.'-'.$mouth.'-'.$val));
				if(in_array($w, $cron->desc['dayw']->getDefaultList())){
					$this->defaultList[] = $val;
				}
			}
		}
		$cron->desc['daym'] = $this;
		return true;
	}
	
}