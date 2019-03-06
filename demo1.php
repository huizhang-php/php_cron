<?php
/**
 * User: yuzhao
 * CreateTime: 2019/2/19 下午3:28
 * Description:
 */

include_once 'CrontabApi.php';

// 删除所有Cron
CrontabApi::instance()->deleteAllJobs();

// 每分钟执行一次
CrontabApi::instance()->setCondition('*/1 * * * *')->setCommint('/usr/local/bin/php /Users/tuzisir/sites/CronNew/test/test1.php')->activate();

// 删除某个Cron
CrontabApi::instance()->deleteJob(0);

// 获取crontab任务文件中的信息
$res = CrontabApi::instance()->listJobs();
var_dump($res);

// 获取正在运行的crontab
$res = CrontabApi::instance()->listActivityJobs();
var_dump($res);
