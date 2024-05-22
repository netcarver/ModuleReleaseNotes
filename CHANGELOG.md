# **Changelog**

[Keep a Changelog]

## [0.11.4] - 2024-05-2024

-   Fix: Update deprecated JQuery syntax

## [0.11.3] - 2023-06-26

-   Fix: changeH1ToH2() to demote non-first-line H1s

## [0.11.2] - 2022-07-25

-   Replace calls to strftime() with calls to date() in preparation for PHP8.1

## [0.11.1] - 2019-09-29

-   More specific loading in admin interface.
-   More specific CSS targetting for warnings.

## [0.11.0] - 2019-09-29

### Added

-   SetRemote() and GetModuleInfo() methods.

## [0.10.8] - 2018-07-24

-   PHP5.4 Compat part 2 - Thanks @Toutouwai.

## [0.10.7] - 2018-07-06

-   Update label for remote HEAD from "Latest" to "Remote"
-   Add missing section to changelog

## [0.10.6] - 2018-03-18

-   Bugfix: Remove problematic render from codeblock.
-   PHP5.4 Compatibility - Thanks @adrianbj.

## [0.10.5] - 2017-12-16

-   Automatically add id attributes to all H2 and H3 headings in documentation.md files. This allows internal
    tables-of-contents to work. **NB** This requires the link href to match the lowercased, hyphen-for-spaces, version of
    the heading text.
-   Automatically add links back to the table of contents after each H2 or H3 heading. **NB** This requires you to have
    your table of contents to start with an H2 or H3 element entitled _"Table Of Contents"_.

## [0.10.4] - 2017-12-13

-   Add files starting with "documentation" to the list of support file on the module information page. Thanks @adrianbj.

## [0.10.3] - 2017-12-13

-   Bugfix: Use the pre-formatted module version string from module information when comparing with the current version.
-   Remove stray debug entries.

## [0.10.2] - 2017-12-02

-   Do not display entire Changelog for modules when installing on a site for the first time. Limit height and add the
    show more button.
-   Improve messages regarding missing, or feature-poor, repository adaptors.

## [0.10.1] - 2017-11-27

-   Fix warning message. Thanks @matjazp.

## [0.10.0] - 2017-11-26

-   Repo access code into separate adaptor classes that implement the GitRepository Interface.
-   Added Github interface.
-   Added basic BitBucket interface (WIP).
-   Added capabilities flags to the repo interface.
-   Rework the Update screen code to use the new interfaces.
-   Grouped config inputs into sections on the config screen.
-   Changed the Show more/Show all buttons to use current theme styles. Thanks @matjazp.
-   Remove Semantic versioning from changelog - not quite ready for it yet.
-   Simplifiy changelog formatting.
-   Add result caching with config settings.
-   Bugfix: don't load Prism when only checking if it is installed. Thanks @matjazp.

## [0.9.2] - 2017-11-22

### Fixed

-   Marker insertion routine to locate version numbers if part of a link.

### Changed

-   Changelog to adhere more closely to [Keep a Changelog] format.
-   Changelog to reflect use of [Semantic Versioning].
-   Styles to improve changelog h2 and h3 display.

## [0.9.1] - 2017-11-21

### Fixed

-   problem locating out-of-order versions in changelogs.

## [0.9.0] - 2017-11-21

### Added

-   Annotation of git commit lists with git-tags (when possible.)
-   Indication that the repository host is in a rate-limited/read-depleted state.

## [0.8.1] - 2017-11-20

### Changed

-   "Show More"/"Show All" link styling to make them more obvious - thanks Matjazp.

## [0.8.0] - 2017-11-19

### Added

-   Ability to apply Prism Code highlighting to changelogs.

## [0.7.6] - 2017-11-19

### Fixed

-   Recursion when marking up breaking changes. Search now stops at first match.

## [0.7.5] - 2017-11-19

### Fixed

-   Markdown parsing of codeblocks that have a class of HTML+PHP.

### Changed

-   How changelogs are divided into the new and existing parts in order to cleanly cut-off at the existing part.
-   How the version arrow-box highlights are inserted to improve display in UIKit admin theme and when viewing the remote changelog file as raw markdown.

## 0.7.4 - 2017-11-17 First Public Release

### Changed

-   Readme files.
-   Function scope within the module class.

## 0.7.3 - 2017-11-17

-   Merge contribute style tweaks from Mike Rockett.

## 0.7.2 - 2017-11-17

-   Add work-around to bug in UIKit theme. This requires the demotion of the h1
    heading in embedded markdown documents to h2.was-h1. This stops the UIKit
    interface from collapsing the page's h1 headline.
-   Variable renaming.
-   Reduce default wordwrap to 90 characters.

## 0.7.1 - 2017-11-17

-   Reduce changelog marker size.
-   Amend changelog marker text.
-   Remove dependency upon TextformatterParsedownExtraPlugin.
-   Remove dead lines.

## 0.7.0 - 2017-11-17

-   New changelog marker style scheme - WIP

## 0.6.2 - 2017-11-17

-   Improve JS for handling long divs.

## 0.6.1 - 2017-11-16

-   Improve wrapper text and collapse state.
-   Refactor internal method.

## 0.6.0 - 2017-11-16

-   Add height control to file content display.
-   Improve changelog division detection and marking.

## 0.5.1 - 2017-11-15

-   Pull JS and CSS out in to their own files.
-   Improve the jquery.
-   Shorten wrapping class name.

## 0.5.0 - 2017-11-15

-   Add breaking change string detection.
-   Make the old part of the update changelog expandable.

## 0.4.0 - 2017-11-15

-   Highlight the current and latest versions in display of update changelog.
    This allows reader to focus on the changes.
-   Only show Github read counter and format-chain when site in debug mode.
-   Remove language-testing readme files - I can't verify the content is appropriate.
-   Internal refactoring.
-   Improve changelog, commit list and readme styles.

## 0.3.3 - 2017-11-14

-   Add utf8-aware wordwrap function.
-   Add config option to control wrap column for plaintext.

## 0.3.2 - 2017-11-14

-   Add MIT license.

## 0.3.1 - 2017-11-14

-   Add example README files for XSS injection, language characters and code display.

## 0.3.0 - 2017-11-14

-   Fix issue with multiple declaration of Markdown parser.
-   Unify formatting and sanitization of texts.
-   Sanitisation done with HTML Purifier for Markdown.
-   Markdown style improvements.
-   Add initial permissions check.
-   Add LICENSE files to list of support files.
-   Add openSettings config option to control initial state of support file list.

## 0.2.0 - 2017-11-06

-   Search for, and display, README and CHANGELOG files from the Module's home directory in the module's information
    screen.
-   Tweak the display CSS to better handle changelogs and top level headings in README files.
-   Add .editorconfig file

## 0.1.1 - 2017-11-05

-   Remove annoying scrolling.
-   Fix an XSS vector.

## 0.1.0 - 2017-11-04

-   Initial packaging as a module.

[semantic versioning]: https://semver.org/spec/v2.0.0.html
[keep a changelog]: http://keepachangelog.com/en/1.0.0/
[upcoming]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.11.4...HEAD
[0.11.4]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.11.3...0.11.4
[0.11.3]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.11.2...0.11.3
[0.11.2]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.11.1...0.11.2
[0.11.1]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.11.0...0.11.1
[0.11.0]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.10.8...0.11.0
[0.10.8]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.10.7...0.10.8
[0.10.7]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.10.6...0.10.7
[0.10.6]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.10.5...0.10.6
[0.10.5]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.10.4...0.10.5
[0.10.4]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.10.3...0.10.4
[0.10.3]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.10.2...0.10.3
[0.10.2]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.10.1...0.10.2
[0.10.1]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.10.0...0.10.1
[0.10.0]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.9.2...0.10.0
[0.9.2]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.9.1...0.9.2
[0.9.1]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.9.0...0.9.1
[0.9.0]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.8.1...0.9.0
[0.8.1]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.8.0...0.8.1
[0.8.0]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.7.6...0.8.0
[0.7.6]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.7.5...0.7.6
[0.7.5]: https://github.com/netcarver/ModuleReleaseNotes/compare/0.7.4...0.7.5