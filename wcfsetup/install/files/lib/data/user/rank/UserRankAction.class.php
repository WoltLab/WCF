<?php

namespace wcf\data\user\rank;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\InvalidObjectArgument;
use wcf\system\file\upload\UploadFile;

/**
 * Executes user rank-related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\User\Rank
 *
 * @method  UserRankEditor[]    getObjects()
 * @method  UserRankEditor      getSingleObject()
 */
class UserRankAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.user.rank.canManageRank'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['delete'];

    /**
     * @inheritDoc
     */
    public function create()
    {
        /** @var UserRank $rank */
        $rank = parent::create();

        if (isset($this->parameters['rankImageFile']) && $this->parameters['rankImageFile']) {
            if (!($this->parameters['rankImageFile'] instanceof UploadFile)) {
                throw new InvalidObjectArgument($this->parameters['rankImageFile'], UploadFile::class,
                    "The parameter 'rankImageFile'");
            }

            if (!$this->parameters['rankImageFile']->isProcessed()) {
                $fileName = $rank->rankID . '-' . $this->parameters['rankImageFile']->getFilename();

                \rename($this->parameters['rankImageFile']->getLocation(),
                    WCF_DIR . UserRank::RANK_IMAGE_DIR . $fileName);
                $this->parameters['rankImageFile']->setProcessed(WCF_DIR . UserRank::RANK_IMAGE_DIR . $fileName);

                $updateData['rankImage'] = $fileName;

                $rankEditor = new UserRankEditor($rank);
                $rankEditor->update($updateData);
            }
        }

        return $rank;
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        if (isset($this->parameters['rankImageFile__removedFiles']) && \is_array($this->parameters['rankImageFile__removedFiles'])) {
            foreach ($this->parameters['rankImageFile__removedFiles'] as $file) {
                if (!($file instanceof UploadFile)) {
                    throw new InvalidObjectArgument($this->parameters['rankImageFile__removedFiles'], UploadFile::class,
                        "An array values of 'rankImageFile__removedFiles'");
                }

                @\unlink($file->getLocation());
            }
        }

        if (isset($this->parameters['rankImageFile'])) {
            if (\count($this->objects) > 1) {
                throw new \BadMethodCallException("The parameter 'rankImageFile' can only be processed, if there is only one object to update.");
            }

            $object = \reset($this->objects);

            if (!$this->parameters['rankImageFile']) {
                $this->parameters['data']['rankImage'] = "";
            } else {
                if (!($this->parameters['rankImageFile'] instanceof UploadFile)) {
                    throw new InvalidObjectArgument($this->parameters['rankImageFile'], UploadFile::class,
                        "The parameter 'rankImageFile'");
                }

                if (!$this->parameters['rankImageFile']->isProcessed()) {
                    $fileName = $object->rankID . '-' . $this->parameters['rankImageFile']->getFilename();

                    \rename($this->parameters['rankImageFile']->getLocation(),
                        WCF_DIR . UserRank::RANK_IMAGE_DIR . $fileName);
                    $this->parameters['rankImageFile']->setProcessed(WCF_DIR . UserRank::RANK_IMAGE_DIR . $fileName);

                    $this->parameters['data']['rankImage'] = $fileName;
                }
            }
        }

        parent::update();
    }
}
