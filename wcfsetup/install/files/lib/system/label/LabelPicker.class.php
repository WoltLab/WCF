<?php

namespace wcf\system\label;

use wcf\data\label\group\ViewableLabelGroup;
use wcf\data\label\Label;
use wcf\util\JSON;
use wcf\util\StringUtil;

final class LabelPicker
{
    public int $selected = 0;

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
            <<<'EOT'
                <woltlab-core-label-picker
                    selected="%d"
                    id="%s"
                    title="%s"
                    labels="%s"
                    %s
                    data-group-id="%d"
                ></woltlab-core-label-picker>
            EOT,
            $this->selected,
            $this->getId(),
            $this->labelGroup->getTitle(),
            StringUtil::encodeHTML(JSON::encode($labels)),
            $this->labelGroup->multipleSelection ? 'multiple' : '',
            $this->labelGroup->groupID,
        );
    }

    public function getId(): string
    {
        return "labelGroup{$this->labelGroup->groupID}";
    }
}
