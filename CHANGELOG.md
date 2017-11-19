## 0.7.5 2017-11-19
- Fix markdown parsing of codeblocks that have a class of HTML+PHP.
- Cleanly cut-off remote changelog after the title of the currently installed version.
- Better placement of the version arrow-box highlights in UIKit admin theme
  and when viewing the remote changelog file as raw markdown.

## 0.7.4 2017-11-17 First Public Release
- Update readme files.
- Make some functions protected.

## 0.7.3 2017-11-17
- Merge contribute style tweaks from Mike Rockett.

## 0.7.2 2017-11-17
- Add work-around to bug in UIKit theme. This requires the demotion of the h1
  heading in embedded markdown documents to h2.was-h1. This stops the UIKit
  interface from collapsing the page's h1 headline.
- Variable renaming.
- Reduce default wordwrap to 90 characters.

## 0.7.1 2017-11-17
- Reduce changelog marker size.
- Amend changelog marker text.
- Remove dependency upon TextformatterParsedownExtraPlugin.
- Remove dead lines.

## 0.7.0 2017-11-17
- New changelog marker style scheme - WIP

## 0.6.2 2017-11-17
- Improve JS for handling long divs.

## 0.6.1 2017-11-16
- Improve wrapper text and collapse state.
- Refactor internal method.

## 0.6.0 2017-11-16
- Add height control to file content display.
- Improve changelog division detection and marking.

## 0.5.1 2017-11-15
- Pull JS and CSS out in to their own files.
- Improve the jquery.
- Shorten wrapping class name.

## 0.5.0 2017-11-15
- Add breaking change string detection.
- Make the old part of the update changelog expandable.

## 0.4.0 2017-11-15
- Highlight the current and latest versions in display of update changelog.
  This allows reader to focus on the changes.
- Only show Github read counter and format-chain when site in debug mode.
- Remove language-testing readme files - I can't verify the content is appropriate.
- Internal refactoring.
- Improve changelog, commit list and readme styles.

## 0.3.3 2017-11-14
- Add utf8-aware wordwrap function.
- Add config option to control wrap column for plaintext.

## 0.3.2 2017-11-14
- Add MIT license.

## 0.3.1 2017-11-14
- Add example README files for XSS injection, language characters and code display.

## 0.3.0 2017-11-14
- Fix issue with multiple declaration of Markdown parser.
- Unify formatting and sanitization of texts.
- Sanitisation done with HTML Purifier for Markdown.
- Markdown style improvements.
- Add initial permissions check.
- Add LICENSE files to list of support files.
- Add openSettings config option to control initial state of support file list.

## 0.2.0 2017-11-06
- Search for, and display, README and CHANGELOG files from the Module's home directory in the module's information
  screen.
- Tweak the display CSS to better handle changelogs and top level headings in README files.
- Add .editorconfig file

## 0.1.1 2017-11-05
- Remove annoying scrolling.
- Fix an XSS vector.

## 0.1.0 2017-11-04
- Initial packaging as a module.
