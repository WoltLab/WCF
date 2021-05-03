<?php

use wcf\data\language\item\LanguageItemAction;
use wcf\data\language\item\LanguageItemList;

$languageItems = [
    // The empty phrase. See: https://github.com/WoltLab/WCF/commit/9e280cb5c1dd3e398a3096b55f6d88be4baa6923
    '',
    'wcf.acp.group.option.user.attachment.allowedExtensions.description',
    'wcf.acp.group.option.user.contactForm.attachment.allowedExtensions.description',
    'wcf.acp.group.option.user.signature.attachment.allowedExtensions.description',
];

$languageItemList = new LanguageItemList();
$languageItemList->getConditionBuilder()->add('languageItem IN (?)', [$languageItems]);
$languageItemList->readObjects();

(new LanguageItemAction($languageItemList->getObjects(), 'delete'))->executeAction();
