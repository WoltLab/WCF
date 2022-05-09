# laminas-progressbar

> This package is considered feature-complete, and is now in **security-only** maintenance mode, following a [decision by the Technical Steering Committee](https://github.com/laminas/technical-steering-committee/blob/2b55453e172a1b8c9c4c212be7cf7e7a58b9352c/meetings/minutes/2020-08-03-TSC-Minutes.md#vote-on-components-to-mark-as-security-only).
> If you have a security issue, please [follow our security reporting guidelines](https://getlaminas.org/security/).
> If you wish to take on the role of maintainer, please [nominate yourself](https://github.com/laminas/technical-steering-committee/issues/new?assignees=&labels=Nomination&template=Maintainer_Nomination.md&title=%5BNOMINATION%5D%5BMAINTAINER%5D%3A+%7Bname+of+person+being+nominated%7D)


[![Build Status](https://github.com/laminas/laminas-progressbar/workflows/Continuous%20Integration/badge.svg)](https://github.com/laminas/laminas-progressbar/actions?query=workflow%3A"Continuous+Integration")

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

- [Issues](https://github.com/laminas/laminas-progressbar/issues/)
- [Chat](https://laminas.dev/chat/)
- [Forum](https://discourse.laminas.dev/)
