Update tool for Behat Transliterator
====================================

This tool performs automatic conversion of original Perl library char tables to Behat Transliterator PHP scripts

Installation
------------

Change directory to tool and setup dependencies with [Composer](https://getcomposer.org):

```bash
cd update-tool && composer install
```

Usage
-----

Run with version number, char tables in Behat Transliterator will be synced to Perl library

```bash
bin/update 1.27
```
