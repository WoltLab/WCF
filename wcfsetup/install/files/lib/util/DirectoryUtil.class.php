<?php
namespace wcf\util;
use wcf\system\exception\SystemException;
use wcf\system\Callback;
use wcf\system\Regex;

/**
 * Contains directory-related functions
 * 
 * @author	Tim Duesterhus
 * @copyright	2011 Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
final class DirectoryUtil {
	/**
	 * @var	\DirectoryIterator
	 */
	protected $obj = null;
	
	/**
	 * all files with full path
	 * @var	string[]
	 */
	protected $files = [];
	
	/**
	 * all files with filename as key and DirectoryIterator object as value
	 * @var	\DirectoryIterator[]
	 */
	protected $fileObjects = [];
	
	/**
	 * directory size in bytes
	 * @var	integer
	 */
	protected $size = 0;
	
	/**
	 * directory path
	 * @var	string
	 */
	protected $directory = '';
	
	/**
	 * determines whether scan should be recursive
	 * @var	boolean
	 */
	protected $recursive = true;
	
	/**
	 * indicates that files won't be sorted
	 * @var	integer
	 */
	const SORT_NONE = -1;
	
	/**
	 * all recursive and non-recursive instances of DirectoryUtil
	 * @var	DirectoryUtil[][]
	 */
	protected static $instances = [
		true => [],	// recursive instances
		false => []        // non-recursive instances
	];
	
	/**
	 * Creates a new instance of DirectoryUtil.
	 * 
	 * @param	string		$directory	directory path
	 * @param	boolean		$recursive	created a recursive directory iterator
	 * @see		\wcf\util\DirectoryUtil::getInstance()
	 */
	public function __construct($directory, $recursive = true) {
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
	 * Returns an instance of DirectoryUtil (or child).
	 * 
	 * @param	string		$tmpDirectory	path
	 * @param	boolean		$recursive	walk through sub-directories too
	 * @return	DirectoryUtil
	 * @throws	SystemException
	 */
	public static function getInstance($tmpDirectory, $recursive = true) {
		$directory = realpath(FileUtil::unifyDirSeparator($tmpDirectory));
		// realpath returns false if the directory does not exist
		if ($directory === false) {
			throw new SystemException("Unknown directory '".$tmpDirectory."'");
		}
		if (!is_dir($directory)) {
			throw new SystemException("'".$tmpDirectory."' is no directory");
		}
		
		if (!isset(static::$instances[$recursive][$directory])) {
			static::$instances[$recursive][$directory] = new static($directory, $recursive);
		}
		
		return static::$instances[$recursive][$directory];
	}
	
	/**
	 * @see	\wcf\util\DirectoryUtil::getInstance()
	 */
	private final function __clone() {
		// does nothing
	}
	
	/**
	 * Returns a sorted list of files.
	 * 
	 * @param	integer		$order			sort-order
	 * @param	Regex		$pattern		pattern to match
	 * @param	boolean		$negativeMatch		true if the pattern should be inversed
	 * @return	string[]
	 * @throws	SystemException
	 */
	public function getFiles($order = SORT_ASC, Regex $pattern = null, $negativeMatch = false) {
		// scan the folder
		$this->scanFiles();
		$files = $this->files;
		
		// sort out non matching files
		if ($pattern !== null) {
			foreach ($files as $filename => $value) {
				if (((bool) $pattern->match($filename)) === $negativeMatch) unset($files[$filename]);
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
	 * @param	integer		$order			sort order
	 * @param	Regex		$pattern		pattern to match
	 * @param	boolean		$negativeMatch		should the pattern be inversed
	 * @return	\DirectoryIterator[]
	 * @throws	SystemException
	 */
	public function getFileObjects($order = SORT_ASC, Regex $pattern = null, $negativeMatch = false) {
		// scan the folder
		$this->scanFileObjects();
		$objects = $this->fileObjects;
		
		// sort out non matching files
		if ($pattern !== null) {
			foreach ($objects as $filename => $value) {
				if (((bool) $pattern->match($filename)) === $negativeMatch) unset($objects[$filename]);
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
				
				$this->files[FileUtil::unifyDirSeparator($filename)] = FileUtil::unifyDirSeparator($filename);
			}
		}
		else {
			foreach ($this->obj as $obj) {
				// ignore . and ..
				if ($this->obj->isDot()) continue;
				
				$this->files[FileUtil::unifyDirSeparator($obj->getFilename())] = FileUtil::unifyDirSeparator($obj->getFilename());
			}
		}
		
		// add the directory itself
		$this->files[$this->directory] = $this->directory;
	}
	
	/**
	 * Fills the list of available files, with DirectoryIterator object as value
	 */
	protected function scanFileObjects() {
		// value is cached
		if (!empty($this->fileObjects)) return;
		
		if ($this->recursive) {
			$it = new \RecursiveIteratorIterator($this->obj, \RecursiveIteratorIterator::CHILD_FIRST);
			
			foreach ($it as $filename => $obj) {
				// ignore . and ..
				if ($it->isDot()) continue;
				
				$this->fileObjects[FileUtil::unifyDirSeparator($filename)] = $obj;
			}
		}
		else {
			foreach ($this->obj as $obj) {
				// ignore . and ..
				if ($this->obj->isDot()) continue;
				
				$this->fileObjects[FileUtil::unifyDirSeparator($obj->getFilename())] = $obj;
			}
		}
		
		// add the directory itself
		$this->fileObjects[$this->directory] = new \SplFileInfo($this->directory);
	}
	
	/**
	 * Executes a callback on each file and returns false if callback is invalid.
	 * 
	 * @param	Callback	$callback
	 * @param	Regex		$pattern	callback is only applied to files matching the given pattern
	 * @return	boolean
	 */
	public function executeCallback(Callback $callback, Regex $pattern = null) {
		if ($pattern !== null) $files = $this->getFileObjects(self::SORT_NONE, $pattern);
		else $files = $this->getFileObjects(self::SORT_NONE);
		
		foreach ($files as $filename => $obj) {
			$callback($filename, $obj);
		}
		
		return true;
	}
	
	/**
	 * Recursive remove of directory.
	 */
	public function removeAll() {
		$this->removePattern(new Regex('.'));
		
		// destroy cached instance
		unset(static::$instances[$this->recursive][$this->directory]);
	}
	
	/**
	 * Removes all files that match the given pattern.
	 * 
	 * @param	Regex		$pattern		pattern to match
	 * @param	boolean		$negativeMatch		should the pattern be inversed
	 * @throws	SystemException
	 */
	public function removePattern(Regex $pattern, $negativeMatch = false) {
		if (!$this->recursive) throw new SystemException('Removing of files only works in recursive mode');
		
		$files = $this->getFileObjects(self::SORT_NONE, $pattern, $negativeMatch);
		
		foreach ($files as $filename => $obj) {
			if (!is_writable($obj->getPath())) {
				throw new SystemException("Could not remove directory: '".$obj->getPath()."' is not writable");
			}
			
			if ($obj->isDir()) {
				@rmdir($filename);
			}
			else if ($obj->isFile()) {
				@unlink($filename);
			}
		}
		
		$this->clearCaches();
	}
	
	/**
	 * Calculates the size of the directory.
	 * 
	 * @return	integer		directory size in bytes
	 * @throws	SystemException
	 */
	public function getSize() {
		if (!$this->recursive) throw new SystemException('Calculating of size only works in recursive mode');
		
		// read cached value first
		if ($this->size) return $this->size;
		
		$files = $this->getFileObjects(self::SORT_NONE);
		foreach ($files as $obj) {
			$this->size += $obj->getSize();
		}
		
		return $this->size;
	}
	
	/**
	 * Clears the caches of the current instance
	 */
	public function clearCaches() {
		// clear cached list of files
		$this->files = [];
		$this->fileObjects = [];
		
		// clear cached size
		$this->size = 0;
	}
}
