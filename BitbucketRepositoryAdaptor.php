<?php namespace Netcarver;

require_once 'GitRepositoryInterface.php';
require_once 'GitRepositoryAdaptor.php';


/**
 */
class BitbucketRepositoryAdaptor extends GitRepositoryAdaptor implements GitRepositoryInterface {

    // Data common across all instances...
    static protected $info = [
        'name'         => 'Bitbucket',
        'icon'         => '<i class="fa fa-icon fa-bitbucket"></i>', // Fontawesome icon markup
        'limit'        => 60,
        'remaining'    => 60,
        'reset'        => 0,
        'capabilities' => [
            /* 'tags', */
            /* 'release-notes', */
            /* 'tag-to-tag-commits', */
            /* 'changelog-access', */
            'commits',
            /* 'forks', */
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
        $ok = preg_match('~^https?://bitbucket.org/([^/]++)/(.++)~i', $remote, $m);
        if ($ok) {
            $this->headers['User-Agent'] = $application;
            $this->http   = $http;
            $this->remote = $remote;
            $this->owner  = $m[1];
            $this->repo   = rtrim($m[2], '/\\');
            $this->encoded_owner = rawurlencode($m[1]);
            $this->encoded_repo  = rawurlencode($this->repo);
            $this->GetTags();
        } else {
            throw new \Exception('Invalid repository signature');
        }
    }



    public function GetTags() {
        if (null === $this->tags) {
            $url       = "https://api.bitbucket.org/2.0/repositories/{$this->encoded_owner}/{$this->encoded_repo}/refs/tags";
            $http_code = null;
            $reply     = $this->RepositoryRead($url, $http_code);
            if (200 == $http_code || 304 == $http_code) {
                $result = [];
                $num = count($reply['values']);
                if ($num) {
                    foreach ($reply['values'] as $tagref) {
                        $sha = $tagref['object']['hash'];
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
     * GetCommits(12);               // Returns the latest 12 commits.
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
            $url   = "https://api.bitbucket.org/2.0/repositories/{$this->encoded_owner}/{$this->encoded_repo}/compare/$startref_encoded...$endref_encoded";
            $slice = false;
        } else {
            //
            // Pull the last n commits.
            //
            $url   = "https://api.bitbucket.org/2.0/repositories/{$this->encoded_owner}/{$this->encoded_repo}/commits/master?fields=values.hash,values.links.html,values.date,values.message,values.author.user.display_name";
            $slice = intval($startref);
            if ($slice < 1)  $slice =  1;
            if ($slice > 30) $slice = 30;
        }

        $http_code = null;
        $reply     = $this->RepositoryRead($url, $http_code);
        if (200 == $http_code || 304 == $http_code) {
            if ($slice) {
                $repo_url = null;
                $commits  = array_slice($reply['values'], 0, $slice);
            } else {
                //$repo_url = $reply['html_url'];
                $commits  = $reply['values'];
            }
//\TD::barDump($commits, "Commit List");

            $history = [];
            foreach ($commits as $commit) {
//\TD::barDump($commit);
                $entry = [];
                $entry['sha']     = $commit['hash'];
                $entry['url']     = $commit['links']['html']['href'];
                $entry['author']  = $commit['author']['user']['display_name'];
                $entry['date']    = str_replace('+00:00', 'Z', $commit['date']);
                $entry['message'] = $commit['message'];
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
        $url = "https://api.bitbucket.com/2.0/repositories/{$this->encoded_owner}/{$this->encoded_repo}/src/HEAD/CHANGELOG.md";
        $http_code = null;
        return $this->RepositoryRead($url, $http_code, false);
    }



    /**
     *
     */
    public function GetForks($sort) {
        if (!in_array($sort, ['newest', 'oldest', 'watchers'])) {
            $sort = 'newest';
        }
        $url       = "https://api.bitbucket.org/2.0/repositories/{$this->encoded_owner}/{$this->encoded_repo}/forks";
        $http_code = null;
        return $this->RepositoryRead($url, $http_code);
    }

}
