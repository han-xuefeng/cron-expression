# cron-expression
Calculate the next  run date a CRON expression (计算crontab表达式下一个执行日期)

Installing
==========

Add the dependency to your project:

```bash
composer require dragonmantank/cron-expression
```

Usage
=====
```php
<?php

require_once '/vendor/autoload.php';


$cron = Cron\Cronexpr::parse('* * * * *');

echo $a->next()->format('Y-m-d H:i:s');

```

Requirements
============
- PHP 7.1+
