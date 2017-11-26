<?php namespace Netcarver;

require_once 'FileCache.php';

/**
 *
 */
class GitRepositoryAdaptor extends FileCache {

    // Per-instance data...
    protected $remote  = null;
    protected $owner   = null;
    protected $repo    = null;
    protected $http    = null;
    protected $tags    = null;
    protected $debug   = false;
    protected $headers = [];
    protected $encoded_owner = null;
    protected $encoded_repo  = null;


    public function debug($newvalue = null) {
        if (null !== $newvalue) {
            $this->debug = (bool) $newvalue;
        }
        return $this->debug;
    }


    /**
     * This is called after the remote is read for whatever reason.
     *
     * Override in descendants to provide specific funtionality - like recording rate limits from reply headers.
     */
    protected function postRead(array $data) {
        if ($this->debug && class_exists('\TD')) {
            \TD::barDump($data, 'Read Summary');
        }
    }


    /**
     * Reads from the Remote Repository - or from a cached result (if any).
     *
     * @param  string $url        Input:  URL of the resource to be accessed.
     * @param  mixed  &$http_code Output: The http code of the remote http response.
     * @param  bool   $json       Input:  (Optional) If the reply from the remote is JSON (true - the default) or RAW (false)
     * @return mixed              Either null, the actual reply, or the cached reply.
     */
    protected function RepositoryRead($url, &$http_code, $json = true) {
        $reply    = null;
        $etag     = null;
        $last_mod = null;
        $headers  = $this->headers;
        $key      = $this->urlToKey($url);
        $entry    = $this->getEntryForKey($key);
        if ($entry) {
            $reply    = $entry['reply'];
            $etag     = $entry['etag'];
            $last_mod = $entry['last_mod'];
        }

        if ($etag === '404') {
            // Cached 404 - see if it is beyond the retry time...
            if (time() < $last_mod) {
                $http_code = false; // Indicate a no-remote-read condition.
                return null;
            }
        } elseif ($etag) {
            $headers['If-None-Match'] = $etag;
        } elseif ($last_mod) {
            $headers['If-Modified-Since'] = $last_mod;
        }
        $this->http->setHeaders($headers);

        $new_reply        = ($json) ? $this->http->getJson($url) : $this->http->get($url);
        $http_code        = $this->http->getHttpCode();
        $response_headers = $this->http->getResponseHeaders();
        $data = [
            'url'   => $url,
            'key'   => $key,
            'entry' => $entry,
            'code'  => $http_code,
            'request_headers' => $headers,
            'reply_headers'   => $response_headers,
            'reply' => $new_reply,
        ];
        $this->postRead($data);

        switch ($http_code) {
        case 200:
            /**
             * Cache miss, but we now have the values from the headers and body that we can store in the cache.
             */
            $entry['etag']     = @$response_headers['etag'];
            $entry['last_mod'] = @$response_headers['Last-Modified'];
            $entry['reply']    = $new_reply;
            $this->setEntryForKey($key, $entry);
            $reply = $new_reply;
            break;

        case 301:
            /**
             * Permanent redirect from GH.
             */
            //$new_url = $response_headers['Location'];
            break;

        case 302:
        case 307:
            /**
             * Temporary redirect from GH. Try again - once.
             */
            //$new_url = $response_headers['Location'];
            break;

        case 304:
            /**
             * Cache hit! File contents are already in the cache!
             */
            break;

        case 403:
            break;

        case 404:
            /**
             * No such resource.
             */
            $cache_for = 2;
            $reply = null;
            $entry['etag']     = '404';
            $entry['last_mod'] = time() + ($cache_for * 60); // Cache 404s for 10 minutes TODO set to 600 before commit. NO COMMIT
            $entry['reply']    = null;
            $this->setEntryForKey($key, $entry);
\TD::barDump(sprintf("Caching returned 404 for %u minutes.", $cache_for));
            break;
        }

        return $reply;
    }

}
