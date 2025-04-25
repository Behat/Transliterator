Behat Transliterator
====================

This package is abandoned
-------------------------

This package is now abandoned and will not receive further updates. The underlying transliteration
dataset it is based on has not been updated since 2016, and there are now better options available.
Additionally, there are some complications around licensing (see [#3](https://github.com/Behat/Transliterator/issues/3))
due to the mixed licenses of the original packages it was based on.

Suggested alternatives include:

* symfony/string for generating slugs
* the native `Transliterator` class built into PHP's `ext-intl` for transliterating text.
  Unlike this package, that can also respect locale-dependent transliterations.
* The `//TRANSLIT` feature of PHP's native `iconv()` extension (available by default)


Behat Transliterator provides transliteration utilities for PHP.

Transliteration data are ported from the [Perl Text-Unidecode module](https://metacpan.org/pod/Text::Unidecode).

[![License](https://poser.pugx.org/behat/transliterator/license.svg)](https://packagist.org/packages/behat/transliterator)
[![CI](https://github.com/Behat/Transliterator/actions/workflows/ci.yml/badge.svg)](https://github.com/Behat/Transliterator/actions/workflows/ci.yml)
[![Total Downloads](https://poser.pugx.org/behat/transliterator/downloads.svg)](https://packagist.org/packages/behat/transliterator)

Installation
------------

The easiest way to install Behat is by using [Composer](https://getcomposer.org):

```bash
composer require behat/transliterator
```

Usage
-----

All features of the library are exposed as static public methods in the [``Behat\Transliterator\Transliterator`` class](src/Behat/Transliterator/Transliterator.php).
