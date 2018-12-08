-- Force-reset all cronjobs due to a bug in 3.1.5 that caused cronjobs to be accidentally disabled.
UPDATE wcf1_cronjob SET isDisabled = 0, failCount = 0;
