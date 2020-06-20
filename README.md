# Wiretap

MediaWiki extension for user pageview tracking.

## Installation

1. Obtain the code from [GitHub](https://github.com/enterprisemediawiki/Wiretap)
2. Extract the files in a directory called ``Wiretap`` in your ``extensions/`` folder.
3. Add the following code at the bottom of your "LocalSettings.php" file: `require_once "$IP/extensions/Wiretap/Wiretap.php";`
4. In the command line run `php maintenance/update.php`
5. Go to "Special:Version" on your wiki to verify that the extension is successfully installed.
6. Done.

## Upgrading

If upgrading, make sure to run `php maintenance/update.php` as well as `php extensions/Wiretap/wiretapRecordPageHitCount.php --type=all`. This will create a hit-totals count for all pages in your wiki.

### Before upgrading to MediaWiki 1.25

Before upgrading your instalation to MediaWiki 1.25, make sure to grab the latest Wiretap and in addition to running update.php and wiretapRecordPageHitCount.php, also run:

```
php extensions/Wiretap/wiretapRecordLegacyHitCount.php
```

## Important note

This extension is intended for internal corporate wikis where transparency is more
important than privacy. It is definitely very invasive for an open, internet-facing
wiki. If you insist on installing it on a public wiki please make your users aware
that they are not browsing with the anonymity they are familiar with from MediaWiki.
