<?php

declare(strict_types=1);

namespace Cron;
use DateTime;
use DateTimeZone;
use Cron\CronexprParse;
use Cron\Descriptor\YearDescriptor;
use Cron\Descriptor\SecondDescriptor;

class Cronexpr {

	/**
	 * [$cronLine cron表达式]
	 * @var [string]
	 */
	public $cronLine = '';

	/**
	 * 表达式错误
	 * @var string
	 */
	public $errorMsg = '';
	/**
	 * 表达式对应部分错误
	 * @var array
	 */
	public $descError = [];
	/**
	 * 时区 默认系统时区
	 * @var [type]
	 */
	public $dateTimeZone;
	/**
	 * [$desc 各单位的解释器]
	 * @var array
	 */
	public $desc = [];  //解释器

	/**
	 * dateTime
	 * @var [type]
	 */
	public $date;

	public function __construct(string $cronLine,$timeZone = null)
	{
		$this->cronLine = $cronLine;
		if($timeZone){
			$timeZone = new DateTimeZone($timeZone);
		}else{
			$timeZone = new DateTimeZone(date_default_timezone_get());
		}
		$this->dateTimeZone = $timeZone; 
	}


	/**
	 * 强制解析cron-expression 
	 * @author woods
	 * @DateTime 2020-09-21T20:57:44+0800
	 * @param    string                   $cronLine [description]
	 * @return   [type]                             [description]
	 */
	public static function mustParse(string $cronLine, $timeZone = null): Cronexpr
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

	public function setDateTimeZone($timeZone)
	{
		$this->dateTimeZone = new DateTimeZone($timeZone);
	}

	/**
	 * 解析cron表达式
	 * @author woods
	 * @DateTime 2020-09-17T14:18:31+0800
	 * @param    string                   $cronLine [cron表达式]
	 * @return   [Cronexpr]                         [Cronexpr]
	 */
	public static function parse(string $cronLine,$timeZone = null): Cronexpr
	{
		$cronexpr = new Cronexpr($cronLine, $timeZone);
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

	public function next($time = 'now')
	{
		$this->date = new \DateTime($time,$this->dateTimeZone);
		if($this->errorMsg !== '' || !empty($this->descError)){
			return $this->date;
		}

		$cronexprParse = new CronexprParse();

		$year = $this->date->format('Y');
		$i = $this->desc['year']->searchInts($year);
		if($i == $this->desc['year']->getLen()){ //超出所有范围了
			$this->unsetDate($this->date);
			return $this->date;
		}
		if($year != $this->desc['year']->getIndexDefaultList($i)){
			return $cronexprParse ->nextYear($this);
		}

		$mouth = $this->date->format('m');
		$i = $this->desc['mouth']->searchInts($mouth);

		if($i == $this->desc['mouth']->getLen()){
			return $cronexprParse->nextYear($this);
		}
		if($mouth != $this->desc['mouth']->getIndexDefaultList($i)){
			return $cronexprParse->nextMouth($this);
		}
		$this->desc['daym']->setReal((int) $this->date->format('Y'),(int)$this->date->format('m'),$this);
		
		if(empty($this->desc['daym']->getDefaultList())){
			return $cronexprParse->nextMouth($this);
		}

		$day = $this->date->format('d');

		$i = $this->desc['daym']->searchInts($day);
		if($i == $this->desc['daym']->getLen()){
			//下一月
			return $cronexprParse->nextMouth($this);
		}
		if($day != $this->desc['daym']->getIndexDefaultList($i)){
			return $cronexprParse->nextDaym($this);
		}

		$hour = $this->date->format('H');
		$i = $this->desc['hour']->searchInts($hour);
		if($i == $this->desc['hour']->getLen()){
			return $cronexprParse->nextDaym($this);
		}
		if($hour != $this->desc['hour']->getIndexDefaultList($i)){
			return $cronexprParse->nextHour($this);
		}

		$minute = $this->date->format('i');
		$i = $this->desc['minute'] ->searchInts($minute);
		if($i == $this->desc['minute']->getLen()){
			return $cronexprParse->nextHour($this);
		}

		if($minute != $this->desc['minute']->getIndexDefaultList($i)){
			return  $cronexprParse->nextMinute($this);
		}

		if(empty($this->desc['second'])){
			$this->desc['second'] = new SecondDescriptor();
		}

		$second = (int)$this->date->format('s');
		$i = $this->desc['second']->searchInts($second);
		if($i == $this->desc['second']->getLen()){
			return $cronexprParse->nextMinute($this);
		}
		$cronexprParse->nextSecond($this);
		return $this->date;
	}
	
	private function unsetDate(DateTime &$date)
	{
		$date->setDate(0,0,0);
		$date->setTime(0,0,0);
	}

	public function nextN(int $n, $time = 'now'):array
	{
		$nextArr = [];
		for($i = 1; $i <= $n; $i++ ){

			$nextArr[$i] = $this->next(($this->date ? $this->date->format('Y-m-d H:i:s') : $time));
		}

		return $nextArr;
	}

}