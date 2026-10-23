<?php

namespace wcf\system\endpoint\controller\core\polls;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\poll\VotePoll;
use wcf\data\poll\Poll;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\poll\PollManager;
use wcf\system\view\poll\PollWidgetView;
use wcf\system\WCF;

/**
 * Stores the vote of the active user in the poll with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/polls/{id:\d+}/vote')]
final class Vote implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $poll = Helper::fetchObjectFromRequestParameter($variables['id'], Poll::class);
        PollManager::getInstance()->loadRelatedObject($poll);

        if (!$poll->canVote()) {
            throw new PermissionDeniedException();
        }

        $parameters = Helper::mapApiParameters($request, VoteParameters::class);
        $optionIDs = \array_values(\array_unique($parameters->optionIDs));

        if ($optionIDs === []) {
            throw new UserInputException('optionIDs');
        }

        if (\count($optionIDs) > $poll->maxVotes) {
            throw new UserInputException('optionIDs', 'maxVotes');
        }

        if (\count($optionIDs) < $poll->getRequiredVotes()) {
            throw new UserInputException('optionIDs', 'minVotes');
        }

        $options = $poll->getOptions();
        foreach ($optionIDs as $optionID) {
            if (!isset($options[$optionID])) {
                throw new UserInputException('optionIDs', 'unknownOption');
            }
        }

        (new VotePoll($poll, WCF::getUser(), $optionIDs))();

        $poll = new Poll($poll->pollID);
        PollManager::getInstance()->loadRelatedObject($poll);

        // Standalone polls are embedded as a widget that is replaced as a whole.
        if ($poll->isStandalone()) {
            $template = (string)new PollWidgetView($poll);
        } else {
            $template = WCF::getTPL()->render('wcf', 'pollResult', [
                'poll' => $poll,
            ]);
        }

        return new JsonResponse([
            'changeableVote' => $poll->isChangeable === 1,
            'totalVotes' => $poll->votes,
            'totalVotesTooltip' => WCF::getLanguage()->getDynamicVariable(
                'wcf.poll.totalVotes',
                ['poll' => $poll]
            ),
            'template' => $template,
        ]);
    }
}

/** @internal */
final class VoteParameters
{
    public function __construct(
        /** @var list<positive-int> */
        public readonly array $optionIDs,
    ) {}
}
