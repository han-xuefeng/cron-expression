<?php

namespace Cron;

use DateTime;
use Cron\Cronexpr;
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
					$desc->parseError = 'Beyond reasonable bounds';
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
				$desc->parseError = 'Unsupported format';
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
				$desc->parseError = 'Unsupported format';
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

	public function nextYear(Cronexpr &$cronexpr): DateTime
	{	
		$year = (int)$cronexpr->date->format('Y') + 1;
		$i = $cronexpr->desc['year']->searchInts($year);
		if($i == $cronexpr->desc['year']->getLen()){ //超出所有范围了
			$cronexpr->date = $cronexpr->unsetDate($cronexpr->date);
			return $cronexpr->date;
		}
		$cronexpr->desc['daym']->setReal($year, (int)$cronexpr->desc['mouth']->getMin(),$cronexpr);
		if(empty($cronexpr->desc['daym']->getDefaultList())){
			$cronexpr->date->setDate((int)$year,(int)$cronexpr->date->format('m') + 1,(int)$cronexpr->desc['daym']->getMin());
			return $this->nextMouth($cronexpr);
		}
		$cronexpr->date->setDate((int)$cronexpr->desc['year']->getIndexDefaultList($i),(int)$cronexpr->desc['mouth']->getMin(),(int)$cronexpr->desc['daym']->getMin());
		$cronexpr->date->setTime((int) $cronexpr->desc['hour']->getMin(),(int) $cronexpr->desc['minute']->getMin(),(int)$cronexpr->desc['second']->getMin());
		return $cronexpr->date;
	}

	public function nextMouth(Cronexpr &$cronexpr): DateTime
	{
		$mouth = (int)$cronexpr->date->format('m') + 1;

		$i = $cronexpr->desc['mouth']->searchInts($mouth);

		if($i == $cronexpr->desc['mouth']->getLen()){ //超出所有范围了
			$cronexpr->date->setDate((int)$cronexpr->date->format('Y'),(int)$mouth,(int)$cronexpr->desc['daym']->getMin());
			return $this->nextYear($cronexpr);
		}
		$cronexpr->desc['daym']->setReal((int) $cronexpr->date->format('Y'),$mouth,$cronexpr);

		if(empty($cronexpr->desc['daym']->getDefaultList())){
			$cronexpr->date->setDate((int)$cronexpr->date->format('Y'),(int)$mouth,(int)$cronexpr->desc['daym']->getMin());
			return $this->nextMouth($cronexpr);
		}

		$cronexpr->date->setDate((int)$cronexpr->date->format('Y'),(int)$cronexpr->desc['mouth']->getIndexDefaultList($i),(int)$cronexpr->desc['daym']->getMin());
		$cronexpr->date->setTime((int) $cronexpr->desc['hour']->getMin(),(int) $cronexpr->desc['minute']->getMin(),(int)$cronexpr->desc['second']->getMin());
		return $cronexpr->date;
	}

	public function nextDaym(Cronexpr &$cronexpr): DateTime
	{
		$daym = (int)$cronexpr->date->format('d') + 1;
		$i = $cronexpr->desc['daym']->searchInts($daym);
		if($i == $cronexpr->desc['daym']->getLen()){
			$cronexpr->date->setDate((int)$cronexpr->date->format('Y'),(int)$cronexpr->date->format('m'),$daym);
			return $this->nextMouth($cronexpr);
		}
		$cronexpr->date->setDate((int)$cronexpr->date->format('Y'),(int)$cronexpr->date->format('m'),(int)$cronexpr->desc['daym']->getIndexDefaultList($i));
		$cronexpr->date->setTime((int) $cronexpr->desc['hour']->getMin(),(int) $cronexpr->desc['minute']->getMin(),(int)$cronexpr->desc['second']->getMin());
		return $cronexpr->date;
	}

	public function nextMinute(Cronexpr &$cronexpr): DateTime
	{
		$minute = (int)$cronexpr->date->format('i') + 1;
		$i = $cronexpr->desc['minute'] ->searchInts($minute);
		if($i == $cronexpr->desc['minute']->getLen()){
			$cronexpr->date->setTime((int)$cronexpr->date->format('H'),$minute,(int)$cronexpr->desc['second']->getMin());
			return $this->nextHour($cronexpr);
		}
		$cronexpr->date->setTime((int)$cronexpr->date->format('H'),(int)$cronexpr->desc['minute']->getIndexDefaultList($i),(int)$cronexpr->desc['second']->getMin());
		return $cronexpr->date;
	}

	public function nextHour(Cronexpr &$cronexpr): DateTime
	{
		$hour = (int)$cronexpr->date->format('H') + 1;
		$i = $cronexpr->desc['hour']->searchInts($hour);
		if($i == $cronexpr->desc['hour']->getLen()){
			$cronexpr->date->setTime((int)$hour,(int) $cronexpr->desc['minute']->getMin());
			return $this->nextDaym($cronexpr);
		}
		$cronexpr->date->setTime((int)$cronexpr->desc['hour']->getIndexDefaultList($i),(int) $cronexpr->desc['minute']->getMin(),(int)$cronexpr->desc['second']->getMin());
		return $cronexpr->date;
	}

	public function nextSecond(Cronexpr &$cronexpr):DateTime
	{
		$second = (int)$cronexpr->date->format('s') + 1;
		$i = $cronexpr->desc['second']->searchInts($second);
		if($i == $cronexpr->desc['second']->getLen()){
			$cronexpr->date->setTime((int)$cronexpr->date->format('H'),(int)$cronexpr->date->format('i'),$second);
			return $this->nextMinute($cronexpr);
		}
		$cronexpr->date->setTime((int)$cronexpr->date->format('H'),(int)$cronexpr->date->format('i'),(int)$cronexpr->desc['second']->getIndexDefaultList($i));
		return $cronexpr->date;
	}

}