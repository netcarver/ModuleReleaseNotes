<?php namespace Netcarver;

interface GitRepositoryInterface {
    public function GetInfo();
    public function GetTags();
    public function GetCommits($startref, $endref='HEAD');
    public function GetChangelog();
}
