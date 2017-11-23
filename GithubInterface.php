<?php namespace Netcarver;

require_once 'GitRepositoryInterface.php';


/**
 *
 *
 *
 *
 * TODO
 * ====
 * - Treat 301, 302 & 307 return codes correctly
 * - Better caching of responses - look for tags first - if these do not exist, then no point looking for releases or
 *   tag-to-tag commits, they will not exist and will return a 404 and use up a read request.
 * - Consider adding OAUTH token support.
 *
 */
class GithubInterface implements GitRepositoryInterface {

    // Per-instance data...
    protected $remote  = null;
    protected $owner   = null;
    protected $repo    = null;
    protected $http    = null;
    protected $tags    = null;
    protected $cache   = null;
    protected $headers = [];

    // Data common across all instances...
    static protected $info = [
        'name'         => 'Github',
        'icon'         => '<i class="fa fa-icon fa-github"></i>', // Fontawesome icon markup
        'limit'        => 60,
        'remaining'    => 0,
        'reset'        => 0,
        'capabilities' => [
            'tags',
            'release-notes',
            'tag-to-tag-commits',
            'changelog-access',
            'commits',
            'forks'
        ],
    ];


    public function GetInfo() {
        return array_merge(
            self::$info,
            [
                'remote' => $this->remote,
                'owner'  => $this->owner,
                'repo'   => $this->repo,
                'tags'   => $this->tags,
            ]
        );
    }


    protected function setReadInfo($headers) {
        if (isset($headers['x-ratelimit-remaining'])) {
            self::$info['remaining'] = $headers['x-ratelimit-remaining'];
            self::$info['limit']     = $headers['x-ratelimit-limit'];
            self::$info['reset']     = $headers['x-ratelimit-reset'];
        }

    }


    public function __construct($http, $remote, $application, $cache_dir) {
        $owner = '';
        $repo  = '';
        $m     = [];
        $this->setCacheDirectory($cache_dir);
        $ok = preg_match('~^https?://github.com/([^/]++)/(.++)~i', $remote, $m);
        if ($ok) {
            $this->headers['Accept'] = 'application/vnd.github.v3+json'; // As requested by the github v3 api documentation.
            $this->headers['User-Agent'] = $application;
            $this->http   = $http;
            $this->remote = $remote;
            $this->owner  = $m[1];
            $this->repo   = $m[2];
            $this->encoded_owner = rawurlencode($m[1]);
            $this->encoded_repo  = rawurlencode($m[2]);
        } else {
            throw new \Exception('Invalid repository signature');
        }
    }


    public function GetReleaseNoteViewUrl($version) {
        $encoded_version = rawurlencode($version);
        return "https://github.com/$this->encoded_owner}/{$this->encoded_repo}/releases/tags/$encoded_version";
    }


    /**
     * Retrieves release notes field from the Github repo.
     *
     * No formatting is applied. It's up to the consumer to apply whatever formatting it wants.
     *
     * GetReleaseNotes('1.2.0'); // returns release notes for the given tag.
     * GetReleaseNotes();        // returns all the release notes for the owner-repository. - Use with care.
     */
    public function GetReleaseNotes($version=null) {
        $encoded_version = '';
        if (is_string($version) && !empty($version)) {
            $encoded_version = '/tags/' . rawurlencode($version);
        }
        $url   = "https://api.github.com/repos/{$this->encoded_owner}/{$this->encoded_repo}/releases$encoded_version";
        $reply = $this->cachedRead($url);
        if(200 == $this->http->getHttpCode() && !empty($reply['body'])) {
            return $reply['body'];
        }
        /* $this->http->setHeaders($this->headers); */
        /* $reply = $this->http->getJSON($url); */
        /* $this->setReadInfo($this->http->getResponseHeaders()); */

        return null;
    }


    public function GetTags() {
        if ($this->tags === null) {
            $url   = "https://api.github.com/repos/{$this->encoded_owner}/{$this->encoded_repo}/git/refs/tags";
            $reply = $this->cachedRead($url);
            /* $this->http->setHeaders($this->headers); */
            /* $reply = $this->http->getJSON($url); */
            /* $this->setReadInfo($this->http->getResponseHeaders()); */
            if(200 == $this->http->getHttpCode()) {
                $result = [];
                $num = count($reply);
                if ($num) {
                    foreach ($reply as $tagref) {
                        $sha = $tagref['object']['sha'];
                        $tag = str_replace('refs/tags/', '', $tagref['ref']);
                        $result[$sha] = $tag;
                    }
                }
                $this->tags = $result;
            }
        }
        return $this->tags;
    }



    /**
     * GetCommits(12);               // Returns the last 12 commits.
     * GetCommits('0.3.0', '0.4.0'); // Returns the commits between the given tags
     * GetCommits('0.3.0');          // Returns the commits between '0.3.0' and HEAD.
     *
     * If the commits cannot be accessed, null is returned.
     */
    public function GetCommits($startref, $endref='HEAD') {
        if (is_string($startref) && !empty(trim($startref))) {
            //
            // Pull all commits between the startref and endref reference points.
            // Useful for getting a list of commits between tags.
            //
            $startref_encoded = rawurlencode($startref);
            $endref_encoded   = rawurlencode($endref);
            $url   = "https://api.github.com/repos/{$this->encoded_owner}/{$this->encoded_repo}/compare/$startref_encoded...$endref_encoded";
            $slice = false;
        } else {
            //
            // Pull the last n commits.
            //
            $url   = "https://api.github.com/repos/{$this->encoded_owner}/{$this->encoded_repo}/commits";
            $slice = intval($startref);
            if ($slice < 1)  $slice =  1;
            if ($slice > 30) $slice = 30;
        }

        /* $this->http->setHeaders($this->headers); */
        /* $reply = $this->http->getJSON($url); */
        /* $this->setReadInfo($this->http->getResponseHeaders()); */
        $reply     = $this->cachedRead($url);
        $http_code = $this->http->getHttpCode();
        if(200 == $http_code || 304 == $http_code) {
            if ($slice) {
                $repo_url = null;
                $commits  = array_slice($reply, 0, $slice);
            } else {
                $repo_url = $reply['html_url'];
                $commits  = array_reverse($reply['commits']); // We want them most-recent-first
            }

            $this->GetTags();

            $history = [];
            foreach ($commits as $commit) {
                $entry = [];
                $entry['sha']     = $commit['sha'];
                $entry['url']     = $commit['html_url'];
                $entry['author']  = $commit['commit']['author']['name'];
                $entry['date']    = $commit['commit']['committer']['date'];
                $entry['message'] = $commit['commit']['message'];
                $entry['tag']     = @$this->tags[$entry['sha']];
                ksort($entry);
                $history[] = $entry;
            }
            return ['commits' => $history, 'url' => $repo_url];
        }
        return null;
    }


    public function GetChangelog() {
        $url   = "https://raw.githubusercontent.com/{$this->encoded_owner}/{$this->encoded_repo}/master/CHANGELOG.md";
        return $this->cachedRead($url, false);
        /* $this->http->setHeaders($this->headers); */
        /* $reply = $this->http->get($url); */
        /* $this->setReadInfo($this->http->getResponseHeaders()); */
        /* if(200 == $this->http->getHttpCode()) { */
        /*     return $reply; */
        /* } */
        /* return null; */
    }



    public function GetForks($sort) {
        if (!in_array($sort, ['newest', 'oldest', 'watchers'])) {
            $sort = 'newest';
        }
        $url   = "https://api.github.com/repos/{$this->encoded_owner}/{$this->encoded_repo}/forks?sort=$sort&page=1";
        return $this->cachedRead($url);
        /* $this->http->setHeaders($this->headers); */
        /* $reply = $this->http->getJson($url); */
        /* $this->setReadInfo($this->http->getResponseHeaders()); */
        /* if(200 == $this->http->getHttpCode()) { */
        /*     return $reply; */
        /* } */
        /* return null; */
    }



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
            return json_decode($entry, true);
        } else {
            return [
                'reply'    => null,
                'etag'     => null,
                'last_mod' => null,
            ];
        }
    }



    /**
     *
     */
    protected function setEntryForKey($key, $entry) {
        $file  = $this->keyToStorageLocation($key);
//\TD::barDump($file, 'Save location');
        $entry = json_encode($entry);
//\TD::barDump($entry, 'Save data');
        $ok = file_put_contents($file, $entry);

    }



    /**
     *
     */
    protected function cachedRead($url, $json = true) {
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

        if ($etag) {
            $headers['If-None-Match'] = $etag;
        } elseif ($last_mod) {
            $headers['If-Modified-Since'] = $last_mod;
        }
        $this->http->setHeaders($headers);
$debug = ['headers' => $headers, 'url' => $url];
\TD::barDump($debug, "Request");

        $new_reply        = ($json) ? $this->http->getJson($url) : $this->http->get($url);
        $http_code        = $this->http->getHttpCode();
        $response_headers = $this->http->getResponseHeaders();
        $this->setReadInfo($response_headers);
\TD::barDump($http_code, "HTTP Return Code");
\TD::barDump($response_headers, "HTTP Return Headers");

        switch ($http_code) {
        case 200:
\TD::barDump("Cache miss, read from GH, saving!");
            $entry['etag']     = @$response_headers['etag'];
            $entry['last_mod'] = @$response_headers['Last-Modified'];
            $entry['reply']    = $new_reply;
            $this->setEntryForKey($key, $entry);
            $reply = $new_reply;

/* \TD::barDump("Old etag[$etag] new etag [{$entry['etag']}]", 'etags'); */
            break;

        case 301:
\TD::barDump("Permanent redirect from GH.");
            /**
             * Permanent redirect from GH.
             */
            $new_url = $response_headers['Location'];
            break;

        case 302:
        case 307:
\TD::barDump("Temporary redirect from GH.");
            /**
             * Temporary redirect from GH. Try again - once.
             */
            $new_url = $response_headers['Location'];
            break;


        case 304:
\TD::barDump("Cache hit!");
            break;
        }

        return $reply;
    }
}
