<?php

namespace wcf\system\endpoint\controller\core\styles;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\style\StyleList;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\WCF;

/**
 * API endpoint to get the template for the style chooser.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
#[GetRequest('/core/styles/chooser')]
final class GetStyleChooser implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $styleList = $this->getStyles();

        return new JsonResponse([
            'template' => WCF::getTPL()->render('wcf', 'styleChooser', [
                'styleList' => $styleList,
            ]),
        ]);
    }

    private function getStyles(): StyleList
    {
        $styleList = new StyleList();
        if (!WCF::getSession()->getPermission('admin.style.canUseDisabledStyle')) {
            $styleList->getConditionBuilder()->add("style.isDisabled = ?", [0]);
        }
        $styleList->sqlOrderBy = "style.styleName ASC";
        $styleList->readObjects();

        return $styleList;
    }
}
