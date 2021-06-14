CONTRIBUTING
============

First of all: Thanks for your interest in contributing to WoltLab Suite Core! However, you have to meet some requirements in order to get your changes accepted.

**Notice:** This is the development tree of WoltLab Suite Core representing the upcoming version of WoltLab Suite, if you wish to submit pull requests for stable versions, please select the branch you want to work on. 

General requirements
--------------------
- **API changes are undesirable**, we want to maintain full backwards compatibility to WSC 3.0.x
- Testing is the key, you MUST try out your changes before submitting pull requests. It saves us and yourself a lot of time.
- The code SHOULD be written by yourself, otherwise you have to check the license beforehand with regard to compatibility and give the proper credit to the original author.
- For getting into developing with WoltLab Suite Core you should take a look at the [WoltLab Suite Documentation](https://docs.woltlab.com/) for developers.

Files
-----
- Unix newlines (\n) MUST be used in every file (php, tpl, scss, js, ts, etc.)
- All `*.php`- and `*tpl`-files MUST be saved in ANSI/ASCII encoding, except language files, which MUST be UTF-8 encoded
- `*.xml`-files MUST be encoded with UTF-8, but omit the BOM (byte-order-mark)

Formatting
----------
- Files MUST be formatted according to the coding standard [PSR-12](https://www.php-fig.org/psr/psr-12/).
- The best way is to use an IDE supporting EditorConfig for getting the correct settings automatically
- PHP
    - The closing PHP tag MUST be omitted
    - Every file MUST end with a newline character (\n)
- JavaScript
    - Make sure every compiled file uses unix newlines (\n)
- TypeScript
    - You MUST intend lines with two (2) spaces; tab-characters are not allowed

Additionally: Have a look at existing files to find out what they should look like.

PHP
---
- Make sure your changes fit by the minimum and maximum PHP-version of this branch:
    - Minimum version: 7.2.24
    - Maximum version: latest stable release

(My)SQL
-------
- WoltLab Suite Core supports MySQL and MariaDB only; Postfix and SQLite are not supported
- Make sure your changes fit by the minimum and maximum MySQL-version of this branch:
    - Minimum version: MySQL 5.7.31+ or MySQL 8.0.19+ or MariaDB 10.1.44+
    - Maximum version: latest stable release

TypeScript & JavaScript
-----------------------
- You MUST NOT change the JavaScript-files manually
- You MUST compile the changed TypeScript and submit the compiled JavaScript within your Pull-Request too
- Make sure to make use of ESLint and Prettier before committing

Appearance & Browsers
---------------------
- Make sure your changes fit by the minimum version of the following browsers:
	- Chrome 91
	- Firefox 89+
	- Safari 14+
	- Microsoft Edge 91+
