<?php

namespace wcf\system\file\processor;

use ParagonIE\ConstantTime\Hex;
use wcf\data\file\File;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Tracks the ownership of uploaded files through a secret token that is only
 * known to the client that uploaded them. This works without a session, which
 * guests only receive on demand.
 *
 * Only the hash of the token is stored, the token itself is carried by the
 * form in the context of the file processor.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UploaderToken
{
    /**
     * Name of the key in the context of the file processor.
     */
    public const CONTEXT_KEY = 'uploaderToken';

    public static function generate(): string
    {
        return Hex::encode(\random_bytes(16));
    }

    /**
     * @phpstan-assert-if-true string $token
     */
    public static function isValid(mixed $token): bool
    {
        return \is_string($token) && \preg_match('~^[a-f0-9]{32}\z~', $token) === 1;
    }

    /**
     * Returns the token from the context or `null` if it is missing or malformed.
     *
     * @param array<string, mixed> $context
     */
    public static function fromContext(array $context): ?string
    {
        $token = $context[self::CONTEXT_KEY] ?? null;

        return self::isValid($token) ? $token : null;
    }

    /**
     * Records the token as the owner of the file.
     */
    public static function store(File $file, string $token): void
    {
        $sql = "INSERT INTO             wcf1_file_uploader_token
                                        (fileID, tokenHash)
                VALUES                  (?, ?)
                ON DUPLICATE KEY UPDATE tokenHash = VALUES(tokenHash)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $file->fileID,
            self::hash($token),
        ]);
    }

    /**
     * Returns true if no token was recorded for the file yet.
     */
    public static function isUnclaimed(File $file): bool
    {
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_file_uploader_token
                WHERE   fileID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$file->fileID]);

        return $statement->fetchSingleColumn() === 0;
    }

    /**
     * Returns true if the file was uploaded with the given token and has not
     * been released since.
     */
    public static function matches(File $file, string $token): bool
    {
        $sql = "SELECT  tokenHash
                FROM    wcf1_file_uploader_token
                WHERE   fileID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$file->fileID]);
        $tokenHash = $statement->fetchSingleColumn();

        if (!\is_string($tokenHash)) {
            return false;
        }

        return \hash_equals($tokenHash, self::hash($token));
    }

    /**
     * Counts the files that were uploaded with the given token and have not
     * been released since.
     */
    public static function countFiles(string $token): int
    {
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_file_uploader_token
                WHERE   tokenHash = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([self::hash($token)]);

        return (int)$statement->fetchSingleColumn();
    }

    /**
     * Revokes the ownership of the uploader, e.g. once the files were submitted.
     * The files remain claimed and cannot be adopted by anyone else.
     *
     * @param list<int> $fileIDs
     */
    public static function release(array $fileIDs): void
    {
        if ($fileIDs === []) {
            return;
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('fileID IN (?)', [$fileIDs]);

        // Files without a record, e.g. uploaded before the update, would otherwise
        // remain unclaimed and could still be adopted by someone else.
        $sql = "INSERT INTO             wcf1_file_uploader_token
                                        (fileID, tokenHash)
                SELECT                  fileID, NULL
                FROM                    wcf1_file
                {$conditionBuilder}
                ON DUPLICATE KEY UPDATE tokenHash = NULL";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
    }

    private static function hash(string $token): string
    {
        // Truncated to match the 128 bits of entropy of the token.
        return \substr(\hash('sha256', $token, true), 0, 16);
    }
}
