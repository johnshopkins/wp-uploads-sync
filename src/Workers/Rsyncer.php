<?php

namespace UploadsSync\Workers;

class Rsyncer
{
  /**
     * Gearman worker
     * @var object
     */
    protected $worker;

    /**
     * Monolog
     * @var object
     */
    protected $logger;

    /**
     * Elasticsearch client
     * @var object
     */
    protected $elasticsearchClient;

    protected $index = "jhu";

    public function __construct($settings = array(), $injection = array())
    {
        $this->worker = $settings["worker"];
        $this->logger = $settings["logger"];

        $this->addFunctions();
    }

    protected function addFunctions()
    {
        $this->worker->addFunction("sync_uploads", array($this, "syncUploads"));
    }
    
    public function syncUploads(\GearmanJob $job)
    {
        $workload = json_decode($job->workload());
        echo $this->getDate() . " Uploads sync triggered from {$workload->trigger}.\n";

        $file = "/opt/scripts/wordpress-sync.sh";
        
        if (file_exists($file)) {
          shell_exec("sh {$file}");
        } else {
          $this->logger->addCritical("{$file} is unavailabe. Images are NOT being synced between servers. " . __FILE__ . " on line " . __LINE__);
        }
        
    }

    protected function getDate()
    {
        return date("Y-m-d H:i:s");
    }
}
