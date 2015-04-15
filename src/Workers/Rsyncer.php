<?php

namespace UploadsSync\Workers;

use Secrets\Secret;

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
        $this->worker->addFunction("sync_uploads_clear_cache", array($this, "syncUploadsAndClearCache"));
    }

    public function syncUploads(\GearmanJob $job)
    {
      $workload = json_decode($job->workload());
      $this->sync($workload->trigger);
      echo "---\n";
    }

    public function syncUploadsAndClearCache(\GearmanJob $job)
    {
      $workload = json_decode($job->workload());
      $success = $this->sync($workload->trigger);

      if ($success) {
        echo $this->getDate() . " Rsync is complete, ititializing cache clearing.\n";
        $this->clearCache($workload->id, $workload->cacheCleaner);
      }

      echo "---\n";
    }

    public function sync($trigger)
    {
      echo $this->getDate() . " Uploads sync triggered from {$workload->trigger}.\n";

      // get username/password from secrets file
      $auth = Secret::get("jhu", ENV, "plugins", "wp-uploads-sync");
      $username = $auth->username;
      $password = $auth->password;

      // set password env variable
      putenv("RSYNC_PASSWORD={$auth->password}");

      // set source and destination
      $source = "/var/www/sites/jhu/current/public/assets/uploads/.";
      $destination = "/366916/assets/uploads";

      // rsync files to Akamai using `apache` upload account
      $command = "rsync -az --delete {$source} {$username}@jhuwww.upload.akamai.com::{$destination} 2>&1 > /dev/null";
      $run = exec($command, $output, $return);

      var_dump($output);
      var_dump($return);

      if ($return > 0) {
        // see http://wpkg.org/Rsync_exit_codes for rsync error codes
        echo $this->getDate() . " Failed to rsync uploads to Akamai.\n";
        $this->logger->addCritical("Uploads could not be rsynced to Akamai. Rsync returned error code {$return}. in " . __FILE__ . " on line " . __LINE__);
        return false;
      } else {
        echo $this->getDate() . " Successfully rsynced uploads to Akamai.\n";
        return true;
      }

    }

    protected function clearCache($id, $cacheCleaner)
    {
      $cacheCleaner->clear_cache($id);
    }

    protected function getDate()
    {
        return date("Y-m-d H:i:s");
    }
}
