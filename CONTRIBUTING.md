Contributing to Behat Transliterator
====================================

Updating data
-------------

Setup dependencies with [Composer](https://getcomposer.org):

```bash
composer install
```

Run, char tables in Behat Transliterator will be synced from Perl library 
using version  defined in `\Behat\Transliterator\SyncTool::LIB_VERSION`

```bash
bin/update-data
```
