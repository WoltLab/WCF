<?php

use wcf\system\application\ApplicationHandler;
use wcf\system\WCF;

$domainName = ApplicationHandler::getInstance()->getApplicationByID(1)->domainName;
foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
    if ($application->domainName !== $domainName) {
        if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
            $message = "Die installierten Apps befinden sich auf unterschiedlichen Domains.";
        } else {
            $message = "The installed apps are running on different domains.";
        }

        throw new \RuntimeException($message);
    }
}
