<?php

namespace wcf\acp\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\system\acp\dashboard\AcpDashboard;
use wcf\system\acp\dashboard\command\ConfigureBoxes;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\WCF;

/**
 * Handles the configuration of the acp dashboard boxes.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class DashboardConfigureAction implements RequestHandlerInterface
{
    private AcpDashboard $dashboard;
    private array $userConfiguration;

    public function __construct()
    {
        $this->dashboard = new AcpDashboard();
        $this->userConfiguration = $this->dashboard->getUserConfiguration();
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!WCF::getSession()->getPermission('admin.general.canUseAcp')) {
            throw new PermissionDeniedException();
        }

        $form = $this->getForm();

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $data = $form->getData();

            $command = new ConfigureBoxes($this->dashboard, WCF::getUser(), $data['boxes'] ?? []);
            $command();

            return new JsonResponse([]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function getForm(): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.acp.dashboard.configure')
        );
        $form->appendChildren([
            $this->getConfigurationFormField()
                ->id('boxes')
                ->required()
                ->options($this->getBoxOptions(), false, false)
                ->value($this->getSelectedBoxNames()),
        ]);

        $form->build();

        return $form;
    }

    private function getSelectedBoxNames(): array
    {
        $selectedBoxNames = [];

        foreach ($this->userConfiguration as $box) {
            if (!$box['enabled']) {
                continue;
            }

            $selectedBoxNames[] = $box['boxName'];
        }

        return $selectedBoxNames;
    }

    private function getConfigurationFormField(): MultipleSelectionFormField
    {
        return new class extends MultipleSelectionFormField
        {
            protected $templateName = '__dashboardBoxesConfigurationFormField';
        };
    }

    private function getBoxOptions(): array
    {
        $options = [];
        foreach ($this->dashboard->getAccessibleBoxes() as $box) {
            $options[$box->getName()] = $box->getTitle();
        }

        $this->sortBoxOptions($options);

        return $options;
    }

    private function sortBoxOptions(array &$options): void
    {
        \uksort($options, function (string $boxNameA, string $boxNameB) {
            $enabledA = true;
            $enabledB = true;
            $showOrderA = 999;
            $showOrderB = 999;

            if (isset($this->userConfiguration[$boxNameA])) {
                $enabledA = $this->userConfiguration[$boxNameA]['enabled'];
                if ($enabledA) {
                    $showOrderA = $this->userConfiguration[$boxNameA]['showOrder'];
                }
            }
            if (isset($this->userConfiguration[$boxNameB])) {
                $enabledB = $this->userConfiguration[$boxNameB]['enabled'];
                if ($enabledB) {
                    $showOrderB = $this->userConfiguration[$boxNameB]['showOrder'];
                }
            }

            if ($enabledA) {
                if ($enabledB) {
                    if ($showOrderA < $showOrderB) {
                        return -1;
                    } else if ($showOrderA > $showOrderB) {
                        return 1;
                    }

                    return 0;
                }

                return -1;
            }

            if ($enabledB) {
                return 1;
            }

            return 0;
        });
    }
}
