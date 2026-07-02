<?php

namespace wcf\acp;

use wcf\command\style\CreateManifest;
use wcf\data\style\StyleList;

$styleList = new StyleList();
$styleList->readObjects();

foreach ($styleList->getObjects() as $style) {
    // Regenerate the `manifest.json` for all styles to drop the `start_url`.
    $command = new CreateManifest($style);
    $command();
}
