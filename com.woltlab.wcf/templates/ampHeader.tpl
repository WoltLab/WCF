<!doctype html>
<html amp lang="{@$__wcf->language->getFixedLanguageCode()}">
	<head>
		<meta charset="utf-8">
		<title>{@$pageTitle} - {PAGE_TITLE|language}</title>
		<link rel="canonical" href="{$regularCanonicalURL}">
		<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
		{if !$headContent|empty}
			{@$headContent}
		{/if}
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600">
		<style amp-custom>
			html {
				box-sizing: border-box;
			}
			
			*,
			*::before,
			*::after {
				box-sizing: inherit;
			}
			
			body {
				background-color: {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfContentBackground')};
				font-family: "Open Sans", "Segoe UI", "Lucida Grande", "Helvetica", sans-serif;
				font-size: 14px;
			}
			
			a {
				color: {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfContentLink')};
				text-decoration: none;
			}
			
			a:hover {
				color: {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfContentLinkActive')};
				text-decoration: none;
			}
			
			button {
				background: none;
				border: none;
				color: inherit;
				display: block;
				font-family: "Open Sans", "Segoe UI", "Lucida Grande", "Helvetica", sans-serif;
				font-size: 14px;
				margin-top: 5px;
				outline: 0;
				overflow: hidden;
				padding: 0;
				text-transform: uppercase;
			}
			
			.header {
				background-color: {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfHeaderBackground')};
				color: {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfHeaderText')};
				padding: 20px;
			}
			
			.header button {
				margin-top: 10px;
			}
			
			.footer {
				background-color: {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfFooterCopyrightBackground')};
				color: {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfFooterCopyrightText')};
				padding: 20px 10px;
			}
			
			.footer a {
				color: {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfFooterCopyrightLink')};
			}
			
			.footer a:hover {
				color: {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfFooterCopyrightLinkActive')};
				text-decoration: none;
			}
			
			.footer .copyright {
				text-align: center;
			}
			
			.main {
				color: {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfContentText')};
				padding: 30px 10px;
			}
			
			.section {
				margin-top: 30px;
			}
			
			.sectionTitle {
				font-weight: 300;
				font-size: 23px;
				line-height: 1.05;
				margin: 0;
			}
			
			.article .articleTitle {
				font-weight: 300;
				font-size: 23px;
				line-height: 1.05;
				margin: 0;
			}
			
			.article .articleAuthor {
				color: {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfContentDimmedText')};
				display: inline-block;
				font-size: 14px;
				font-weight: 400;
				margin: 5px 0 0 0;
			}
			
			.article .articleAuthor::after {
				color: {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfContentDimmedText')};
				content: "\00b7";
				margin-left: 6px;
			}
			
			.article .articleDate {
				color: {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfContentDimmedText')};
			}
			
			.article .articleImage {
				margin: 0;
			}
			
			.article .articleImage figcaption {
				color: {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfContentDimmedText')};
				font-size: smaller;
				margin-top: 5px;
				text-align: center;
			}
			
			.article .articleImage,
			.article .articleContent,
			.article .articleTeaser {
				margin-top: 30px;
			}
			
			.article .articleTeaser {
				font-weight: 600;
			}
			
			amp-user-notification {
				background-color: {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfStatusInfoBackground')};
				color: {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfStatusInfoText')};
				padding: 10px;
			}
			
			amp-sidebar {
				padding: 20px 10px 10px;
				width: 250px;
			}
			
			amp-sidebar button {
				margin-top: 0;
				position: absolute;
				right: 10px;
				top: 10px;
			}
			
			amp-sidebar h3 {
				font-size: 18px;
				font-weight: 400;
				margin: 20px 0 0;
			}
			
			amp-sidebar ol {
				margin: 10px 0 0;
				padding: 0;
			}
			
			amp-sidebar ol ol {
				margin-left: 20px;
				margin-top: 0;
			}
			
			amp-sidebar ol + ol {
				margin-top: 0;
			}
			
			amp-sidebar li {
				list-style: none;
			}
			
			amp-sidebar li a {
				display: block;
				padding: 7px 7px 7px 0;
			}
			
			amp-carousel {
				margin-top: 20px;
			}
			
			amp-carousel figcaption {
				background-color: rgba(0, 0, 0, .6);
				bottom: 0;
				color: #fff;
				left: 0;
				padding: 10px;
				position: absolute;
				right: 0;
			}
			
			.breadcrumbs li:nth-child(2) {
				padding-left: 20px;
			}
			.breadcrumbs li:nth-child(3) {
				padding-left: 30px;
			}
			.breadcrumbs li:nth-child(4) {
				padding-left: 40px;
			}
			
			.quoteBox {
				align-items: center;
				border: 1px solid {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfContentBorderInner')};
				border-left-width: 5px;
				display: flex;
				flex-wrap: wrap;
				margin: 0;
				padding: 5px 10px;
			}
			.quoteBoxIcon {
				border-radius: 50%;
				flex: 0 0 16px;
				margin-right: 5px;
				overflow: hidden;
			}
			.quoteBoxTitle {
				flex: 0 0 calc(100% - 21px); /* width + margin */
			}
			.quoteBoxContent {
				flex: 0 0 100%;
				font-style: italic;
			}
			
			.article ~ .section {
				border-top: 1px solid {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfContentBorder')};
				margin-top: 20px;
				padding: 20px 10px 0 10px;
			}
			
			.codeBox {
				border: 1px solid {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfContentBorderInner')};
				border-radius: 3px;
				margin: 10px 0;
			}
			.codeBoxHeader {
				border-bottom: 1px solid {@$__wcf->getStyleHandler()->getStyle()->getVariable('wcfContentBorderInner')};
				font-size: larger;
				padding: 5px 10px;
			}
			.codeBoxCode {
				margin: 0;
				max-height: 300px;
				overflow: auto;
				padding: 10px;
			}
		</style>
		{literal}<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>{/literal}
		<script async custom-element="amp-carousel" src="https://cdn.ampproject.org/v0/amp-carousel-0.1.js"></script>
		<script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
		<script async custom-element="amp-user-notification" src="https://cdn.ampproject.org/v0/amp-user-notification-0.1.js"></script>
		<script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
		<script async custom-element="amp-iframe" src="https://cdn.ampproject.org/v0/amp-iframe-0.1.js"></script>
		<script async src="https://cdn.ampproject.org/v0.js"></script>
	</head>
<body>
	<header class="header">
		<div class="logo">
			<a href="{link}{/link}"><amp-img width="{@$__wcf->getStyleHandler()->getStyle()->getVariable('pageLogoWidth')}" height="{@$__wcf->getStyleHandler()->getStyle()->getVariable('pageLogoHeight')}" src="{$__wcf->getStyleHandler()->getStyle()->getPageLogo()}"></amp-img></a>
		</div>
		
		<button on='tap:sidebar.toggle'>{lang}wcf.global.page.pagination{/lang}</button>
	</header>
	<main class="main">
