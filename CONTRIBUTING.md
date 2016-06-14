CONTRIBUTING
============

First of all: Thanks for your interest in contributing to WoltLab Suite Core! However, you have to meet some requirements in order to get your changes accepted.

**Notice:** This is the stable tree of WCF 2.1.x, if you wish to submit pull requests for WCF 2.0.x, please select the branch "2.0". 

General requirements
--------------------
- **API changes are undesirable**, we want to maintain full backwards compatibility to WCF 2.0.x
- Testing is the key, you MUST try out your changes before submitting pull requests. It saves us and yourself a lot of time.
- The code SHOULD be written by yourself, otherwise you have to check the license beforehand with regard to compatibility and give the proper credit to the original author.

Files
-----
- Unix newlines (\n) MUST be used in every file (php, tpl, less, js, etc.)
- All files MUST be saved in ASCII encoding, except language files, which MUST be UTF-8 encoded

Formatting
----------
- Tabs MUST be used for indentation, you HAVE TO use a tab size of 8
    - empty lines MUST be indentated as deep as the previous line
- All identifiers and comments MUST be written in English
- PHP
    - The closing PHP tag MUST be omitted
    - Every file MUST end with a newline character (\n)

Additionally: Have a look at existing files to find out what they should look like.

Automated tests
---------------
We are running [PHP Codesniffer](https://github.com/squizlabs/PHP_CodeSniffer) to ensure most of our formatting rules. You SHOULD test your changes before submitting them with it.

1. Install PHP Codesniffer, either via PEAR or via cloning it
2. Execute the following command in the root of your clone of WoltLab Suite Core:

   ```sh
    $ phpcs -p --extensions=php --standard="`pwd`/CodeSniff/WCF" .
   ```
3. Fix any errors
4. Repeat it until no more errors occur
