<?php
namespace wcf\system\io;

/**
 * Represents an archive of files.
 * 
 * @author	Tim Düsterhus
 * @copyright	2012 Tim Düsterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.io
 * @category 	Community Framework
 */
interface IArchive {
	/**
	 * Returns an associative array with information
	 * about a specific file in the archive.
	 *
	 * @param	mixed	$index	index or name of the requested file
	 * @return	array
	 */
	public function getFileInfo($index);
	
	/**
	 * Extracts a specific file and returns the content as string.
	 * Returns false if extraction failed.
	 * 
	 * @param 	mixed 		$index		index or name of the requested file
	 * @return 	string 				content of the requested file
	 */
	public function extractToString($index);
	
	/**
	 * Extracts a specific file and writes it's content
	 * to the file specified with $destination.
	 * 
	 * @param 	mixed 		$index		index or name of the requested file
	 * @param 	string 		$destination
	 * @return 	boolean 	$success
	 */
	public function extract($index, $destination);
	
	/**
	 * Searchs a file in the tar archive
	 * and returns the numeric fileindex.
	 * Returns false if not found.
	 *
	 * @param 	string 		$filename
	 * @return 	integer 			index of the requested file
	 */
	public function getIndexByFilename($filename);
}
