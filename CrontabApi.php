<?php
/**
 * User: yuzhao
 * CreateTime: 2019/2/19 下午3:28
 * Description: PHP管理Crontab
 */

class CrontabApi {

    /**
     * User: yuzhao
     * CreateTime: 2019/2/19 下午6:41
     * @var string
     * Description: crontab 执行目录
     */
    var $crontab = '/usr/bin/crontab';

    /**
     * User: yuzhao
     * CreateTime: 2019/2/19 下午6:41
     * @var string
     * Description: crontab信息存放路径
     */
    var $destination = '/tmp/CronManager';

    /**
     * User: yuzhao
     * CreateTime: 2019/2/19 下午6:41
     * @var string
     * Description: 时间信息
     */
    var $condition = '* * * * *';

    /**
     * User: yuzhao
     * CreateTime: 2019/2/19 下午6:41
     * @var array
     * Description:  所有任务
     */
    var $jobs = array();

    /**
     * CrontabApi constructor.
     */
    public function __construct() {
        if (!is_writable($this->destination) || !file_exists($this->destination)) {
            die('crontab文件没有权限或不存在');
        }
    }

    /**
     * User: yuzhao
     * CreateTime: 2019/2/19 下午6:41
     * @return CrontabApi
     * Description: 返回当前对象
     */
    public static function instance() {
        return new CrontabApi();
    }

    /**
     * User: yuzhao
     * CreateTime: 2019/2/19 下午6:40
     * @param $condition
     * @return $this
     * Description: 设置时间条件
     */
    public function setCondition($condition) {
        $this->condition = $condition;
        return $this;
    }

    /**
     * User: yuzhao
     * CreateTime: 2019/2/19 下午6:40
     * @param $job
     * @return $this
     * Description: 设置需要执行的命令
     */
    public function setCommint($job) {
        $this->jobs[] = $this->condition . ' ' . $job;
        return $this;
    }

    /**
     * User: yuzhao
     * CreateTime: 2019/2/19 下午6:40
     * @param bool $includeOldJobs
     * @return bool
     * Description: 启动任务
     */
    public function activate($includeOldJobs = TRUE){
        $contents = implode("\n", $this->jobs);
        $contents .= "\n";
        if ($includeOldJobs) {
            $oldJobs = $this->listJobs();
            if (!empty($oldJobs)) {
                foreach ($oldJobs as $job) {
                    if (trim($job) == "") continue;
                    $contents .= $job;
                    $contents .= "\n";
                }
            }
        }
        if (is_writable($this->destination) || !file_exists($this->destination)) {
            $this->synExec($this->crontab . ' -r;', false);
            file_put_contents($this->destination, $contents, LOCK_EX);
            $this->synExec($this->crontab . ' ' . $this->destination . ';');
            return TRUE;
        }
        return FALSE;
    }

    /**
     * User: yuzhao
     * CreateTime: 2019/2/19 下午6:40
     * @return bool
     * Description: 删除所有任务
     */
    function deleteAllJobs()
    {
        $this->synExec($this->crontab . ' -r;', false);
        file_put_contents($this->destination, '', LOCK_EX);
        $this->synExec($this->crontab . ' ' . $this->destination . ';');
        return TRUE;
    }

    /**
     * User: yuzhao
     * CreateTime: 2019/2/19 下午6:40
     * @param $id
     * @return bool
     * Description: 删除任务
     */
    function deleteJob($id)
    {
        $allJobs = $this->listJobs();
        if (empty($allJobs)) {
            die('没有任务');
        }
        foreach ($allJobs as $key => $job) {
            if ($key == $id || $job == "") {
                unset($allJobs[$key]);
            }
        }
        $this->deleteAllJobs();
        $contents = '';
        foreach ($allJobs as $job) {
            $contents .= $job;
            $contents .= "\n";
        }
        $this->synExec($this->crontab . ' -r;');
        file_put_contents($this->destination, $contents, LOCK_EX);
        $this->synExec($this->crontab . ' ' . $this->destination . ';');
        return TRUE;
    }

    /**
     * User: yuzhao
     * CreateTime: 2019/2/19 下午6:31
     * @return array
     * Description: 获取cron文件中的job信息
     */
    public function listJobs()
    {
        $res = file_get_contents($this->destination);
        $res = explode("\n", $res);
        if (end($res) == '') {
            unset($res[count($res)-1]);
        }
        return $res;
    }

    /**
     * User: yuzhao
     * CreateTime: 2019/2/19 下午6:52
     * @return mixed
     * Description: 获取正在运行的job
     */
    public function listActivityJobs() {
        exec($this->crontab . ' -l;', $output, $retval);
        return $output;
    }

    /**
     * User: yuzhao
     * CreateTime: 2019/2/19 下午6:40
     * @param string $comment
     * Description: 异步执行脚本
     */
    private function synExec($comment='', $isShow=true) {
        if ($isShow == false) {
            $comment .= ' > /dev/null &';
        }
        pclose(popen($comment, 'r'));
    }
}