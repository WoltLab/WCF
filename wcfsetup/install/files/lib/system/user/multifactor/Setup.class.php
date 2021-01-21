<?php

namespace wcf\system\user\multifactor;

use wcf\data\IIDObject;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\WCF;

/**
 * Represents a multifactor setup.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\System\User\Multifactor
 * @since   5.4
 */
final class Setup implements IIDObject
{
    /**
     * @var array
     */
    private $row;

    private $isDeleted = false;

    private function __construct(array $row)
    {
        $this->row = $row;
    }

    /**
     * Returns the setup ID.
     */
    public function getId(): int
    {
        if ($this->isDeleted) {
            throw new \BadMethodCallException('The Setup is deleted.');
        }

        return $this->row['setupID'];
    }

    /**
     * @see Setup::getId()
     */
    public function getObjectID(): int
    {
        if ($this->isDeleted) {
            throw new \BadMethodCallException('The Setup is deleted.');
        }

        return $this->getId();
    }

    /**
     * Returns the object type.
     */
    public function getObjectType(): ObjectType
    {
        if ($this->isDeleted) {
            throw new \BadMethodCallException('The Setup is deleted.');
        }

        return ObjectTypeCache::getInstance()->getObjectType($this->row['objectTypeID']);
    }

    /**
     * Returns the user.
     */
    public function getUser(): User
    {
        if ($this->isDeleted) {
            throw new \BadMethodCallException('The Setup is deleted.');
        }

        return UserRuntimeCache::getInstance()->getObject($this->row['userID']);
    }

    /**
     * Locks the database record for this setup, preventing concurrent changes, and returns itself.
     */
    public function lock(): self
    {
        if ($this->isDeleted) {
            throw new \BadMethodCallException('The Setup is deleted.');
        }

        $sql = "SELECT	setupId
			FROM	wcf" . WCF_N . "_user_multifactor
			WHERE	setupId = ?
			FOR UPDATE";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([
            $this->getId(),
        ]);

        $setupId = \intval($statement->fetchSingleColumn());
        \assert($setupId === $this->getId());

        return $this;
    }

    /**
     * Deletes the setup.
     */
    public function delete(): void
    {
        $sql = "DELETE FROM	wcf" . WCF_N . "_user_multifactor
			WHERE		setupId = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([
            $this->getId(),
        ]);
        $this->isDeleted = true;
    }

    /**
     * Returns an existing setup for the given objectType and user or null if none was found.
     */
    public static function find(ObjectType $objectType, User $user): ?self
    {
        $sql = "SELECT	*
			FROM	wcf" . WCF_N . "_user_multifactor
			WHERE	userID = ?
				AND objectTypeID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([
            $user->userID,
            $objectType->objectTypeID,
        ]);
        $row = $statement->fetchSingleRow();

        if ($row) {
            return new self($row);
        }

        return null;
    }

    /**
     * Returns all setups for a single user.
     *
     * @return self[]
     */
    public static function getAllForUser(User $user): array
    {
        $sql = "SELECT	*
			FROM	wcf" . WCF_N . "_user_multifactor
			WHERE	userID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$user->userID]);

        $setups = [];
        while ($row = $statement->fetchArray()) {
            $setups[$row['setupID']] = new self($row);
        }

        return $setups;
    }

    /**
     * Allocates a fresh setup for the given objectType and user.
     */
    public static function allocateSetUpId(ObjectType $objectType, User $user): self
    {
        $sql = "INSERT INTO	wcf" . WCF_N . "_user_multifactor
					(userID, objectTypeID)
			VALUES		(?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([
            $user->userID,
            $objectType->objectTypeID,
        ]);

        $setup = self::find($objectType, $user);
        \assert($setup);

        return $setup;
    }
}
