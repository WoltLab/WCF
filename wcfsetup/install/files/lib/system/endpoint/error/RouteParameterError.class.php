<?php

namespace wcf\system\endpoint\error;

enum RouteParameterError: string
{
    case ExpectedPositiveInteger = 'expected_positive_integer';
    case ExpectedNonEmptyString = 'expected_non_empty_string';
}
