<?php

namespace wcf\system\breadcrumb;

/**
 * Represents a breadcrumb.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class Breadcrumb
{
    /**
     * displayed label
     * @var string
     */
    protected $label = '';

    /**
     * target url
     * @var string
     */
    protected $url = '';

    public function __construct(string $label, string $url)
    {
        $this->setLabel($label);
        $this->setURL($url);
    }

    /**
     * Sets the displayed label.
     *
     * @return void
     */
    public function setLabel(string $label)
    {
        $this->label = $label;
    }

    /**
     * Sets the target url.
     * May be left empty to disable url functionality.
     *
     * @param bool $appendSession This parameter is unused as of version 3.0
     * @return void
     */
    public function setURL(string $url, bool $appendSession = false)
    {
        $this->url = $url;
    }

    /**
     * Returns displayed label.
     *
     * @return  string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns target url.
     *
     * @return  string
     */
    public function getURL()
    {
        return $this->url;
    }
}
