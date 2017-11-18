# Notes About The ModuleReleaseNotes Module

This is an auto-loading, admin-only module for ProcessWire v3+.

## Aims

1. Make discovery of a module's changes prior to an upgrade a trivial task.
2. Make breaking changes very obvious.
3. Make module authors start to think about how they can improve the change discovery process for their modules.
4. Make reading of a module's support documentation post-install a trivial task.
5. Make sure the display of information from the module support files/commit messages doesn't introduce a vulnerability.


### Making Discovery Of Changes Easy Prior To Upgrade

A "What's Changed" section is added to the Module Update Confirmation Dialog that lists any Github Release Notes for the 
new version (along with all the commits pushed between the currently installed version and the available version) or the
contents of the project's CHANGELOG.md file (if any) or simply a list of the last commits if the above are not available.

In the case of a changelog display, attempts are made to highlight the currently installed version and the latest
version, with focus being given to all the changes between the two.


### Making Breaking Changes Obvious

The module includes a very rudimentary method of checking for breaking changes - it simply searches for one of a set of
configurable strings that could indicate a breaking change. If there is a match found, the changelog display styles are
updated to make this obvious.

It may be possible, at a later date, to support other breaking-change signalling schemes, but this will do as a starting
point.


### Getting Module Authors to Think About The Process Of Change Discovery

As part of the update confirmation dialog, details are shown of how authors can improve the change discovery process for
the module users. This module does not intend to dictate any particular method - that's up to module authors and why
this module supports multiple different methods for showing changes.


### Making Reading Of Support Files Easy

As well as supporting the discovery of changes prior to upgrade, the module makes the support files that normally
accompany a module readable from the Module's information page in the admin interface. Each file starting with README,
CHANGELOG and LICENSE can be displayed and browsed.


### Staying Safe

Files/input is either passed through HTML Purifier (in the case of HTML-formatted files/parsed markdown) or is passed
through ```htmlspecialchars()``` prior to output.



## Supported Code Hosts

ModuleReleaseNotes needs to be able to ask remotely hosted code repositories about what's changed for a module. As most
people push their code up to Github, that's where I started. The module knows how to talk to github using the v3 rest
API, but it isn't a pretty picture - yet.
