# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [8.8.10] - 2021-12-09

### Improvements
 - Check for CVE-2021-43789 (GHSA-6xxj-gcjq-wgf4)
 - Updated dependencies
 

## [8.8.9] - 2021-11-16

### Fixed
 - Fix warning

### Improvements
 - Added German translation


## [8.8.8] - 2021-11-11

### Improvements
 - Improvements for PrestaShop 1.7.8.x
 - Updated dependencies

### Fixed
 - Minor bugs


## [8.8.7] - 2021-10-16

### Improvements
 - Improvements for PHP 7.4
 - Improvemed malwarescanner
 - Updated dependencies


## [8.8.6] - 2021-08-27

### Improvements
 - Improved logging
 - Updated dependencies


## [8.8.5] - 2021-07-14

### Improvements
 - Improved backup database
 - Improved translations


## [8.8.4] - 2021-06-27

### Fixed
 - Fixed documentation for Dropbox access token, as the process has changed.

### Improvements
 - Minor improvements.
 - Improved front office performance a little.


## [8.8.3] - 2021-06-02

### Fixed
 - Compatible fix with very first versions of PrestaShop 1.6.


## [8.8.2] - 2021-05-03

### Fixed
- Bug with cronjobs for PrestaShop 1.6
- Local file backups was not deleted automatically
- Whitelisted false positive in malware scanner


## [8.8.1] - 2021-04-01

### Improvements
- Refactored the whitelist of file monitoring

### Added
- Check for CVE-2021-21398


## [8.8.0] - 2021-30-03

### Fixed
- Minor bugfixes

### Improvements
- Sort backups by date
- Improved input validation

### Added
- Feature to block custom list of email addresses from contact form
- Check for CVE-2021-21302 & CVE-2021-21308


## [8.7.9] - 2021-05-03

### Fixed
- Minor bugfix with PHP 5.6.x

### Improvements
- Thirty bees 1.2.x improvements


## [8.7.8] - 2021-03-03

### Fixed
- Minor bugfix

### Changed
- Improved coding style
- Improved performance of firewall
- Improved display of server configurations
- Improved XXS rules

### Added
- Serbian language
- Bosnian language


## [8.7.7] - 2021-02-12

### Changed
- Improved SQLi detection


## [8.7.6] - 2021-02-10

### Fixed
- Bug with HTTP headers

### Changed
- Improved coding style

### Added
- Arabic language
- Italian language


## [8.7.5] - 2021-01-31

### Fixed
- Long loading time on module back office is improved a lot
- Upload of big files to Google Drive was failing on some systems
- Google reCAPTCHA v3 token expired after 120 sec. It is now resat every 90 sec.
- Fixed error in case of invalid date format

### Changed
- Visually improvements


## [8.7.4] - 2021-01-16

### Fixed
- Minor bugfix


## [8.7.3] - 2021-01-15

### Fixed
- Bugfix for PrestaShop 1.6 with log in to BO

### Changed
- Using SHA1 for the malware report to compare files
- Updated French translation


## [8.7.2] - 2021-01-12

### Fixed
- Bugfix for Internet Explorer at two-factor auth


## [8.7.1] - 2021-01-11

### Fixed
- Login bug with two-factor auth


## [8.7.0] - 2021-01-09

### Added
- Check if the website includes front-end JavaScript libraries with known security vulnerabilities
- New feature at two-factor auth
- New feature to secure external links
- New feature to log admin login attempts

### Changed
- Improved generation of file backups 
- Improvements to the code


## [8.6.1] - 2021-01-05

### Fixed
- Login bug with two-factor auth


## [8.6.0] - 2020-01-05

### Fixed
- Old backups were included in the new backup due to an error in the path
- Fixed minor display issues

### Added
- Google Drive integration
- Option to encrypt backups (AES 256)
- BZIP2 support
- Slovakia translation

### Changed
- Improved file change monitoring
- Improved performance of the file backups
- Improved performance of back-office
- Improved Romanian translation
- Improved layouts of the logs


## [8.5.1] - 2020-12-16

### Fixed
- Detection of redirecting to HTTPS
- Detection of CVE-2018-7491

### Added
- Polish translation

### Changed
- Improved wording
