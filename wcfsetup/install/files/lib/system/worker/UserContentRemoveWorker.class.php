<?php

namespace wcf\system\worker;

use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\user\content\provider\IUserContentProvider;
use wcf\system\WCF;

/**
 * Worker implementation for updating users.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class UserContentRemoveWorker extends AbstractWorker
{
    /**
     * variable name for the session to store the data
     */
    const USER_CONTENT_REMOVE_WORKER_SESSION_NAME = 'userContentRemoveWorkerData';

    /**
     * @inheritDoc
     */
    protected $limit = 10;

    /**
     * @var User[]
     */
    protected $users = [];

    /**
     * data
     * @var mixed
     */
    protected $data;

    /**
     * @var null
     */
    public $contentProviders;

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if (isset($this->parameters['userID']) && !isset($this->parameters['userIDs'])) {
            $this->parameters['userIDs'] = [$this->parameters['userID']];
        }

        if (
            isset($this->parameters['userIDs'])
            && \is_array($this->parameters['userIDs'])
            && !empty($this->parameters['userIDs'])
        ) {
            $userList = new UserList();
            $userList->setObjectIDs($this->parameters['userIDs']);
            $userList->readObjects();

            if ($userList->count() !== \count($this->parameters['userIDs'])) {
                $diff = \array_diff($this->parameters['userIDs'], \array_column($userList->getObjects(), 'userID'));

                throw new \InvalidArgumentException(
                    'The parameter `userIDs` contains unknown values (' . \implode(', ', $diff) . ').'
                );
            }

            foreach ($userList as $user) {
                if (
                    !WCF::getSession()->getPermission('admin.user.canDeleteUser')
                    || !UserGroup::isAccessibleGroup($user->getGroupIDs())
                ) {
                    throw new PermissionDeniedException();
                }

                $this->users[] = $user;
            }
        }

        if (empty($this->users)) {
            throw new \InvalidArgumentException('The parameter `userIDs` is empty.');
        }

        if (isset($this->parameters['contentProvider'])) {
            if (!\is_array($this->parameters['contentProvider'])) {
                throw new \InvalidArgumentException('The parameter `contentProvider` must be an array.');
            }

            $unknownContentProvider = \array_diff(
                $this->parameters['contentProvider'],
                \array_column(
                    ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.content.userContentProvider'),
                    'objectType'
                )
            );
            if (!empty($unknownContentProvider)) {
                throw new \InvalidArgumentException('The parameter `contentProvider` contains unknown objectTypes (' . \implode(
                    ', ',
                    $unknownContentProvider
                ) . ').');
            }

            $this->contentProviders = $this->parameters['contentProvider'];
        }

        if ($this->loopCount === 0) {
            $this->generateData();
        } else {
            $data = WCF::getSession()->getVar(self::USER_CONTENT_REMOVE_WORKER_SESSION_NAME);

            if (!\is_array($data) || !isset($data[$this->generateKey()])) {
                throw new \RuntimeException('`data` variable in session is invalid or missing.');
            }

            $this->data = $data[$this->generateKey()];
        }
    }

    /**
     * Generate the data variable.
     */
    private function generateData()
    {
        $this->data = [
            'provider' => [],
            'count' => 0,
        ];

        /** @var ObjectType[] $contentProviders */
        $contentProviders = [];

        // add the required object types for the select content provider
        if (\is_array($this->contentProviders)) {
            foreach ($this->contentProviders as $contentProvider) {
                $objectType = ObjectTypeCache::getInstance()
                    ->getObjectTypeByName('com.woltlab.wcf.content.userContentProvider', $contentProvider);
                $contentProviders[] = $objectType;

                if ($objectType->requiredobjecttype !== null) {
                    $objectTypeNames = \explode(',', $objectType->requiredobjecttype);

                    foreach ($objectTypeNames as $objectTypeName) {
                        $objectType = ObjectTypeCache::getInstance()
                            ->getObjectTypeByName('com.woltlab.wcf.content.userContentProvider', $objectTypeName);

                        if ($objectType === null) {
                            throw new \RuntimeException('Unknown required object type "' . $objectTypeName . '" for object type "' . $contentProvider . '" given.');
                        }

                        $this->contentProviders[] = $objectTypeName;
                        $contentProviders[] = $objectType;
                    }
                }
            }
        } else {
            $contentProviders = ObjectTypeCache::getInstance()
                ->getObjectTypes('com.woltlab.wcf.content.userContentProvider');
        }

        // sort object types
        \uasort($contentProviders, static function ($a, $b) {
            $niceValueA = ($a->nicevalue ?: 0);
            $niceValueB = ($b->nicevalue ?: 0);

            return $niceValueA <=> $niceValueB;
        });

        foreach ($contentProviders as $contentProvider) {
            foreach ($this->users as $user) {
                /** @var IUserContentProvider $processor */
                $processor = $contentProvider->getProcessor();
                $contentList = $processor->getContentListForUser($user);
                $count = $contentList->countObjects();

                if ($count) {
                    $this->data['provider'][] = [
                        'userID' => $user->userID,
                        'objectTypeID' => $contentProvider->objectTypeID,
                        'count' => $count,
                    ];

                    $this->data['count'] += \ceil($count / $this->limit) * $this->limit;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function countObjects()
    {
        $this->count = $this->data['count'];
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (empty($this->data['provider'])) {
            return;
        }

        $providerIDs = \array_keys($this->data['provider']);
        $provideKey = \array_shift($providerIDs);
        $currentItem = $this->data['provider'][$provideKey];

        /** @var IUserContentProvider $processor */
        $processor = ObjectTypeCache::getInstance()->getObjectType($currentItem['objectTypeID'])->getProcessor();
        $user = new User($currentItem['userID']);

        $objectList = $processor->getContentListForUser($user);
        $objectList->sqlLimit = $this->limit;
        $objectList->readObjectIDs();
        if (!empty($objectList->objectIDs)) {
            $processor->deleteContent($objectList->objectIDs);
        }

        $this->data['provider'][$provideKey]['count'] -= $this->limit;

        if ($this->data['provider'][$provideKey]['count'] <= 0) {
            unset($this->data['provider'][$provideKey]);
        }
    }

    /**
     * @inheritDoc
     */
    public function finalize()
    {
        parent::finalize();

        $dataArray = WCF::getSession()->getVar(self::USER_CONTENT_REMOVE_WORKER_SESSION_NAME);

        if (!\is_array($dataArray)) {
            $dataArray = [];
        }

        $dataArray[$this->generateKey()] = $this->data;

        WCF::getSession()->register(self::USER_CONTENT_REMOVE_WORKER_SESSION_NAME, $dataArray);

        ClipboardHandler::getInstance()->unmark(
            \array_column($this->users, 'userID'),
            ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user')
        );
    }

    /**
     * @inheritDoc
     */
    public function getProceedURL()
    {
        return LinkHandler::getInstance()->getLink('UserList');
    }

    /**
     * Generates a key for session data saving.
     */
    protected function generateKey(): string
    {
        $userIDs = \array_column($this->users, 'userID');
        \sort($userIDs);

        return \sha1(\implode(';', $userIDs));
    }
}
