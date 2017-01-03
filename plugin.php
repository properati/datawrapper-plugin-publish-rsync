<?php

/**
 * Datawrapper Publish S3
 *
 */

class DatawrapperPlugin_PublishRsync extends DatawrapperPlugin {

    public function init() {
	    DatawrapperHooks::register(DatawrapperHooks::PUBLISH_FILES, array($this, 'publish'));
	    DatawrapperHooks::register(DatawrapperHooks::UNPUBLISH_FILES, array($this, 'unpublish'));
	    DatawrapperHooks::register(DatawrapperHooks::GET_PUBLISHED_URL, array($this, 'getUrl'));
    }

    /**
     * pushs a list of files to rsync
     *
     * @param files list of file descriptions in the format [localFile, remoteFile, contentType]
     * e.g.
     *
     * array(
     *     array('path/to/local/file', 'remote/file', 'text/plain')
     * )
     */
    public function publish($files) {
      #debug_log("Publishing... ");
      $cfg = $this->getConfig();
      $tmp = sys_get_temp_dir() . '/datawrapper-' . rand() . '/';
      foreach($files as $file){
        $reldir=dirname($file[1]);
        $dest = $tmp . $reldir;
        if (! file_exists($dest) ){
          mkdir($dest, 0700, true);
        }
        #debug_log("copy ".$file[0] . " " . $tmp . $file[1]);
        copy($file[0], $tmp . $file[1]);
      }
      $c = "RSYNC_PASSWORD=".escapeshellarg($cfg["password"])." rsync --log-file=/tmp/rsync.log -r ". escapeshellarg($tmp . "/") ." " . escapeshellarg($cfg['destination']);
      #debug_log($c);
      exec($c, $ret);
      #debug_log($ret.join("\n"));
    }

    /**
     * Removes a list of files from S3
     *
     * @param files  list of remote file names (removeFile)
     */
    public function unpublish($files) {
    }


    /**
     * Returns URL to the chart hosted on S3
     *
     * @param chart Chart class
     */
    public function getUrl($chart) {
      $cfg = $this->getConfig();
      return($cfg['base_url'] . $chart->getID());
    }

}
