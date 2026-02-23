INSERT INTO wcf1_package_installation_plugin (packageID, pluginName, priority, className) VALUES (1, 'packageInstallationPlugin', 1, 'wcf\\system\\package\\plugin\\PIPPackageInstallationPlugin');

-- default user groups
INSERT INTO wcf1_user_group (groupID, groupName, groupDescription, groupType) VALUES (1, 'wcf.acp.group.group1', '', 1); -- Everyone
INSERT INTO wcf1_user_group (groupID, groupName, groupDescription, groupType) VALUES (2, 'wcf.acp.group.group2', '', 2); -- Guests
INSERT INTO wcf1_user_group (groupID, groupName, groupDescription, groupType, priority) VALUES (3, 'wcf.acp.group.group3', '', 3, 10); -- Registered Users
INSERT INTO wcf1_user_group (groupID, groupName, groupDescription, groupType, priority, showOnTeamPage) VALUES (4, 'wcf.acp.group.group4', '', 9, 1000, 1); -- Administrators
INSERT INTO wcf1_user_group (groupID, groupName, groupDescription, groupType, priority, showOnTeamPage) VALUES (5, 'wcf.acp.group.group5', '', 4, 100, 1); -- Moderators

-- default user group options
INSERT INTO wcf1_user_group_option (packageID, optionID, optionName, categoryName, optionType, defaultValue, showOrder, usersOnly) VALUES (1, 1, 'admin.general.canUseAcp', 'admin.general', 'boolean', '0', 1, 1);
INSERT INTO wcf1_user_group_option (packageID, optionID, optionName, categoryName, optionType, defaultValue, showOrder, usersOnly) VALUES (1, 2, 'admin.configuration.package.canInstallPackage', 'admin.configuration.package', 'boolean', '0', 1, 1);
INSERT INTO wcf1_user_group_option (packageID, optionID, optionName, categoryName, optionType, defaultValue, showOrder, usersOnly) VALUES (1, 3, 'admin.user.canEditGroup', 'admin.user.group', 'boolean', '0', 1, 1);

-- default user group option values
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (1, 1, '0');	-- Everyone
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (1, 2, '0');	-- Everyone
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (1, 3, '0');	-- Everyone
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (4, 1, '1');	-- Administrators
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (4, 2, '1');	-- Administrators
INSERT INTO wcf1_user_group_option_value (groupID, optionID, optionValue) VALUES (4, 3, '1');	-- Administrators

-- default update servers
INSERT INTO wcf1_package_update_server (serverURL, status, isDisabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('https://update.woltlab.com/6.0/', 'online', 0, NULL, 0, '', '');
INSERT INTO wcf1_package_update_server (serverURL, status, isDisabled, errorMessage, lastUpdateTime, loginUsername, loginPassword) VALUES ('https://store.woltlab.com/6.0/', 'online', 0, NULL, 0, '', '');

-- style default values
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('individualScss', '', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('messageSidebarOrientation', 'left', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('overrideScss', '', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('pageLogo', '', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('pageLogoWidth', '281', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('pageLogoHeight', '40', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('pageLogoMobile', '', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('useFluidLayout', '1', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfButtonBackground', 'rgba(207, 216, 220, 1)', 'rgba(47, 57, 76, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfButtonBackgroundActive', 'rgba(120, 144, 156, 1)', 'rgba(37, 45, 60, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfButtonDisabledBackground', 'rgba(223, 223, 223, 1)', 'rgba(38, 39, 42, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfButtonDisabledText', 'rgba(165, 165, 165, 1)', 'rgba(112, 115, 118, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfButtonPrimaryBackground', 'rgba(29, 122, 197, 1)', 'rgba(1, 87, 155, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfButtonPrimaryBackgroundActive', 'rgba(26, 107, 173, 1)', 'rgba(1, 75, 132, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfButtonPrimaryText', 'rgba(255, 255, 255, 1)', 'rgba(231, 236, 245, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfButtonPrimaryTextActive', 'rgba(255, 255, 255, 1)', 'rgba(231, 236, 245, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfButtonText', 'rgba(33, 33, 33, 1)', 'rgba(230, 231, 234, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfButtonTextActive', 'rgba(255, 255, 255, 1)', 'rgba(230, 231, 234, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfContentBackground', 'rgba(250, 250, 250, 1)', 'rgba(26, 29, 33, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfContentBorder', 'rgba(65, 121, 173, 1)', 'rgba(98, 113, 136, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfContentBorderInner', 'rgba(224, 224, 224, 1)', 'rgba(54, 55, 59, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfContentContainerBackground', 'rgba(255, 255, 255, 1)', 'rgba(34, 37, 41, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfContentContainerBorder', 'rgba(236, 241, 247, 1)', 'rgba(54, 55, 59, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfContentDimmedLink', 'rgba(52, 73, 94, 1)', 'rgba(29, 155, 209, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfContentDimmedLinkActive', 'rgba(52, 73, 94, 1)', 'rgba(64, 179, 228, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfContentDimmedText', 'rgba(113, 117, 122, 1)', 'rgba(138, 140, 143, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfContentHeadlineBorder', 'rgba(238, 238, 238, 1)', 'rgba(54, 55, 59, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfContentHeadlineLink', 'rgba(58, 58, 61, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfContentHeadlineLinkActive', 'rgba(58, 58, 61, 1)', 'rgba(158, 158, 158, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfContentHeadlineText', 'rgba(58, 58, 61, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfContentLink', 'rgba(38, 113, 166, 1)', 'rgba(29, 155, 209, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfContentLinkActive', 'rgba(22, 81, 124, 1)', 'rgba(64, 179, 228, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfContentText', 'rgba(58, 58, 61, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfDropdownBackground', 'rgba(255, 255, 255, 1)', 'rgba(34, 37, 41, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfDropdownBackgroundActive', 'rgba(238, 238, 238, 1)', 'rgba(44, 49, 59, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfDropdownBorderInner', 'rgba(238, 238, 238, 1)', 'rgba(54, 55, 59, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfDropdownLink', 'rgba(33, 33, 33, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfDropdownLinkActive', 'rgba(33, 33, 33, 1)', 'rgba(239, 239, 239, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfDropdownText', 'rgba(33, 33, 33, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfEditorButtonBackground', 'rgba(58, 109, 156, 1)', 'rgba(47, 57, 76, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfEditorButtonBackgroundActive', 'rgba(36, 66, 95, 1)', 'rgba(37, 45, 60, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfEditorButtonText', 'rgba(255, 255, 255, 1)', 'rgba(230, 231, 234, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfEditorButtonTextActive', 'rgba(255, 255, 255, 1)', 'rgba(230, 231, 234, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfEditorButtonTextDisabled', 'rgba(165, 165, 165, 1)', 'rgba(118, 125, 137, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfEditorTableBorder', 'rgba(221, 221, 221, 1)', 'rgba(221, 221, 221, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFontFamilyFallback', 'system', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFontFamilyGoogle', '', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFontLineHeight', '1.48', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFontSizeDefault', '15px', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFontSizeHeadline', '18px', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFontSizeSection', '23px', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFontSizeSmall', '12px', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFontSizeTitle', '28px', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterBackground', 'rgba(58, 109, 156, 1)', 'rgba(30, 39, 52, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterBoxBackground', 'rgba(236, 239, 241, 1)', 'rgba(26, 34, 45, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterBoxHeadlineLink', 'rgba(58, 58, 61, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterBoxHeadlineLinkActive', 'rgba(58, 58, 61, 1)', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterBoxHeadlineText', 'rgba(58, 58, 61, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterBoxLink', 'rgba(38, 113, 166, 1)', 'rgba(29, 155, 209, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterBoxLinkActive', 'rgba(22, 81, 124, 1)', 'rgba(64, 179, 228, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterBoxText', 'rgba(58, 58, 61, 1)', 'rgba(158, 158, 158, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterCopyrightBackground', 'rgba(50, 92, 132, 1)', 'rgba(36, 46, 61, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterCopyrightLink', 'rgba(217, 220, 222, 1)', 'rgba(182, 184, 185, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterCopyrightLinkActive', 'rgba(255, 255, 255, 1)', 'rgba(217, 220, 222, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterCopyrightText', 'rgba(217, 220, 222, 1)', 'rgba(182, 184, 185, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterHeadlineLink', 'rgba(255, 255, 255, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterHeadlineLinkActive', 'rgba(255, 255, 255, 1)', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterHeadlineText', 'rgba(233, 235, 236, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterLink', 'rgba(255, 255, 255, 1)', 'rgba(30, 163, 220, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterLinkActive', 'rgba(255, 255, 255, 1)', 'rgba(75, 184, 231, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfFooterText', 'rgba(217, 220, 222, 1)', 'rgba(158, 158, 158, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderBackground', 'rgba(58, 109, 156, 1)', 'rgba(30, 39, 52, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderText', 'rgba(255, 255, 255, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderLink', 'rgba(255, 255, 255, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderLinkActive', 'rgba(255, 255, 255, 1)', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderMenuBackground', 'rgba(50, 92, 132, 1)', 'rgba(36, 46, 61, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderMenuLinkBackground', 'rgba(43, 79, 113, 1)', 'rgba(36, 46, 61, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderMenuLinkBackgroundActive', 'rgba(36, 66, 95, 1)', 'rgba(43, 56, 74, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderMenuLink', 'rgba(255, 255, 255, 1)', 'rgba(183, 186, 191, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderMenuLinkActive', 'rgba(255, 255, 255, 1)', 'rgba(224, 227, 230, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderMenuDropdownBackground', 'rgba(36, 66, 95, 1)', 'rgba(43, 56, 74, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderMenuDropdownBackgroundActive', 'rgba(65, 121, 173, 1)', 'rgba(38, 49, 64, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderMenuDropdownLink', 'rgba(255, 255, 255, 1)', 'rgba(224, 227, 230, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderMenuDropdownLinkActive', 'rgba(255, 255, 255, 1)', 'rgba(229, 231, 234, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderSearchBoxBackground', 'rgba(50, 92, 132, 1)', 'rgba(36, 46, 61, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderSearchBoxBackgroundActive', 'rgba(50, 92, 132, 1)', 'rgba(43, 56, 74, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderSearchBoxText', 'rgba(255, 255, 255, 1)', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderSearchBoxTextActive', 'rgba(255, 255, 255, 1)', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderSearchBoxPlaceholder', 'rgba(207, 207, 207, 1)', 'rgba(207, 207, 207, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderSearchBoxPlaceholderActive', 'rgba(207, 207, 207, 1)', 'rgba(207, 207, 207, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfInputBackground', 'rgba(241, 246, 251, 1)', 'rgba(26, 29, 33, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfInputBackgroundActive', 'rgba(241, 246, 251, 1)', 'rgba(26, 29, 33, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfInputBorder', 'rgba(176, 200, 224, 1)', 'rgba(87, 88, 86, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfInputBorderActive', 'rgba(41, 128, 185, 1)', 'rgba(173, 174, 175, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfInputDisabledBackground', 'rgba(245, 245, 245, 1)', 'rgba(34, 37, 41, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfInputDisabledBorder', 'rgba(174, 176, 179, 1)', 'rgba(56, 56, 57, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfInputDisabledText', 'rgba(125, 130, 100, 1)', 'rgba(118, 119, 121, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfInputLabel', 'rgba(59, 109, 169, 1)', 'rgba(144, 164, 174, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfInputText', 'rgba(58, 58, 61, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfInputTextActive', 'rgba(58, 58, 61, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfInputPlaceholder', 'rgba(169, 169, 169, 1)', 'rgba(122, 123, 125, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfInputPlaceholderActive', 'rgba(204, 204, 204, 1)', 'rgba(122, 123, 125, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfLayoutFixedWidth', '1200px', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfLayoutMaxWidth', '1400px', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfLayoutMinWidth', '1000px', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfNavigationBackground', 'rgba(236, 239, 241, 1)', 'rgba(26, 34, 45, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfNavigationLink', 'rgba(58, 58, 61, 1)', 'rgba(179, 182, 185, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfNavigationLinkActive', 'rgba(58, 58, 61, 1)', 'rgba(205, 207, 208, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfNavigationText', 'rgba(170, 170, 170, 1)', 'rgba(179, 182, 185, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfPageThemeColor', '', NULL);
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfSidebarBackground', 'rgba(236, 241, 247, 1)', 'rgba(30, 39, 52, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfSidebarDimmedLink', 'rgba(58, 58, 61, 1)', 'rgba(29, 155, 209, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfSidebarDimmedLinkActive', 'rgba(58, 58, 61, 1)', 'rgba(64, 179, 228, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfSidebarDimmedText', 'rgba(105, 109, 114, 1)', 'rgba(139, 141, 145, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfSidebarHeadlineLink', 'rgba(58, 58, 61, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfSidebarHeadlineLinkActive', 'rgba(58, 58, 61, 1)', 'rgba(158, 158, 158, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfSidebarHeadlineText', 'rgba(58, 58, 61, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfSidebarLink', 'rgba(38, 113, 166, 1)', 'rgba(29, 155, 209, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfSidebarLinkActive', 'rgba(22, 81, 124, 1)', 'rgba(64, 179, 228, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfSidebarText', 'rgba(58, 58, 61, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusErrorBackground', 'rgba(242, 222, 222, 1)', 'rgba(116, 38, 30, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusErrorBorder', 'rgba(235, 204, 204, 1)', 'rgba(139, 46, 36, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusErrorLink', 'rgba(132, 53, 52, 1)', 'rgba(201, 170, 165, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusErrorLinkActive', 'rgba(132, 53, 52, 1)', 'rgba(201, 170, 165, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusErrorText', 'rgba(169, 68, 66, 1)', 'rgba(201, 170, 165, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusInfoBackground', 'rgba(217, 237, 247, 1)', 'rgba(12, 81, 92, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusInfoBorder', 'rgba(188, 223, 241, 1)', 'rgba(14, 97, 110, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusInfoLink', 'rgba(36, 82, 105, 1)', 'rgba(171, 191, 196, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusInfoLinkActive', 'rgba(36, 82, 105, 1)', 'rgba(171, 191, 196, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusInfoText', 'rgba(49, 112, 143, 1)', 'rgba(171, 191, 196, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusSuccessBackground', 'rgba(223, 240, 216, 1)', 'rgba(0, 94, 70, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusSuccessBorder', 'rgba(208, 233, 198, 1)', 'rgba(0, 113, 84, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusSuccessLink', 'rgba(43, 84, 44, 1)', 'rgba(180, 203, 195, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusSuccessLinkActive', 'rgba(43, 84, 44, 1)', 'rgba(180, 203, 195, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusSuccessText', 'rgba(60, 118, 61, 1)', 'rgba(180, 203, 195, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusWarningBackground', 'rgba(252, 248, 227, 1)', 'rgba(122, 78, 9, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusWarningBorder', 'rgba(250, 242, 204, 1)', 'rgba(146, 94, 11, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusWarningLink', 'rgba(102, 81, 44, 1)', 'rgba(221, 209, 194, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusWarningLinkActive', 'rgba(102, 81, 44, 1)', 'rgba(221, 209, 194, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfStatusWarningText', 'rgba(138, 109, 59, 1)', 'rgba(221, 209, 194, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfTabularBoxBackgroundActive', 'rgba(242, 242, 242, 1)', 'rgba(30, 33, 36, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfTabularBoxBorderInner', 'rgba(238, 238, 238, 1)', 'rgba(54, 55, 59, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfTabularBoxHeadline', 'rgba(38, 113, 166, 1)', 'rgba(29, 155, 209, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfTabularBoxHeadlineActive', 'rgba(22, 81, 124, 1)', 'rgba(64, 179, 228, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfTextShadowDark', 'rgba(0, 0, 0, .8)', 'rgba(0, 0, 0, .8)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfTextShadowLight', 'rgba(255, 255, 255, .8)', 'rgba(255, 255, 255, .8)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfTooltipBackground', 'rgba(0, 0, 0, .8)', 'rgba(0, 0, 0, .8)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfTooltipText', 'rgba(255, 255, 255, 1)', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfUserMenuBackground', 'rgba(255, 255, 255, 1)', 'rgba(34, 37, 41, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfUserMenuBackgroundActive', 'rgba(239, 239, 239, 1)', 'rgba(44, 49, 59, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfUserMenuText', 'rgba(58, 58, 61, 1)', 'rgba(209, 210, 211, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfUserMenuTextActive', 'rgba(58, 58, 61, 1)', 'rgba(239, 239, 239, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfUserMenuTextDimmed', 'rgba(108, 108, 108, 1)', 'rgba(149, 152, 156, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfUserMenuIndicator', 'rgba(49, 138, 220, 1)', 'rgba(49, 138, 220, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfUserMenuBorder', 'rgba(221, 221, 221, 1)', 'rgba(54, 55, 59, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfSidebarBorder', 'rgba(236, 241, 247, 0)', 'rgba(57, 65, 77, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('individualScssDarkMode', '', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfHeaderMenuDropdownBorder', 'rgba(36, 66, 95, 1)', 'rgba(36, 66, 95, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfToggleButtonBackground', 'rgba(105, 109, 114, 1)', 'rgba(89, 89, 89, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfToggleButtonBackgroundActive', 'rgba(60, 118, 61, 1)', 'rgba(0, 113, 84, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfToggleButtonSliderBackground', 'rgba(250, 250, 250, 1)', 'rgba(203, 203, 203, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue, defaultValueDarkMode) VALUES('wcfToggleButtonSliderBackgroundActive', 'rgba(250, 250, 250, 1)', 'rgba(203, 203, 203, 1)');

-- System template groups
INSERT INTO wcf1_template_group (parentTemplateGroupID, templateGroupName, templateGroupFolderName) VALUES (NULL, 'wcf.acp.template.group.email', '_wcf_email/');
INSERT INTO wcf1_template_group (parentTemplateGroupID, templateGroupName, templateGroupFolderName) VALUES (NULL, 'wcf.acp.template.group.shared', '_wcf_shared/');

-- default options: subject and message
INSERT INTO wcf1_contact_option (optionID, optionTitle, optionDescription, optionType, configuration, showOrder, originIsSystem) VALUES (1, 'wcf.contact.option1', 'wcf.contact.optionDescription1', 'text', '{\"required\":1}', 1, 1);
INSERT INTO wcf1_contact_option (optionID, optionTitle, optionDescription, optionType, configuration, showOrder, originIsSystem) VALUES (2, 'wcf.contact.option2', '', 'textarea', '{\"required\":1}', 1, 1);

-- default recipient: site administrator
INSERT INTO wcf1_contact_recipient (recipientID, name, email, isAdministrator, originIsSystem) VALUES (1, 'wcf.contact.recipient.name1', '', 1, 1);
