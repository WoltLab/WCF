<?php

namespace wcf\page;

use Psr\Http\Message\ResponseInterface;

/**
 * All page classes should implement this interface.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IPage
{
    /**
     * Initializes the page.
     *
     * @return void|ResponseInterface
     */
    public function __run();

    /**
     * Reads the given parameters.
     *
     * @return void
     */
    public function readParameters();

    /**
     * Checks the modules of this page.
     *
     * @return void
     */
    public function checkModules();

    /**
     * Checks the permissions of this page.
     *
     * @return void
     */
    public function checkPermissions();

    /**
     * Reads/Gets the data to be displayed on this page.
     *
     * @return void
     */
    public function readData();

    /**
     * Assigns variables to the template engine.
     *
     * @return void
     */
    public function assignVariables();

    /**
     * Shows the requested page.
     *
     * @return void
     */
    public function show();
}
