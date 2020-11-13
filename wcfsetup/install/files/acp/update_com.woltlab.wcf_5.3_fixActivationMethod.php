<?php
use wcf\data\option\OptionEditor;

// Legacy versions handled disabled option values incorrectly, causing them to be unset
// instead of maintaining the previously selected value. As a result, installations that
// disabled the registration in WoltLab Suite 3.1 without enabling it again may have an
// empty string value set for the activation method.
//
// See https://github.com/WoltLab/WCF/issues/3723
if (!is_numeric(REGISTER_ACTIVATION_METHOD)) {
	OptionEditor::import([
		'register_activation_method' => 0,
	]);
}
