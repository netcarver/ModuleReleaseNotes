<?php namespace Netcarver;

interface GitRepositoryInterface {
    public function __construct($http, $remote, $application, array $options);
    public function GetInfo();
    public function GetTags();
    public function GetCommits($startref, $endref='HEAD');
    public function GetChangelog();
    public function GetForks($sort);
    public function GetRepoInfo();
    public function SetRemote($remote);
}
