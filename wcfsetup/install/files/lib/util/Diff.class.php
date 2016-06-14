<?php
namespace wcf\util;

/**
 * Diff calculates the longest common subsequence of two given
 * arrays and is able to generate the differences (added / removed items)
 * between both arrays as well.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
class Diff {
	/**
	 * identifier for added lines
	 * @var	string
	 */
	const ADDED = '+';
	
	/**
	 * identifier for removed lines
	 * @var	string
	 */
	const REMOVED = '-';
	
	/**
	 * indentifier for unchanged lines
	 * @var	string
	 */
	const SAME = ' ';
	
	/**
	 * original array, as given by the user
	 * @var	array
	 */
	protected $a = [];
	
	/**
	 * modified array, as given by the user
	 * @var	array
	 */
	protected $b = [];
	
	/**
	 * size of a
	 * @var	integer
	 */
	protected $sizeA = 0;
	
	/**
	 * size of b
	 * @var	integer
	 */
	protected $sizeB = 0;
	
	/**
	 * calculated diff
	 * @var	array
	 */
	protected $d = null;
	
	/**
	 * Creates a new instance of Diff.
	 * 
	 * @param	string[]	$a	original lines of text
	 * @param	string[]	$b	modified lines of text
	 */
	public function __construct(array $a, array $b) {
		$this->a = $a;
		$this->b = $b;
		
		$this->sizeA = count($this->a);
		$this->sizeB = count($this->b);
	}
	
	/**
	 * Calculates the longest common subsequence of `$this->a`
	 * and `$this->b` and returns it as an SplFixedArray.
	 * 
	 * @return	\SplFixedArray	Array of all the items in the longest common subsequence.
	 */
	public function getLCS() {
		// skip all items at the beginning and the end that are the same
		// this reduces the size of the table and improves performance
		$offsetStart = $offsetEnd = 0;
		while ($offsetStart < $this->sizeA && $offsetStart < $this->sizeB && $this->a[$offsetStart] === $this->b[$offsetStart]) {
			$offsetStart++;
		}
		while ($offsetEnd < ($this->sizeA - $offsetStart) && $offsetEnd < ($this->sizeB - $offsetStart) && $this->a[$this->sizeA - 1 - $offsetEnd] === $this->b[$this->sizeB - 1 - $offsetEnd]) {
			$offsetEnd++;
		}
		
		// B starts with A
		if ($offsetStart === $this->sizeA) {
			return \SplFixedArray::fromArray($this->a);
		}
		// A starts with B
		if ($offsetStart === $this->sizeB) {
			return \SplFixedArray::fromArray($this->b);
		}
		// A ends with B
		if ($offsetEnd === $this->sizeB) {
			return \SplFixedArray::fromArray($this->b);
		}
		// B ends with A
		if ($offsetEnd === $this->sizeA) {
			return \SplFixedArray::fromArray($this->a);
		}
		
		// allocate table that keeps track of the subsequence lengths
		// add 1 to fit the line of zeroes at the top and at the left
		$tableHeight = $this->sizeA + 1 - $offsetStart - $offsetEnd;
		$tableWidth = $this->sizeB + 1 - $offsetStart - $offsetEnd;
		
		$table = new \SplFixedArray($tableHeight);
		for ($i = 0; $i < $tableHeight; $i++) {
			$table[$i] = new \SplFixedArray($tableWidth);
		}
		
		// begin calculating the length of the LCS
		for ($y = 0; $y < $tableHeight; $y++) {
			for ($x = 0; $x < $tableWidth; $x++) {
				// the first row and first column are simply zero
				if ($y === 0 || $x === 0) {
					$table[$y][$x] = 0;
					continue;
				}
				
				$valueA = $this->a[$y - 1 + $offsetStart];
				$valueB = $this->b[$x - 1 + $offsetStart];
				
				if ($valueA === $valueB) {
					// both items match, the subsequence becomes longer
					$table[$y][$x] = $table[$y - 1][$x - 1] + 1;
				}
				else {
					// otherwise the length is the greater length of the entry above and the entry left
					$table[$y][$x] = max($table[$y][$x - 1], $table[$y - 1][$x]);
				}
			}
		}
		
		$x = $this->sizeB - $offsetStart - $offsetEnd;
		$y = $this->sizeA - $offsetStart - $offsetEnd;
		$lcsLength = $table[$y][$x];
		
		// allocate array of the length of the LCS
		$lcs = new \SplFixedArray($lcsLength + $offsetStart + $offsetEnd);
		
		// until no more items are left in the LCS
		$i = 0;
		while ($table[$y][$x] !== 0) {
			// go to the very left of the current length
			if ($table[$y][$x - 1] === $table[$y][$x]) {
				$x--;
				continue;
			}
			
			// go to the very top of the current length
			if ($table[$y - 1][$x] === $table[$y][$x]) {
				$y--;
				continue;
			}
			
			// add the item that incremented the length to the LCS
			// we save the items in reverse order as we traverse the table from the back
			$lcs[$lcsLength + $offsetStart - (++$i)] = $this->a[$y - 1 + $offsetStart];
			
			// and go diagonally to the upper left entry
			$x--;
			$y--;
		}
		
		for ($i = 0; $i < $offsetStart; $i++) {
			$lcs[$i] = $this->a[$i];
		}
		for ($i = 0; $i < $offsetEnd; $i++) {
			$lcs[$lcsLength + $offsetStart + $i] = $this->a[$this->sizeA - 1 - ($offsetEnd - 1 - $i)];
		}
		
		return $lcs;
	}
	
	/**
	 * Builds the diff out of the longest common subsequence of `$this->a`
	 * and `$this->b` and saves it in `$this->d`.
	 */
	protected function calculateDiff() {
		if ($this->d !== null) return;
		$lcs = $this->getLCS();
		
		$this->d = [];
		$positionA = 0;
		$positionB = 0;
		foreach ($lcs as $item) {
			// find next matching item in a, every item in between must be removed
			while ($positionA < $this->sizeA && $this->a[$positionA] !== $item) {
				$this->d[] = [self::REMOVED, $this->a[$positionA++]];
			}
			
			// find next matching item in b, every item in between must be removed
			while ($positionB < $this->sizeB && $this->b[$positionB] !== $item) {
				$this->d[] = [self::ADDED, $this->b[$positionB++]];
			}
			
			// we are back in our longest common subsequence
			$this->d[] = [self::SAME, $item];
			$positionA++;
			$positionB++;
		}
		
		// append remaining items of `a` and `b`
		while ($positionA < $this->sizeA) {
			$this->d[] = [self::REMOVED, $this->a[$positionA++]];
		}
		while ($positionB < $this->sizeB) {
			$this->d[] = [self::ADDED, $this->b[$positionB++]];
		}
	}
	
	/**
	 * Returns the raw difference array.
	 * 
	 * @return	array
	 */
	public function getRawDiff() {
		$this->calculateDiff();
		
		return $this->d;
	}
	
	/**
	 * Returns a string like the one generated by unix diff.
	 * 
	 * @param	integer		$context
	 * @return	string
	 */
	public function getUnixDiff($context = 2) {
		$d = $this->getRawDiff();
		
		$result = [];
		$result[] = "--- a";
		$result[] = "+++ b";
		
		$leftStart = 1;
		$rightStart = 1;
		for ($i = 0, $max = count($d); $i < $max; $i++) {
			list($type, ) = $d[$i];
			
			if ($type == self::REMOVED || $type == self::ADDED) {
				// calculate start of context
				$start = max($i - $context, 0);
				
				// calculate start in left array
				$leftStart -= $i - $start;
				// ... and in right array
				$rightStart -= $i - $start;
				
				// set current context size
				$inContext = $context;
				
				// search the end of the current window
				$plus = $minus = 0;
				for ($j = $start; $j < $max; $j++) {
					list($type, ) = $d[$j];
					
					switch ($type) {
						case self::REMOVED:
							// reset context size
							$inContext = $context;
							$minus++;
						break;
						case self::ADDED:
							// reset context size
							$inContext = $context;
							$plus++;
						break;
						case self::SAME:
							if ($inContext) {
								// decrement remaining context
								$inContext--;
							}
							else {
								// context is zero, but this isn't an addition or removal
								// check whether the next context would overlap
								for ($k = $j; $k < $max && $k <= $j + $context; $k++) {
									if ($d[$k][0] != self::SAME) {
										$inContext = $k - $j;
										continue 2;
									}
								}
								break 2;
							}
						break;
					}
				}
				
				// calculate marker
				$result[] = '@@ -'.($leftStart).(($j - $plus - $start) > 1 ? ','.($j - $plus - $start) : '').' +'.($rightStart).(($j - $minus - $start) > 1 ? ','.($j - $minus - $start) : '').' @@';
				
				// append lines
				foreach (array_slice($d, $start, $j - $start) as $item) $result[] = implode('', $item);
				
				// shift the offset by the shown lines
				$i = $j;
				$leftStart += $j - $start - $plus;
				$rightStart += $j - $start - $minus;
			}
			
			// line is skipped
			$leftStart++;
			$rightStart++;
		}
		
		return implode("\n", $result);
	}
	
	/**
	 * @see	Diff::getUnixDiff()
	 */
	public function __toString() {
		return $this->getUnixDiff();
	}
}
