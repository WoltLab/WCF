-- Force-enable the visibility of *all* pages by setting `allowSpidersToIndex` to `2`.
-- 
-- This value isn't valid by definition, but because it is considered to be a true-ish
-- value, we can use this to imply an "implicit yes" without breaking any checks. Check
-- the PagePackageInstallationPlugin to see what this magic value is good for.
UPDATE wcf1_page SET allowSpidersToIndex = 2 WHERE pageType = 'system';
