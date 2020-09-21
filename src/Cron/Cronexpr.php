<?php

declare(strict_types=1);

namespace Cron;
use DateTime;
use Cron\CronexprParse;
use Cron\Descriptor\YearDescriptor;

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
	/**
	 * [$secondList secondList]
	 * @var array
	 */
	public $secondList = [];
	/**
	 * [$minuteList minuteList]
	 * @var array
	 */
	public $minuteList = [];
	/**
	 * [$hourList hourList]
	 * @var array
	 */
	public $hourList = [];
	/**
	 * [$daysOfMonth daysOfMonth]
	 * @var array
	 */
	public $daysOfMonth = [];   //map[int]bool
	/**
	 * [$daysOfWeek daysOfWeek]
	 * @var array
	 */
	public $daysOfWeek = []; //map[int]bool
	/**
	 * [$monthList monthList]
	 * @var array
	 */
	public $monthList = [];
	/**
	 * [$yearList yearList]
	 * @var array
	 */
	public $yearList = [];


	public $desc = [];  //解释器

	public static function factory(): Cronexpr
	{

	}


	/**
	 * 解析cron表达式
	 * @author woods
	 * @DateTime 2020-09-17T14:18:31+0800
	 * @param    string                   $cronLine [cron表达式]
	 * @return   [Cronexpr]                         [Cronexpr]
	 */
	public static function parse( string $cronLine, string &$msg = ''): Cronexpr
	{
		$cronLineArr = explode(' ', $cronLine);

		$filedCount = count($cronLineArr);

		if($filedCount < 5){
			$msg = $cronLine.' is not a valid CRON expression';
			return false;
		}
		// 如果大于7，只取前7位
		if($filedCount > 7){
			$filedCount = 7;
		}

		$cronexpr = new static($cronLine);
		$cronexprParse = new CronexprParse();
		
		//秒
		if($filedCount === 7){
			$desc = $cronexprParse->secondFieldHandler(array_shift($cronLineArr));
			if($desc){
				$cronexpr->desc[$desc->name] = $desc;
 			}else{
 				throw new Exception("不合法");
 				
 			}
		}
		//分
		$desc = $cronexprParse->minuteFieldHandler(array_shift($cronLineArr));
		if($desc){
			$cronexpr->desc[$desc->name] = $desc;
		}else{
			throw new Exception("不合法");
			
		}
		//时
		$desc = $cronexprParse->hourFieldHandler(array_shift($cronLineArr));
		if($desc){
			$cronexpr->desc[$desc->name] = $desc;
		}else{
			throw new Exception("不合法");
			
		}
		//日
		$desc = $cronexprParse->daymFieldHandler(array_shift($cronLineArr));
		if($desc){
			$cronexpr->desc[$desc->name] = $desc;
			$cronexpr->desc[$desc->name]->trueList = $desc->defaultList;
		}else{
			throw new Exception("不合法");
			
		}
		//月
		$desc = $cronexprParse->mouthFieldHandler(array_shift($cronLineArr));
		if($desc){
			$cronexpr->desc[$desc->name] = $desc;
		}else{
			throw new Exception("不合法");
			
		}
		//周
		$desc = $cronexprParse->daywFieldHandler(array_shift($cronLineArr));
		if($desc){
			$cronexpr->desc[$desc->name] = $desc;
		}else{
			throw new Exception("不合法");
			
		}
		//年
		if($filedCount === 7){
			$desc = $cronexprParse->yearFieldHandler(array_shift($cronLineArr));
			if($desc){
				$cronexpr->desc[$desc->name] = $desc;
			}else{
				throw new Exception("不合法");
				
			}
		}
		return $cronexpr;

	}


	public function __construct(string $cronLine)
	{
		$this->cronLine = $cronLine;
	}


	public function next()
	{
		$date = new \DateTime();
		
		$year = $date->format('Y');


		$mouth = $date->format('m');
		
		// if($mouth > $this->desc['mouth']->getLast()){
		// 	return $this->nextYear($date);
		// }
		
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
			return  $this->nextMinute($minute);
		}

		// $mi = array_search($mouth,$this->desc['mouth']['defaultList']);
		// if($mi === false){
		// 	$this->nextYear($date);
		// }
		// if($mouth)
		return $date;
	}

	public function nextMinute(DateTime &$date): DateTime
	{
		$minute = (int)$date->format('i') + 1;
		$i = $this->desc['minute'] ->searchInts($minute);
		if($i == $this->desc['minute']->getLen()){
			$date->setTime((int)$date->format('H'),$minute);
			return $this->nextHour($date);
		}
		$date->setTime((int)$date->format('H'),(int)$this->desc['minute']->getIndexDefaultList($i));
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
		$date->setTime((int)$this->desc['hour']->getIndexDefaultList($i),(int) $this->desc['minute']->getMin());
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
		$date->setTime((int) $this->desc['hour']->getMin(),(int) $this->desc['minute']->getMin());
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
		$date->setTime((int) $this->desc['hour']->getMin(),(int) $this->desc['minute']->getMin());
		return $date;
	}

	public function nextYear(DateTime &$date): DateTime
	{	
		if(empty($this->desc['year'])){
			$this->desc['year'] = new YearDescriptor();
		}
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
		$date->setTime((int) $this->desc['hour']->getMin(),(int) $this->desc['minute']->getMin());
		return $date;
	}


	public function unsetDate(DateTime &$date): DateTime
	{
		$date->setDate(0,0,0);
		$date->setTime(0,0,0);
	}

}