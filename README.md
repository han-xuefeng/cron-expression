# cron-expression
Calculate the next run date a CRON expression. Support years and seconds (计算crontab表达式下一个执行日期，支持年和秒)


Implementation
==========
The reference documentation for this implementation is found at
<https://en.wikipedia.org/wiki/Cron#CRON_expression>, which I copy/pasted here (laziness!) with modifications where this implementation differs:

    Field name     Mandatory?   Allowed values    Allowed special characters
    ----------     ----------   --------------    --------------------------
    Seconds        No           0-59              * / , -
    Minutes        Yes          0-59              * / , -
    Hours          Yes          0-23              * / , -
    Day of month   Yes          1-31              * / , -
    Month          Yes          1-12              * / , -
    Day of week    Yes          0-6               * / , -
    Year           No           1970–2099         * / , -

#### Asterisk ( * )
The asterisk indicates that the cron expression matches for all values of the field. E.g., using an asterisk in the 4th field (month) indicates every month. 

#### Slash ( / )
Slashes describe increments of ranges. For example `3-59/15` in the minute field indicate the third minute of the hour and every 15 minutes thereafter. The form `*/...` is equivalent to the form "first-last/...", that is, an increment over the largest possible range of the field.

#### Comma ( , )
Commas are used to separate items of a list. For example 3,4,5 in the day filed indicate the day of The 3rd, 4th and 5th of every month.

#### Hyphen ( - )
Hyphens define ranges. For example, 2000-2010 indicates every year between 2000 and 2010 AD, inclusive.



Installing
==========

Add the dependency to your project:

```bash
composer require han-xuefeng/cron-expression
```

Usage
=====
#### example1
```php
<?php

require_once '/vendor/autoload.php';

$cron = Cron\Cronexpr::parse('* * * * *');

echo $cron->next()->format('Y-m-d H:i:s');

```

#### example2
```php
<?php

require_once '/vendor/autoload.php';

$cron = Cron\Cronexpr::parse('* * * * *');

// $cron->next()->format('Y-m-d H:i:s');

$cron->next('now','PRC')->format('Y-m-d H:i:s');

```

#### example3
```php
<?php

require_once '/vendor/autoload.php';

// $cron = Cron\Cronexpr::parse('* * * * *');

$cron = Cron\Cronexpr::mustParse('* * * * *');

// $cron->next()->format('Y-m-d H:i:s');

// $cron->next('now','PRC')->format('Y-m-d H:i:s');

$cron->nextN('10');  //return array

```

Requirements
============
- PHP 7.1+
