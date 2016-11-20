/* foreign keys */
ALTER TABLE wcf1_acl_simple_to_user ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_acl_simple_to_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_acl_simple_to_group ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_acl_simple_to_group ADD FOREIGN KEY (groupID) REFERENCES wcf1_user_group (groupID) ON DELETE CASCADE;

ALTER TABLE wcf1_acp_session_virtual ADD FOREIGN KEY (sessionID) REFERENCES wcf1_acp_session (sessionID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wcf1_article ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_article ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE SET NULL;

ALTER TABLE wcf1_article_content ADD FOREIGN KEY (articleID) REFERENCES wcf1_article (articleID) ON DELETE CASCADE;
ALTER TABLE wcf1_article_content ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE SET NULL;
ALTER TABLE wcf1_article_content ADD FOREIGN KEY (imageID) REFERENCES wcf1_media (mediaID) ON DELETE SET NULL;

ALTER TABLE wcf1_box ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_box ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_box ADD FOREIGN KEY (menuID) REFERENCES wcf1_menu (menuID) ON DELETE CASCADE;
ALTER TABLE wcf1_box ADD FOREIGN KEY (linkPageID) REFERENCES wcf1_page (pageID) ON DELETE SET NULL;

ALTER TABLE wcf1_box_content ADD FOREIGN KEY (boxID) REFERENCES wcf1_box (boxID) ON DELETE CASCADE;
ALTER TABLE wcf1_box_content ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE CASCADE;
ALTER TABLE wcf1_box_content ADD FOREIGN KEY (imageID) REFERENCES wcf1_media (mediaID) ON DELETE SET NULL;

ALTER TABLE wcf1_box_to_page ADD FOREIGN KEY (boxID) REFERENCES wcf1_box (boxID) ON DELETE CASCADE;
ALTER TABLE wcf1_box_to_page ADD FOREIGN KEY (pageID) REFERENCES wcf1_page (pageID) ON DELETE CASCADE;

-- re-add dropped foreign key
ALTER TABLE wcf1_event_listener ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_media ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_media ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE SET NULL;

ALTER TABLE wcf1_media_content ADD FOREIGN KEY (mediaID) REFERENCES wcf1_media (mediaID) ON DELETE CASCADE;
ALTER TABLE wcf1_media_content ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE CASCADE;

ALTER TABLE wcf1_menu ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_menu_item ADD FOREIGN KEY (menuID) REFERENCES wcf1_menu (menuID) ON DELETE CASCADE;
ALTER TABLE wcf1_menu_item ADD FOREIGN KEY (parentItemID) REFERENCES wcf1_menu_item (itemID) ON DELETE SET NULL;
ALTER TABLE wcf1_menu_item ADD FOREIGN KEY (pageID) REFERENCES wcf1_page (pageID) ON DELETE CASCADE;
ALTER TABLE wcf1_menu_item ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;

ALTER TABLE wcf1_page ADD FOREIGN KEY (parentPageID) REFERENCES wcf1_page (pageID) ON DELETE SET NULL;
ALTER TABLE wcf1_page ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_page ADD FOREIGN KEY (applicationPackageID) REFERENCES wcf1_package (packageID) ON DELETE SET NULL;

ALTER TABLE wcf1_page_content ADD FOREIGN KEY (pageID) REFERENCES wcf1_page (pageID) ON DELETE CASCADE;
ALTER TABLE wcf1_page_content ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE CASCADE;

ALTER TABLE wcf1_session ADD FOREIGN KEY (pageID) REFERENCES wcf1_page (pageID) ON DELETE SET NULL;
ALTER TABLE wcf1_session ADD FOREIGN KEY (parentPageID) REFERENCES wcf1_page (pageID) ON DELETE SET NULL;

-- remove obsolete update servers
DELETE FROM wcf1_package_update_server WHERE serverURL IN ('http://update.woltlab.com/maelstrom/', 'http://store.woltlab.com/maelstrom/', 'http://update.woltlab.com/typhoon/', 'http://store.woltlab.com/typhoon/');

-- style default values
DELETE FROM wcf1_style;
DELETE FROM wcf1_style_variable;
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('individualScss', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('messageSidebarOrientation', 'left');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('overrideScss', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('pageLogo', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('pageLogoWidth', '281');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('pageLogoHeight', '40');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('pageLogoMobile', '');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('useFluidLayout', '1');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('useGoogleFont', '1');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonBackground', 'rgba(207, 216, 220, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonBackgroundActive', 'rgba(120, 144, 156, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonDisabledBackground', 'rgba(223, 223, 223, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonDisabledText', 'rgba(165, 165, 165, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonPrimaryBackground', 'rgba(33, 150, 243, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonPrimaryBackgroundActive', 'rgba(26, 119, 201, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonPrimaryText', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonPrimaryTextActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonText', 'rgba(33, 33, 33, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfButtonTextActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentBackground', 'rgba(250, 250, 250, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentBorder', 'rgba(65, 121, 173, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentBorderInner', 'rgba(224, 224, 224, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentDimmedLink', 'rgba(52, 73, 94, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentDimmedLinkActive', 'rgba(52, 73, 94, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentDimmedText', 'rgba(125, 130, 135, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentHeadlineBorder', 'rgba(238, 238, 238, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentHeadlineLink', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentHeadlineLinkActive', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentHeadlineText', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentLink', 'rgba(230, 81, 0, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentLinkActive', 'rgba(191, 54, 12, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfContentText', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownBackground', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownBackgroundActive', 'rgba(238, 238, 238, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownBorderInner', 'rgba(238, 238, 238, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownLink', 'rgba(33, 33, 33, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownLinkActive', 'rgba(33, 33, 33, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfDropdownText', 'rgba(33, 33, 33, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFontFamilyFallback', '"Segoe UI", "DejaVu Sans", "Lucida Grande", "Helvetica", sans-serif');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFontFamilyGoogle', 'Open Sans');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFontLineHeight', '1.48');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFontSizeDefault', '14px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFontSizeHeadline', '18px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFontSizeSection', '23px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFontSizeSmall', '12px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFontSizeTitle', '28px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterBackground', 'rgba(58, 109, 156, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterBoxBackground', 'rgba(236, 239, 241, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterBoxHeadlineLink', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterBoxHeadlineLinkActive', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterBoxHeadlineText', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterBoxLink', 'rgba(230, 81, 0, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterBoxLinkActive', 'rgba(191, 54, 12, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterBoxText', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterCopyrightBackground', 'rgba(50, 92, 132, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterCopyrightLink', 'rgba(217, 220, 222, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterCopyrightLinkActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterCopyrightText', 'rgba(217, 220, 222, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterHeadlineLink', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterHeadlineLinkActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterHeadlineText', 'rgba(189, 195, 199, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterLink', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterLinkActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfFooterText', 'rgba(217, 220, 222, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderBackground', 'rgba(58, 109, 156, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderText', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderLink', 'rgba(255, 255, 255, .8)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderLinkActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuBackground', 'rgba(50, 92, 132, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuLinkBackground', 'rgba(43, 79, 113, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuLinkBackgroundActive', 'rgba(36, 66, 95, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuLink', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuLinkActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuDropdownBackground', 'rgba(36, 66, 95, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuDropdownBackgroundActive', 'rgba(65, 121, 173, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuDropdownBorder', 'rgba(55, 73, 95, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuDropdownLink', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderMenuDropdownLinkActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderSearchBoxBackground', 'rgba(50, 92, 132, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderSearchBoxBackgroundActive', 'rgba(50, 92, 132, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderSearchBoxText', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderSearchBoxTextActive', 'rgba(255, 255, 255, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderSearchBoxPlaceholder', 'rgba(207, 207, 207, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfHeaderSearchBoxPlaceholderActive', 'rgba(207, 207, 207, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputBackground', 'rgba(241, 246, 251, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputBackgroundActive', 'rgba(241, 246, 251, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputBorder', 'rgba(176, 200, 224, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputBorderActive', 'rgba(41, 128, 185, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputDisabledBackground', 'rgba(245, 245, 245, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputDisabledBorder', 'rgba(174, 176, 179, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputDisabledText', 'rgba(125, 130, 100, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputLabel', 'rgba(59, 109, 169, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputText', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputTextActive', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputPlaceholder', 'rgba(169, 169, 169, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfInputPlaceholderActive', 'rgba(204, 204, 204, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfLayoutFixedWidth', '1200px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfLayoutMaxWidth', '1400px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfLayoutMinWidth', '1240px');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfNavigationBackground', 'rgba(236, 239, 241, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfNavigationLink', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfNavigationLinkActive', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfNavigationText', 'rgba(170, 170, 170, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarBackground', 'rgba(236, 241, 247, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarDimmedLink', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarDimmedLinkActive', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarDimmedText', 'rgba(127, 140, 141, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarHeadlineLink', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarHeadlineLinkActive', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarHeadlineText', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarLink', 'rgba(230, 81, 0, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarLinkActive', 'rgba(191, 54, 12, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfSidebarText', 'rgba(44, 62, 80, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusErrorBackground', 'rgba(242, 222, 222, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusErrorBorder', 'rgba(235, 204, 204, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusErrorLink', 'rgba(169, 68, 66, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusErrorLinkActive', 'rgba(169, 68, 66, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusErrorText', 'rgba(169, 68, 66, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusInfoBackground', 'rgba(217, 237, 247, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusInfoBorder', 'rgba(188, 223, 241, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusInfoLink', 'rgba(49, 112, 143, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusInfoLinkActive', 'rgba(49, 112, 143, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusInfoText', 'rgba(49, 112, 143, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusSuccessBackground', 'rgba(223, 240, 216, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusSuccessBorder', 'rgba(208, 233, 198, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusSuccessLink', 'rgba(60, 118, 61, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusSuccessLinkActive', 'rgba(60, 118, 61, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusSuccessText', 'rgba(60, 118, 61, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusWarningBackground', 'rgba(252, 248, 227, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusWarningBorder', 'rgba(250, 242, 204, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusWarningLink', 'rgba(138, 109, 59, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusWarningLinkActive', 'rgba(138, 109, 59, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfStatusWarningText', 'rgba(138, 109, 59, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTabularBoxBackgroundActive', 'rgba(242, 242, 242, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTabularBoxBorderInner', 'rgba(238, 238, 238, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTabularBoxHeadline', 'rgba(65, 121, 173, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTabularBoxHeadlineActive', 'rgba(230, 81, 0, 1)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTextShadowDark', 'rgba(0, 0, 0, .8)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTextShadowLight', 'rgba(255, 255, 255, .8)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTooltipBackground', 'rgba(0, 0, 0, .8)');
INSERT INTO wcf1_style_variable (variableName, defaultValue) VALUES ('wcfTooltipText', 'rgba(255, 255, 255, 1)');

-- Email template group
INSERT INTO wcf1_template_group (parentTemplateGroupID, templateGroupName, templateGroupFolderName) VALUES (NULL, 'wcf.acp.template.group.email', '_wcf_email/');

-- media providers
INSERT INTO wcf1_bbcode_media_provider (title, regex, html) VALUES ('YouTube Playlist', 'https?://(?:.+?\\.)?youtu(?:\\.be/|be\\.com/)playlist\\?(?:.*?&)?list=(?P<ID>[a-zA-Z0-9_-]+)', '<div class="videoContainer"><iframe src="https://www.youtube.com/embed/videoseries?list={$ID}" allowfullscreen></iframe></div>');
UPDATE wcf1_bbcode_media_provider SET regex = 'https?://vimeo\\.com/(?:channels/[^/]+/)?(?P<ID>\\d+)' WHERE title = 'Vimeo';
