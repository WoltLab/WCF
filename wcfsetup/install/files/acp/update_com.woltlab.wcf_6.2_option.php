<?php

namespace wcf\acp;

use wcf\data\option\OptionEditor;

/**
 * Opt-out of the new image conversion and EXIF removal feature if the settings
 * for the image autoscaling is set to preserve the file type. This isn’t
 * exactly the same but still close enough as a preset.
 */

if (\ATTACHMENT_IMAGE_AUTOSCALE_FILE_TYPE !== 'keep') {
    return;
}

OptionEditor::import([
    'image_convert_format' => 'keep',
    'image_strip_exif' => 0,
]);
