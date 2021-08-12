# laminas-progressbar

[![Build Status](https://travis-ci.org/laminas/laminas-progressbar.svg?branch=master)](https://travis-ci.org/laminas/laminas-progressbar)
[![Coverage Status](https://coveralls.io/repos/github/laminas/laminas-progressbar/badge.svg?branch=master)](https://coveralls.io/github/laminas/laminas-progressbar?branch=master)

laminas-progressbar is a component to create and update progress bars in different
environments. It consists of a single backend, which outputs the progress through
one of the multiple adapters. On every update, it takes an absolute value and
optionally a status message, and then calls the adapter with some precalculated
values like percentage and estimated time left.

## Installation

Run the following to install this library:

```bash
$ composer require laminas/laminas-progressbar
```

## Documentation

Browse the documentation online at https://docs.laminas.dev/laminas-progressbar/

## Support

* [Issues](https://github.com/laminas/laminas-progressbar/issues/)
* [Chat](https://laminas.dev/chat/)
* [Forum](https://discourse.laminas.dev/)
