<?php

namespace wcf\system\endpoint\error;

enum ControllerError: string
{
    case ParameterTypeComplex = 'parameter_type_complex';
    case ParameterTypeUnknown = 'parameter_type_unknown';
    case ParameterWithoutType = 'parameter_without_type';
    case ParameterNotInUri = 'parameter_not_in_uri';
}
