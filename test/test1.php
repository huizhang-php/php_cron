<?php
/**
 * User: yuzhao
 * CreateTime: 2019/2/19 下午3:46
 * Description:
 */
$file = fopen('/Users/guoyuzhao/sites/CronNew/test/'.rand(0,1000).'.txt','a+');
fclose($file);