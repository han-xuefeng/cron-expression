<?php

declare(strict_types=1);

namespace Cron;
use DateTime;
use Cron\CronexprParse;
use Cron\Descriptor\YearDescriptor;
use Cron\Descriptor\SecondDescriptor;

class Cronexpr {

	public const SECONDS = 0; 
    public const MINUTE = 1;
    public const HOUR = 2;
    public const DAY = 3;
    public const MONTH = 4;
    public const WEEKDAY = 5;
    public const YEAR = 6;


	/**
	 * [$cronLine cron表达式]
	 * @var [string]
	 */
	public $cronLine = '';

	public $errorMsg = '';
	/**
	 * [$desc 各单位的解释器]
	 * @var array
	 */
	public $desc = [];  //解释器

	public function __construct(string $cronLine)
	{
		$this->cronLine = $cronLine;
	}

	public static function mustParse(string $cronLine): Cronexpr
	{
		$cronexpr = static::parse($cronLine);
		if($cronexpr->errorMsg){
			throw new \Exception($cronexpr->errorMsg);
		}
		if(!empty($cronexpr->descError)){
			foreach ($cronexpr->descError as $key => $value) {
				throw new \Exception($key . " is not allowed" .$value);
			}
		}
		return $cronexpr;
	}
	/**
	 * 解析cron表达式
	 * @author woods
	 * @DateTime 2020-09-17T14:18:31+0800
	 * @param    string                   $cronLine [cron表达式]
	 * @return   [Cronexpr]                         [Cronexpr]
	 */
	public static function parse(string $cronLine): Cronexpr
	{
		$cronexpr = new Cronexpr($cronLine);
		$cronLineArr = explode(' ', $cronLine);
		$filedCount = count($cronLineArr);
		if($filedCount < 5){
			$cronexpr->errorMsg = $cronLine.' is not a valid CRON expression';
			return $cronexpr;
		}
		// 如果大于7，只取前7位
		if($filedCount > 7){
			$filedCount = 7;
		}
		
		$cronexprParse = new CronexprParse();
		//秒
		if($filedCount >= 6){
			$desc = $cronexprParse->secondFieldHandler($part = array_shift($cronLineArr));
			if($desc && $desc->getParseError() === ''){
				$cronexpr->desc[$desc->name] = $desc;
 			}else{
 				$cronexpr->setDescError($desc);
 				return $cronexpr;
 			}
		}else{
			$desc = SecondDescriptor::notEnabledFactory();
			$cronexpr->desc[$desc->name] = $desc;
		}
		//分
		$desc = $cronexprParse->minuteFieldHandler($part = array_shift($cronLineArr));
		if($desc && $desc->getParseError() === ''){
			$cronexpr->desc[$desc->name] = $desc;
		}else{
			$cronexpr->setDescError($desc);
 			return $cronexpr;
		}
		//时
		$desc = $cronexprParse->hourFieldHandler($part = array_shift($cronLineArr));
		if($desc && $desc->getParseError() === ''){
			$cronexpr->desc[$desc->name] = $desc;
		}else{
			$cronexpr->setDescError($desc);
 			return $cronexpr;
		}
		
		//日
		$desc = $cronexprParse->daymFieldHandler($part = array_shift($cronLineArr));
		if($desc && $desc->getParseError() === ''){
			$cronexpr->desc[$desc->name] = $desc;
			$cronexpr->desc[$desc->name]->trueList = $desc->defaultList;
		}else{
			$cronexpr->setDescError($desc);
 			return $cronexpr;
		}
		//月
		$desc = $cronexprParse->mouthFieldHandler($part = array_shift($cronLineArr));
		if($desc && $desc->getParseError() === ''){
			$cronexpr->desc[$desc->name] = $desc;
		}else{
			$cronexpr->setDescError($desc);
 			return $cronexpr;	
		}
		//周
		$desc = $cronexprParse->daywFieldHandler($part = array_shift($cronLineArr));
		if($desc && $desc->getParseError() === ''){
			$cronexpr->desc[$desc->name] = $desc;
		}else{
			$cronexpr->setDescError($desc);
 			return $cronexpr;
		}
		//年
		if($filedCount === 7){
			$desc = $cronexprParse->yearFieldHandler($part = array_shift($cronLineArr));
			if($desc && $desc->getParseError() === ''){
				$cronexpr->desc[$desc->name] = $desc;
			}else{
				$cronexpr->setDescError($desc);
 				return $cronexpr;
			}
		}else{
			$desc = YearDescriptor::notEnabledFactory();
			$cronexpr->desc[$desc->name] = $desc;
		}
		return $cronexpr;

	}

	private function setDescError($desc){
		if($desc && $desc->getParseError()){
			$this->descError[$desc->name] = $desc->getParseError();
		}
	}

	public function next()
	{
		$date = new \DateTime();
		if($this->errorMsg !== '' || !empty($this->descError)){
			return $date;
		}
		$year = $date->format('Y');
		$i = $this->desc['year']->searchInts($year);
		if($i == $this->desc['year']->getLen()){ //超出所有范围了
			$date = $this->unsetDate($date);
			return $date;
		}

		$mouth = $date->format('m');
		$i = $this->desc['mouth']->searchInts($mouth);

		if($i == $this->desc['mouth']->getLen()){
			return $this->nextYear($date);
		}
		if($mouth != $this->desc['mouth']->getIndexDefaultList($i)){
			return $this->nextMouth($date);
		}
		$this->desc['daym']->setReal((int) $date->format('Y'),(int)$date->format('m'),$this);
		
		if(empty($this->desc['daym']->getDefaultList())){
			return $this->nextMouth($date);
		}

		$day = $date->format('d');

		$i = $this->desc['daym']->searchInts($day);
		if($i == $this->desc['daym']->getLen()){
			//下一月
			return $this->nextMouth($date);
		}
		if($day != $this->desc['daym']->getIndexDefaultList($i)){
			return $this->nextDaym($date);
		}

		$hour = $date->format('H');
		$i = $this->desc['hour']->searchInts($hour);
		if($i == $this->desc['hour']->getLen()){
			return $this->nextDaym($date);
		}
		if($hour != $this->desc['hour']->getIndexDefaultList($i)){
			return $this->nextHour($date);
		}

		$minute = $date->format('i');
		$i = $this->desc['minute'] ->searchInts($minute);
		if($i == $this->desc['minute']->getLen()){
			return $this->nextHour($date);
		}

		if($minute != $this->desc['minute']->getIndexDefaultList($i)){
			return  $this->nextMinute($date);
		}

		if(empty($this->desc['second'])){
			$this->desc['second'] = new SecondDescriptor();
		}

		$second = (int)$date->format('s');
		$i = $this->desc['second']->searchInts($second);
		if($i == $this->desc['second']->getLen()){
			return $this->nextMinute($date);
		}
		$this->nextSecond($date);
		return $date;
	}

	public function nextSecond(DateTime &$date):DateTime
	{
		$second = (int)$date->format('s') + 1;
		$i = $this->desc['second']->searchInts($second);
		if($i == $this->desc['second']->getLen()){
			$date->setTime((int)$date->format('H'),(int)$date->format('i'),$second);
			return $this->nextMinute($date);
		}
		$date->setTime((int)$date->format('H'),(int)$date->format('i'),(int)$this->desc['second']->getIndexDefaultList($i));
		return $date;
	}

	public function nextMinute(DateTime &$date): DateTime
	{
		$minute = (int)$date->format('i') + 1;
		$i = $this->desc['minute'] ->searchInts($minute);
		if($i == $this->desc['minute']->getLen()){
			$date->setTime((int)$date->format('H'),$minute,(int)$this->desc['second']->getMin());
			return $this->nextHour($date);
		}
		$date->setTime((int)$date->format('H'),(int)$this->desc['minute']->getIndexDefaultList($i),(int)$this->desc['second']->getMin());
		return $date;
	}

	public function nextHour(DateTime &$date): DateTime
	{
		$hour = (int)$date->format('H') + 1;
		$i = $this->desc['hour']->searchInts($hour);
		if($i == $this->desc['hour']->getLen()){
			$date->setTime((int)$hour,(int) $this->desc['minute']->getMin());
			return $this->nextDaym($date);
		}
		$date->setTime((int)$this->desc['hour']->getIndexDefaultList($i),(int) $this->desc['minute']->getMin(),(int)$this->desc['second']->getMin());
		return $date;
	}

	public function nextDaym(DateTime &$date): DateTime
	{
		$daym = (int)$date->format('d') + 1;
		$i = $this->desc['daym']->searchInts($daym);
		if($i == $this->desc['daym']->getLen()){
			$date->setDate((int)$date->format('Y'),(int)$date->format('m'),$daym);
			return $this->nextMouth($date);
		}
		$date->setDate((int)$date->format('Y'),(int)$date->format('m'),(int)$this->desc['daym']->getIndexDefaultList($i));
		$date->setTime((int) $this->desc['hour']->getMin(),(int) $this->desc['minute']->getMin(),(int)$this->desc['second']->getMin());
		return $date;
	}

	public function nextMouth(DateTime &$date): DateTime
	{
		$mouth = (int)$date->format('m') + 1;

		$i = $this->desc['mouth']->searchInts($mouth);

		if($i == $this->desc['mouth']->getLen()){ //超出所有范围了
			$date->setDate((int)$date->format('Y'),(int)$mouth,(int)$this->desc['daym']->getMin());
			return $this->nextYear($date);
		}
		$this->desc['daym']->setReal((int) $date->format('Y'),$mouth,$this);

		if(empty($this->desc['daym']->getDefaultList())){
			$date->setDate((int)$date->format('Y'),(int)$mouth,(int)$this->desc['daym']->getMin());
			return $this->nextMouth($date);
		}

		$date->setDate((int)$date->format('Y'),(int)$this->desc['mouth']->getIndexDefaultList($i),(int)$this->desc['daym']->getMin());
		$date->setTime((int) $this->desc['hour']->getMin(),(int) $this->desc['minute']->getMin(),(int)$this->desc['second']->getMin());
		return $date;
	}

	public function nextYear(DateTime &$date): DateTime
	{	
		$year = (int)$date->format('Y') + 1;
		$i = $this->desc['year']->searchInts($year);
		if($i == $this->desc['year']->getLen()){ //超出所有范围了
			$date = $this->unsetDate($date);
			return $date;
		}
		$this->desc['daym']->setReal($year, (int)$this->desc['mouth']->getMin(),$this);
		if(empty($this->desc['daym']->getDefaultList())){
			$date->setDate((int)$year,(int)$date->format('m') + 1,(int)$this->desc['daym']->getMin());
			return $this->nextMouth($date);
		}
		$date->setDate((int)$this->desc['year']->getIndexDefaultList($i),(int)$this->desc['mouth']->getMin(),(int)$this->desc['daym']->getMin());
		$date->setTime((int) $this->desc['hour']->getMin(),(int) $this->desc['minute']->getMin(),(int)$this->desc['second']->getMin());
		return $date;
	}
	public function unsetDate(DateTime &$date): DateTime
	{
		$date->setDate(0,0,0);
		$date->setTime(0,0,0);
		return $date;
	}

}