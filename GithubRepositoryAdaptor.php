<?php namespace Netcarver;

require_once 'GitRepositoryInterface.php';
require_once 'GitRepositoryAdaptor.php';


/**
 *
 */
class GithubRepositoryAdaptor extends GitRepositoryAdaptor implements GitRepositoryInterface {


    // Data common across all instances...
    static protected $info = [
        'name'         => 'Github',
        'icon'         => '<i class="fa fa-icon fa-github"></i>', // Fontawesome icon markup
        'limit'        => 60,
        'remaining'    => 60,
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



    /**
     *
     */
    public function __construct($http, $remote, $application, array $options) {
        $owner = '';
        $repo  = '';
        $m     = [];

        if (array_key_exists('debug', $options)) {
            $this->debug($options['debug']);
        }
        if (array_key_exists('cache_dir', $options)) {
            $this->cache = new \Netcarver\FileCache();
            $this->cache->setCacheDirectory($options['cache_dir']);
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
        $url       = "https://api.github.com/repos/{$this->encoded_owner}/{$this->encoded_repo}/releases$encoded_version";
        $http_code = null;
        $reply = $this->RepositoryRead($url, $http_code);
        if ((200 == $http_code || 304 == $http_code) && !empty($reply['body'])) {
            return $reply['body'];
        }

        return null;
    }


    /**
     *
     */
    public function GetTags() {
        if (null === $this->tags) {
            $url       = "https://api.github.com/repos/{$this->encoded_owner}/{$this->encoded_repo}/git/refs/tags";
            $http_code = null;
            $reply     = $this->RepositoryRead($url, $http_code);
            if (200 == $http_code || 304 == $http_code) {
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

        $http_code = null;
        $reply = $this->RepositoryRead($url, $http_code);
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


    /**
     *
     */
    public function GetChangelog() {
        $url       = "https://raw.githubusercontent.com/{$this->encoded_owner}/{$this->encoded_repo}/master/CHANGELOG.md";
        $http_code = null;
        $reply     = $this->RepositoryRead($url, $http_code, false);
        return $reply;
    }



    /**
     *
     */
    public function GetForks($sort) {
        if (!in_array($sort, ['newest', 'oldest', 'watchers'])) {
            $sort = 'newest';
        }
        $url       = "https://api.github.com/repos/{$this->encoded_owner}/{$this->encoded_repo}/forks?sort=$sort&page=1";
        $http_code = null;
        $reply     = $this->RepositoryRead($url, $http_code);
        return $reply;
    }


    /**
     * Override the postRead() function from the base class.
     *
     * For github, we use this to record the remaining reads from the reply headers.
     */
    protected function postRead(array $data) {
        parent::postRead($data);
        $reply_headers = $data['reply_headers'];
        if (isset($reply_headers['x-ratelimit-remaining'])) {
            self::$info['remaining'] = $reply_headers['x-ratelimit-remaining'];
            self::$info['limit']     = $reply_headers['x-ratelimit-limit'];
            self::$info['reset']     = $reply_headers['x-ratelimit-reset'];
        }
    }


}
