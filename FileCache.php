<?php namespace Netcarver;

class FileCache {
    /**
     *
     */
    protected $cache_dir = null;


    /**
     *
     */
    public function setCacheDirectory($dir = null) {
        if (is_string($dir) && !empty($dir)) {
            $this->cache_dir = realpath("$dir/");
        } else {
            $this->cache_dir = dirname(__FILE__) . "/cache";
        }
    }



    /**
     *
     */
    protected function urlToKey($url) {
        $key = str_replace('https://', '', strtolower($url));
        $key = str_replace(['/',':','?','#', '%'], '-', $key);
        return $key;
    }




    /**
     *
     */
    protected function keyToStorageLocation($key) {
        $dir = $this->cache_dir;
        if (!$dir || !is_readable($dir)) {
            throw new \Exception("Cache directory is invalid or unreadable.");
        }
        $location = "{$this->cache_dir}/$key";
        return $location;
    }



    /**
     *
     */
    protected function getEntryForKey($key) {
        $file  = $this->keyToStorageLocation($key);
        $entry = @file_get_contents($file);
        if ($entry) {
            if ((strncmp($entry, "\x1F\x8B", 2) === 0) && is_callable('gzinflate')) {
                $entry = gzinflate($entry);
            }
            $entry = json_decode($entry, true);
            return $entry;
        }
        return null;
    }



    /**
     *
     */
    protected function setEntryForKey($key, $entry) {
        $file  = $this->keyToStorageLocation($key);
        $entry = json_encode($entry);

        if (is_callable('gzdeflate')) {
            $entry = gzdeflate($entry);
        }
        file_put_contents($file, $entry);
    }


    /**
     *
     */
    public function getCachedFiles() {
        return glob($this->cache_dir."/*");
    }


    /**
     *
     */
    public function clearCache() {
        $files = $this->getCachedFiles();
        if (count($files)) {
            foreach($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}
