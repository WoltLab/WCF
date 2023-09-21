<?php

namespace wcf\system\label;

use wcf\data\label\group\ViewableLabelGroup;
use wcf\data\label\Label;
use wcf\util\JSON;
use wcf\util\StringUtil;

final class LabelPicker
{
    public function __construct(public readonly ViewableLabelGroup $labelGroup)
    {
    }

    public function toHtml(): string
    {
        $labels = [];
        foreach ($this->labelGroup as $label) {
            \assert($label instanceof Label);

            $labels[] = [$label->labelID, $label->render()];
        }

        return \sprintf(
            '<woltlab-core-label-picker group-id="%d" title="%s" labels="%s"></woltlab-core-label-picker>',
            $this->labelGroup->groupID,
            $this->labelGroup->getTitle(),
            StringUtil::encodeHTML(JSON::encode($labels)),
        );
    }
}
