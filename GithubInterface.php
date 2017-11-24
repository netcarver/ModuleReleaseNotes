<?php namespace Netcarver;

require_once 'FileCache.php';
require_once 'GitRepositoryInterface.php';


/**
 *
 *
 *
 *
 * TODO
 * ====
 *  - Lookup GZIP detection from old stuff
 * - Treat 301, 302 & 307 return codes correctly
 * - Consider adding OAUTH token support.
 *
 */
class GithubInterface extends FileCache implements GitRepositoryInterface {

    // Per-instance data...
    protected $remote  = null;
    protected $owner   = null;
    protected $repo    = null;
    protected $http    = null;
    protected $tags    = null;
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
            'forks',
        ],
    ];


    /**
     * Returns information about this repository interface.
     *
     * This is a mixture of static and per-instance information.
     */
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



    public function __construct($http, $remote, $application, array $options) {
        $owner = '';
        $repo  = '';
        $m     = [];

        if (array_key_exists('cache_dir', $options)) {
            $this->setCacheDirectory($options['cache_dir']);
        } else {
            throw new \Exception('Cache directory is needed.');
        }
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
            $this->GetTags();
        } else {
            throw new \Exception('Invalid repository signature');
        }
    }


    /**
     * Returns the URL for humans to review the Release Notes at GH.
     */
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

        //
        // If we could not access any tags for this repo, then there will be no release notes.
        //
        if (null === $this->tags) return null;

        $encoded_version = '';
        if (is_string($version) && !empty($version)) {
            // If the version is not in the tag list, we waste a read just to fetch a 404.
            if (!in_array($version, $this->tags)) {
                return null;
            }
            $encoded_version = '/tags/' . rawurlencode($version);
        }
        $url   = "https://api.github.com/repos/{$this->encoded_owner}/{$this->encoded_repo}/releases$encoded_version";
        $reply = $this->RepositoryRead($url);
        if(200 == $this->http->getHttpCode() && !empty($reply['body'])) {
            return $reply['body'];
        }

        return null;
    }


    public function GetTags() {
        if (null === $this->tags) {
            $url   = "https://api.github.com/repos/{$this->encoded_owner}/{$this->encoded_repo}/git/refs/tags";
            $reply = $this->RepositoryRead($url);
            $code  = $this->http->getHttpCode();
            if(200 == $code || 304 == $code) {
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
            // If we could not access any tags for this repo, then there will be no tag-to-tag commit.
            //
            // If the startref or the endref (when not HEAD) is not in the taglist there will be no tag-to-tag list.
            if (null === $this->tags || !in_array($startref, $this->tags)) {
                return null;
            }
            if ('HEAD' !== $endref && !in_array($endref, $this->tags)) {
                return null;
            }

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

        $reply     = $this->RepositoryRead($url);
        $http_code = $this->http->getHttpCode();
        if(200 == $http_code || 304 == $http_code) {
            if ($slice) {
                $repo_url = null;
                $commits  = array_slice($reply, 0, $slice);
            } else {
                $repo_url = $reply['html_url'];
                $commits  = array_reverse($reply['commits']); // We want them most-recent-first
            }

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
        $url = "https://raw.githubusercontent.com/{$this->encoded_owner}/{$this->encoded_repo}/master/CHANGELOG.md";
        return $this->RepositoryRead($url, false);
    }



    public function GetForks($sort) {
        if (!in_array($sort, ['newest', 'oldest', 'watchers'])) {
            $sort = 'newest';
        }
        $url = "https://api.github.com/repos/{$this->encoded_owner}/{$this->encoded_repo}/forks?sort=$sort&page=1";
        return $this->RepositoryRead($url);
    }



    /**
     *
     */
    protected function RepositoryRead($url, $json = true) {
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

        $new_reply        = ($json) ? $this->http->getJson($url) : $this->http->get($url);
        $http_code        = $this->http->getHttpCode();
        $response_headers = $this->http->getResponseHeaders();
        if (isset($response_headers['x-ratelimit-remaining'])) {
            self::$info['remaining'] = $response_headers['x-ratelimit-remaining'];
            self::$info['limit']     = $response_headers['x-ratelimit-limit'];
            self::$info['reset']     = $response_headers['x-ratelimit-reset'];
        }

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
            $new_url = $response_headers['Location'];
            break;

        case 302:
        case 307:
            /**
             * Temporary redirect from GH. Try again - once.
             */
            $new_url = $response_headers['Location'];
            break;

        case 304:
            /**
             * Cache hit! File contents are already in the cache!
             */
            break;

        case 403:
        case 404:
            break;
        }

        return $reply;
    }
}
