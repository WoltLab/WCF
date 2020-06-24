<?php
namespace wcf\system\database\table\column;

/**
 * Represents a `varchar` database table column.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Column
 * @since	5.2
 */
class VarcharDatabaseTableColumn extends AbstractDatabaseTableColumn implements ILengthDatabaseTableColumn {
	use TLengthDatabaseTableColumn {
		getLength as protected traitGetLength;
	}
	
	/**
	 * @inheritDoc
	 */
	protected $type = 'varchar';
	
	/**
	 * @inheritDoc
	 */
	public function getLength() {
		if ($this->length === null) {
			throw new \LogicException('The length of varchar fields must be explicitly set.');
		}
		
		return $this->traitGetLength();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMaximumLength() {
		return 65535;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMinimumLength() {
		return 1;
	}
}
