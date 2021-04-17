<?php

use wcf\data\language\item\LanguageItemAction;
use wcf\data\language\item\LanguageItemList;

$languageItems = [
    'wcf.acp.group.option.user.attachment.allowedExtensions.description',
    'wcf.acp.group.option.user.contactForm.attachment.allowedExtensions.description',
    'wcf.acp.group.option.user.signature.attachment.allowedExtensions.description',
];

$languageItemList = new LanguageItemList();
$languageItemList->getConditionBuilder()->add('languageItem IN (?)', [$languageItems]);
$languageItemList->readObjects();

(new LanguageItemAction($languageItemList->getObjects(), 'delete'))->executeAction();
