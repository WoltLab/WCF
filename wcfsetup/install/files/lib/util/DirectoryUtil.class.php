<?php
namespace wcf\util;
use wcf\system\exception\SystemException;

/**
* Contains directory-related functions
*
* @author Tim Düsterhus
* @copyright 2011 Tim Düsterhus
* @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
* @package com.woltlab.wcf
* @subpackage util
* @category Community Framework
*/
class DirectoryUtil {
	/**
	 * @var DirectoryIterator | RecursiveDirectoryIterator
	 */
	protected $obj = null;

	/**
	 * Stores all files with full path
	 *
	 * @var array<string>
	 */
	protected $files = array();

	/**
	 * Stores all files with filename as key and DirectoryIterator object as value
	 *
	 * @var array<DirectoryIterator>
	 */
	protected $filesObj = array();

	/**
	 * Directory-size in bytes
	 *
	 * @var	integer
	 */
	protected $size = 0;

	/**
	 * Directory path
	 *
	 * @var	string
	 */
	protected $directory = '';

	/**
	 * Determines wether scan should be recursive
	 *
	 * @var	boolean
	 */
	protected $recursive = true;
	
	/**
	 * No sorting
	 *
	 * @var	integer
	 */
	const SORT_NONE = -1;

	/**
	 * All recursive and non-recursive instances
	 *
	 * @var array<array>
	 */
	protected static $instances = array(
		true => array(),	// recursive instances
		false => array()	// non-recursive instances
	);

	/**
	 * Creates a new instance of DirectoryUtil
	 *
	 * @param	string				$directory	directory path
	 * @param	boolean				$recursive	created a recursive directory iterator
	 * @see		DirectoryUtil::getInstance()
	 */
	protected function __construct($directory, $recursive = true) {
		$this->directory = $directory;
		$this->recursive = $recursive;

		// handle iterator type
		if ($this->recursive) {
			$this->obj = new \RecursiveDirectoryIterator($directory);
		}
		else {
			$this->obj = new \DirectoryIterator($directory);
		}
	}

	/**
	 * @see		DirectoryUtil::getInstance()
	 */
	private final function __clone() {}

	/**
	 * Clears an instance
	 *
	 * @param	string		$directory	directory path
	 * @param	boolean		$recursive	destroy a recursive instance
	 * @return	boolean				success
	 */
	public static function destroy($directory, $recursive = true) {
		$directory = realpath(FileUtil::unifyDirSeperator($directory));
		if (!isset(static::$instances[$recursive][$directory])) {
			return false;
		}
		
		unset (static::$instances[$recursive][$directory]);
		return true;
	}

	/**
	 * Returns an instance of DirectoryUtil (or child)
	 *
	 * @param	string		$directory	Path
	 * @param	boolean		$recursive	Walk through sub-directories too
	 * @return	static
	 */
	public static function getInstance($tmpDirectory, $recursive = true) {
		$directory = realpath(FileUtil::unifyDirSeperator($tmpDirectory));
		// realpath returns false if the directory does not exist
		if ($directory === false) {
			throw new SystemException("Unknown directory '".$tmpDirectory."'");
		}
		
		if (!isset(static::$instances[$recursive][$directory])) {
			static::$instances[$recursive][$directory] = new static($directory, $recursive);
		}

		return static::$instances[$recursive][$directory];
	}

	/**
	 * Returns a sorted list of files
	 *
	 * @param	integer		$order			sort-order
	 * @param	string		$pattern			pattern to match
	 * @param	boolean		$negativeMatch	should the pattern be inversed
	 * @return	array<string>
	 */
	public function getFiles($order = SORT_ASC, $pattern = '', $negativeMatch = false) {
		// scan the folder
		$this->scanFiles();
		$files = $this->files;

		// sort out non matching files
		if (!empty($pattern)) {
			foreach ($files as $filename => $value) {
				if (preg_match($pattern, $filename) != $negativeMatch) unset($files[$filename]);
			}
		}

		if ($order == SORT_DESC) {
			krsort($files, $order);
		}
		else if ($order == SORT_ASC) {
			ksort($files, $order);
		}
		else if ($order == self::SORT_NONE) {
			// nothing to do here :)
		}
		else {
			throw new SystemException('The given sorting is not supported');
		}

		return $files;
	}

	/**
	 * Returns a sorted list of files, with DirectoryIterator object as value
	 *
	 * @param	integer				$order			sort-order
	 * @param	string				$pattern			pattern to match
	 * @param	boolean				$negativeMatch	should the pattern be inversed
	 * @return	array<DirectoryIterator>
	 */
	public function getFilesObj($order = SORT_ASC, $pattern = '', $negativeMatch = false) {
		// scan the folder
		$this->scanFilesObj();
		$objects = $this->filesObj;

		// sort out non matching files
		if (!empty($pattern)) {
			foreach ($objects as $filename => $value) {
				if (preg_match($pattern, $filename) != $negativeMatch) unset($objects[$filename]);
			}
		}

		if ($order == SORT_DESC) {
			krsort($objects, $order);
		}
		else if ($order == SORT_ASC) {
			ksort($objects, $order);
		}
		else if ($order == self::SORT_NONE) {
			// nothing to do here :)
		}
		else {
			throw new SystemException('The given sorting is not supported');
		}

		return $objects;
	}

	/**
	 * Fills the list of available files
	 */
	protected function scanFiles() {
		// value is cached
		if (!empty($this->files)) return;

		if ($this->recursive) {
			$it = new \RecursiveIteratorIterator($this->obj, \RecursiveIteratorIterator::CHILD_FIRST);

			foreach ($it as $filename => $obj) {
				// ignore . and ..
				if ($it->isDot()) continue;

				$this->files[$filename] = $filename;
			}
		}
		else {
			foreach ($this->obj as $filename => $obj) {
				// ignore . and ..
				if ($this->obj->isDot()) continue;

				$this->files[$obj->getFilename()] = $obj->getFilename();
			}
		}

		// add the directory itself
		$this->filesObj[$this->directory] = $this->directory;
	}

	/**
	 * Fills the list of available files, with DirectoryIterator object as value
	 */
	protected function scanFilesObj() {
		// value is cached
		if (!empty($this->filesObj)) return;

		if ($this->recursive) {
			$it = new \RecursiveIteratorIterator($this->obj, \RecursiveIteratorIterator::CHILD_FIRST);

			foreach ($it as $filename => $obj) {
				// ignore . and ..
				if ($it->isDot()) continue;

				$this->filesObj[$filename] = $obj;
			}
		}
		else {
			foreach ($this->obj as $filename => $obj) {
				// ignore . and ..
				if ($this->obj->isDot()) continue;

				$this->filesObj[$obj->getFilename()] = $obj;
			}
		}

		// add the directory itself
		$this->filesObj[$this->directory] = new \SPLFileInfo($this->directory);
	}
	
	/**
	 * Executes a callback on each file
	 *
	 * @param	callback	$callback	Valid callback
	 * @param	string		$pattern	Apply callback only to files matching the given pattern
	 * @return	boolean				Returns false if callback is invalid
	 */
	public function executeCallback($callback, $pattern = '') {
		if (!is_callable($callback)) return false;

		$files = $this->getFilesObj(self::SORT_NONE, $pattern);
		foreach ($files as $filename => $obj) {
			call_user_func($callback, $filename, $obj);
		}

		return true;
	}
	
	/**
	 * Recursive remove of directory
	 */
	public function removeAll() {
		$this->removePattern('');
		
		// destroy cached instance
		unset(static::$instances[$this->recursive][$this->directory]);
	}

	/**
	 * Removes all files that match the pattern
	 *
	 * @param	string	$pattern	regex pattern
	 */
	public function removePattern($pattern) {
		if (!$this->recursive) throw new SystemException('Removing of files only works in recursive mode');

		$files = $this->getFilesObj(self::SORT_NONE, $pattern);

		foreach ($files as $filename => $obj) {
			if (!is_writable($obj->getPath())) {
				throw new SystemException("Could not remove directory: '".$obj->getPath()."' is not writable");
			}

			if ($obj->isDir()) {
				rmdir($filename);
			}
			else if ($obj->isFile()) {
				unlink($filename);
			}
		}
		
		// clear cache
		$this->filesObj = array();
		$this->scanFilesObj();

		$this->files = array();
		$this->scanFiles();
	}

	/**
	 * Calculates the size of the directory.
	 *
	 * @return	integer		Directorysize in bytes
	 */
	public function getSize() {
		if (!$this->recursive) throw new SystemException('Calculating of size only works in recursive mode');
		
		// read cached value first
		if ($this->size) return $this->size;

		$files = $this->getFilesObj(self::SORT_NONE);
		foreach ($files as $filename => $obj) {
			$this->size += $obj->getSize();
		}

		return $this->size;
	}
}
