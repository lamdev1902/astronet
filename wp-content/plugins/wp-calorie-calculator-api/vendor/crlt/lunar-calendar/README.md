# The Gregorian calendar to Lunar calendar
> Just for practice composer tool usage myself

## Install
```shell
composer require crlt/lunar-calendar
```


## Usage
```php
<?php
require_once "vendor/autoload.php";
use Crlt_\LunarCalendar\LunarCalendar;

$lunar = new LunarCalendar();

echo $lunar->toLunar(2017,8,16);

```

## Reference
- https://github.com/NearXdu/chinese-calendar
- http://blog.jjonline.cn/userInterFace/173.html
- https://github.com/echosoar/gdate/blob/master/0.1.2/gdate_0.1.2.js

## License
MIT

