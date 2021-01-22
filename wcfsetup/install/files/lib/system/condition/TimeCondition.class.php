<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\StringUtil;

/**
 * Condition implementation for the current time.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Condition
 */
class TimeCondition extends AbstractMultipleFieldsCondition implements IContentCondition
{
    /**
     * end time
     * @var string
     */
    protected $endTime = '00:00';

    /**
     * @inheritDoc
     */
    protected $labels = [
        'time' => 'wcf.date.time',
        'timezone' => 'wcf.date.timezone',
    ];

    /**
     * start time
     * @var string
     */
    protected $startTime = '00:00';

    /**
     * timezone used to evaluate the start/end time
     * @var string
     */
    protected $timezone = 0;

    /**
     * @inheritDoc
     */
    public function getData()
    {
        $data = [];

        if ($this->startTime) {
            $data['startTime'] = $this->startTime;
        }
        if ($this->endTime) {
            $data['endTime'] = $this->endTime;
        }

        if (!empty($data) && $this->timezone) {
            $data['timezone'] = $this->timezone;
        }

        if (!empty($data)) {
            return $data;
        }
    }

    /**
     * @inheritDoc
     */
    public function getHTML()
    {
        $start = WCF::getLanguage()->get('wcf.date.period.start');
        $end = WCF::getLanguage()->get('wcf.date.period.end');

        return <<<HTML
<dl>
	<dt>{$this->getLabel('time')}</dt>
	<dd>
		<input type="datetime" data-ignore-timezone="1" data-time-only="1" id="startTime" name="startTime" value="{$this->startTime}" placeholder="{$start}">
		<input type="datetime" data-ignore-timezone="1" data-time-only="1" id="endTime" name="endTime" value="{$this->endTime}" placeholder="{$end}">
		{$this->getDescriptionElement('time')}
		{$this->getErrorMessageElement('time')}
	</dd>
</dl>
<dl>
	<dt>{$this->getLabel('timezone')}</dt>
	<dd>
		{$this->getTimezoneFieldElement()}
		{$this->getDescriptionElement('timezone')}
		{$this->getErrorMessageElement('timezone')}
	</dd>
</dl>
HTML;
    }

    /**
     * Returns the select element with all available timezones.
     *
     * @return  string
     */
    protected function getTimezoneFieldElement()
    {
        $fieldElement = '<select name="timezone" id="timezone"><option value="0"' . ($this->timezone ? ' selected' : '') . '>' . WCF::getLanguage()->get('wcf.date.timezone.user') . '</option>';
        foreach (DateUtil::getAvailableTimezones() as $timezone) {
            $fieldElement .= '<option value="' . $timezone . '"' . ($this->timezone === $timezone ? ' selected' : '') . '>' . WCF::getLanguage()->get('wcf.date.timezone.' . \str_replace('/',
                        '.', \strtolower($timezone))) . '</option>';
        }
        $fieldElement .= '</select>';

        return $fieldElement;
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        if (isset($_POST['endTime'])) {
            $this->endTime = StringUtil::trim($_POST['endTime']);
        }
        if (isset($_POST['startTime'])) {
            $this->startTime = StringUtil::trim($_POST['startTime']);
        }
        if (isset($_POST['timezone'])) {
            $this->timezone = StringUtil::trim($_POST['timezone']);
        }
    }

    /**
     * @inheritDoc
     */
    public function reset()
    {
        $this->endTime = '00:00';
        $this->startTime = '00:00';
        $this->timezone = 0;
    }

    /**
     * @inheritDoc
     */
    public function setData(Condition $condition)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $endTime = $condition->endTime;
        if ($endTime) {
            $this->endTime = $endTime;
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $startTime = $condition->startTime;
        if ($startTime) {
            $this->startTime = $startTime;
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $timezone = $condition->timezone;
        if ($timezone) {
            $this->timezone = $timezone;
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if ($this->startTime == '00:00' && $this->endTime == '00:00') {
            $this->startTime = $this->endTime = '';

            return;
        }

        $startDateTime = $endDateTime = null;
        if ($this->startTime) {
            $startDateTime = \DateTime::createFromFormat('H:i', $this->startTime);
            if ($startDateTime === false) {
                $this->errorMessages['time'] = 'wcf.date.startTime.error.invalid';

                throw new UserInputException('startTime', 'invalid');
            }
        }
        if ($this->endTime) {
            $endDateTime = \DateTime::createFromFormat('H:i', $this->endTime);
            if ($endDateTime === false) {
                $this->errorMessages['time'] = 'wcf.date.endTime.error.invalid';

                throw new UserInputException('endTime', 'invalid');
            }
        }

        if ($startDateTime !== null && $endDateTime !== null) {
            if ($startDateTime->getTimestamp() >= $endDateTime->getTimestamp()) {
                $this->errorMessages['time'] = 'wcf.date.endTime.error.beforeStartTime';

                throw new UserInputException('endTime', 'beforeStartTime');
            }
        }

        if ($this->timezone && !\in_array($this->timezone, DateUtil::getAvailableTimezones())) {
            $this->errorMessages['timezone'] = 'wcf.global.form.error.noValidSelection';

            throw new UserInputException('timezone', 'noValidSelection');
        }
    }

    /**
     * @inheritDoc
     */
    public function showContent(Condition $condition)
    {
        $timezone = WCF::getUser()->getTimeZone();
        /** @noinspection PhpUndefinedFieldInspection */
        $conditionTimezone = $condition->timezone;
        if ($conditionTimezone) {
            $timezone = new \DateTimeZone($conditionTimezone);
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $startTime = $condition->startTime;
        if ($startTime) {
            $dateTime = \DateTime::createFromFormat('H:i', $startTime, $timezone);
            if ($dateTime->getTimestamp() > TIME_NOW) {
                return false;
            }
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $endTime = $condition->endTime;
        if ($endTime) {
            $dateTime = \DateTime::createFromFormat('H:i', $endTime, $timezone);
            if ($dateTime->getTimestamp() < TIME_NOW) {
                return false;
            }
        }

        return true;
    }
}
