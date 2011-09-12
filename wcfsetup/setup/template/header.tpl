<!DOCTYPE html>
<html dir="{@$__wcf->getLanguage()->getPageDirection()}" lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<meta charset="utf-8" />
	<title>{lang}wcf.global.progressBar{/lang} - {lang}wcf.global.pageTitle{/lang}</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	
	<style type="text/css">
		/*<![CDATA[*/
		
		/* ToDo: Reduce the necessary CSS declarations and find out if really all of that is deeded! This is not the final state! */
		
		/* -- -- -- -- -- Reset -- -- -- -- -- */
		
		/**
		 * Parts taken from
		 * http://meyerweb.com/eric/tools/css/reset/ 
		 * v2.0 | 20110126
		 * License: none (public domain)
		 * modifyed to meet the needs of WoltLab
		 * reduced for the installation file
		 */
		
		html, body, div, span, iframe,
		h1, h2, h3, h4, h5, h6, p, pre,
		a, abbr, acronym, address, big, cite, code,
		del, dfn, em, img, ins, kbd, q, s, samp,
		small, strike, strong, sub, sup, tt, var,
		b, u, i, center,
		dl, dt, dd, ol, ul, li,
		fieldset, form, label, legend,
		article, aside, canvas, details, embed, 
		figure, figcaption, footer, header, hgroup, 
		menu, nav, output, ruby, section, summary,
		time, mark, audio, video {
			margin: 0;
			padding: 0;
			border: 0;
			font-size: 100%;
		}
		
		/* HTML5 display-role reset for older browsers */
		article, aside, details, figcaption, figure, 
		footer, header, hgroup, menu, nav, section {
			display: block;
		}
		
		ol, ul {
			list-style: none;
		}
		
		
		
		/* -- -- -- -- -- Globals -- -- -- -- -- */
		
		* {
			text-overflow: ellipsis;
		}
		
		body {
			font-family: 'Trebuchet MS', Arial, sans-serif;
			font-size: 80%;
			background-color: #29374a;
		}
		
		img {
			vertical-align: middle !important;
		}
		
		p {
			color: #666;
		}
		
		a {
			color: #369;
			text-decoration: none;
			
			-webkit-transition: color .1s linear;
			-moz-transition: color .1s linear;
			-ms-transition: color .1s linear;
			-o-transition: color .1s linear;
			transition: color .1s linear;
		}
		
		a:hover {
			text-decoration: underline;
			color: #036;
		}
		
		/* ToDo 
		a.externalURL {
			background-image: url("../../icon/externalURL.svg");
			background-position: right center;
			background-repeat: no-repeat;
			padding-right: 17px;
		}
		*/
		
		
		
		/* -- -- -- -- -- Page Header -- -- -- -- -- */
		
		header.pageHeader {
			background-image: url("../images/header.png");
			background-position: left top;
			background-repeat: repeat-x;
			min-width: 800px;
			width: 100%;
		}
		
		/* Logo */
		
		header.pageHeader div#logo {
			margin: 0 23px 0;
			position: relative;
			z-index: 1;
		}
		
		header.pageHeader div#logo h1 {
			font-size: 150%;
			text-shadow: 0 -1px 0 #000;
			color: #d8e7f5;
			position: relative;
			top: 70px;
		}
		
		header.pageHeader div#logo a:hover {
			text-decoration: none !important;
			color: #d8e7f5;
		}
		
		header.pageHeader div#logo img {
			position: absolute;
			bottom: 10px;
			left: 0;
		}
		
		header.pageHeader div#logo a {
			text-align: right;
			display: block;
			height: 110px;
		}
		
		/* Header Navigation  */
		
		nav.headerNavigation {
			background-color: #e7f2fd;
			border-bottom: 1px solid #bcd;
			border-top-left-radius: 3px;
			border-top-right-radius: 3px;
			margin: 0 23px;
			display: block;
			position: relative;
			min-width: 800px;
		}
		
		nav.headerNavigation:after {
			content: "";
			display: block;
			clear: both;
		}
		
		nav.headerNavigation > div {
			padding: 3px 7px;
		}
		
		nav.headerNavigation > div:after {
			content: "";
			display: block;
			clear: both;
		}
		
		nav.headerNavigation ul {
			display: block;
		}
		
		nav.headerNavigation ul li {
			float: right;
		}
		
		
		
		/* -- -- -- -- -- Main -- -- -- -- -- */
		
		div.main {
			margin: 0 23px;
			min-width: 800px;
		}
		
		
		
		/* -- -- -- -- -- Content -- -- -- -- -- */
		
		section.content {
			background-color: #fff;
			display: table-cell;
			padding: 25px 40px;
			position: relative;
			width: 100%;
			z-index: 100;
			vertical-align: top;
		}
		
		section.content .content {
			border: 1px solid #ccc;
			background-color: rgba(0, 0, 0, .01);
			padding: 13px 23px;
		}
		
		div.main > div section:only-child {
			display: block !important;
			width: auto;
		}
		
		
		
		/* -- -- -- -- -- Page Footer -- -- -- -- --  */
		
		footer.pageFooter {
			text-align: center;
			padding: 0 23px;
			clear: both;
			position: relative;
			min-width: 800px;
		}
		
		footer.pageFooter:after {
			content: "";
			display: block;
			clear: both;
		}
		
		footer.pageFooter > div {
			border-top: 1px solid #bcd;
			border-bottom-left-radius: 3px;
			border-bottom-right-radius: 3px;
			background-color: #e7f2fd;
			padding: 3px 7px;
			clear: both;
		}
		
		footer.pageFooter > div:after {
			content: "";
			display: block;
			clear: both;
		}
		
		footer.pageFooter .copyright {
			padding-top: 20px;
			display: inline-block;
			min-height: 40px;
		}
		
		footer.pageFooter .copyright a {
			text-shadow: 0 -1px 0 #000;
		}
		
		footer.pageFooter .copyright a:hover {
			text-decoration: none;
			color: #69c;
		}
		
		/* Footer Navigation */
		
		footer.pageFooter nav.footerNavigation ul li {
			display: inline-block;
			float: right;
		}
		
		
		
		/* -- -- -- -- -- Headings -- -- -- -- -- */
		
		/* Main Heading */
		
		.mainHeading {
			margin: 0 0 15px;
			position: relative;
		}
		
		.mainHeading img {
			position: absolute;
			top: 0;
			left: 0;
			width: 48px;
			height: 48px;
		}
		
		.mainHeading > hgroup {
			margin-bottom: 30px;
			padding-left: 60px;
			min-height: 48px;
		}
		
		.mainHeading > hgroup h1 {
			font-size: 175%;
			font-weight: bold;
			text-shadow: 0 1px 0 #fff;
			color: #666;
			border-bottom: 1px solid #999;
			padding-bottom: 10px;
		}
		
		.mainHeading > hgroup h2 {
			font-size: 100%;
			font-weight: normal;
			color: #999;
			padding-top: 5px;
		}
		
		.mainHeading > hgroup p {
			padding-top: 3px;
		} 
		
		/* Sub Heading */
		
		.subHeading h1 {
			font-size: 150%;
			text-shadow: 0 1px 0 #fff;
			color: #999;
			border-bottom: 1px solid #ccc;
			margin: 10px 0;
			padding: 10px 0;
		}
		
		
		
		/* -- -- -- -- -- Fieldsets -- -- -- -- -- */
		
		fieldset {
			border: 1px solid #ccc;
			border-radius: 7px;
			background-color: rgba(0, 0, 0, .015);
			margin: 15px 0;
			padding: 15px;
		}
		
		fieldset legend {
			color: #999;
			padding: 0 7px 0;
		}
		
		fieldset legend ~ p {
			margin-bottom: 14px;
		}
		
		fieldset p.description {
			font-size: 90%;
			color: #999;
		}
		
		fieldset > ul {
			margin: 7px 0 21px 21px;
		}
		
		fieldset > ul li {
			list-style-type: circle;
		}
		
		
		
		/* -- -- -- -- -- Tabbed Content -- -- -- -- -- */
		
		/* Simple */
		
		dl {
			margin-bottom: 7px;
			clear: both;
			position: relative;
		}
		
		dl > dt {
			text-align: right;
			color: #69c;
			margin-top: 5px; /* not so nice */
			float: left;
			width: 230px;
		}
		
		dl > dt > label {
			margin-top: 5px;
			display: block;
		}
		
		dl.disabled > dt {
			color: #777;
		}
		
		dl > dd {
			margin-left: 250px;
			padding-top: 5px;
		}
		
		dl > dd > small {
			font-size: 85%;
			color: #999;
			margin: 3px 0 7px;
			display: block;
		}
		
		/* Nested */
		
		dl > dd > fieldset {
			margin-top: 0;
			margin-bottom: 0;
		}
		
		dl > dd > fieldset > legend {
			display: none;
		}
		
		dl > dd > fieldset > dl > dt {
			margin-right: 20px;
			width: 150px;
		}
		
		dl > dd > fieldset > dl > dd {
			margin-left: 0;
		}
		
		dl > dd > fieldset > dl > dd > label {
			display: block;
		}
		
		
		
		/* -- -- -- -- -- Forms -- -- -- -- -- */
		
		/* Globals */
		
		label {
			color: #69c;
		}
		
		/* Structure */
		
		.formSubmit {
			text-align: center;
			margin-top: 15px;
		}
		
		input[type='checkbox'] ~ small,
		input[type='radio'] ~ small {
			margin-top: 0;
			margin-left: 21px;
		}
		
		/* Form Elements */
		
		input[type='reset'],
		input[type='submit'],
		input[type='checkbox'],
		input[type='radio'],
		select {
			cursor: pointer;
		}
		
		input[type='text'],
		input[type="search"],
		input[type="email"],
		input[type='password'] {
			padding: 5px 3px;
		}
		
		/* inputs normal */
		input[type='text'],
		input[type="search"],
		input[type="email"],
		input[type='password'],
		textarea {
			border-width: 1px;
			border-style: solid;
			border-color: #999 #ccc #eee;
			border-radius: 3px;
			background-color: #fff;
			
			-webkit-box-shadow: inset 0 1px 5px rgba(0, 0, 0, .1);
			-moz-box-shadow: inset 0 1px 5px rgba(0, 0, 0, .1);
			-ms-box-shadow: inset 0 1px 5px rgba(0, 0, 0, .1);
			-o-box-shadow: inset 0 1px 5px rgba(0, 0, 0, .1);
			box-shadow: inset 0 1px 5px rgba(0, 0, 0, .1);
		}
		
		/* inputs hover */
		input[type='text']:hover,
		input[type="search"]:hover,
		input[type="email"]:hover,
		input[type='password']:hover,
		textarea:hover {
			border: 1px solid #fa2;
			background-color: #fff9f4;
			
		}
		
		/* inputs active */
		input[type='text']:active,
		input[type="search"]:active,
		input[type="email"]:active,
		input[type='password']:active,
		textarea:active,
		input[type='text']:focus,
		input[type="search"]:focus,
		input[type="email"]:focus,
		input[type='password']:focus,
		textarea:focus {
			border: 1px solid #fa2;
			background-color: #fff9f4;
			outline: none;
			
			-webkit-box-shadow: 0 0 5px rgba(255, 170, 34, .5), inset 0 1px 5px rgba(0, 0, 0, .2);
			-moz-box-shadow: 0 0 5px rgba(255, 170, 34, .5), inset 0 1px 5px rgba(0, 0, 0, .2);
			-ms-box-shadow: 0 0 5px rgba(255, 170, 34, .5), inset 0 1px 5px rgba(0, 0, 0, .2);
			-o-box-shadow: 0 0 5px rgba(255, 170, 34, .5), inset 0 1px 5px rgba(0, 0, 0, .2);
			box-shadow: 0 0 5px rgba(255, 170, 34, .5), inset 0 1px 5px rgba(0, 0, 0, .2);
			
			-webkit-transition: all .2s linear;
			-moz-transition: all .2s linear;
			-ms-transition: all .2s linear;
			-o-transition: all .2s linear;
			transition: all .2s linear;
		}
		
		/* Widths */
		
		textarea {
			width: 95%;
		}
		
		.tiny {
			width: 100px;
		}
		
		.short {
			width: 10%;
		}
		
		.medium {
			width: 50%;
		}
		
		.long {
			width: 95%;
		}
		
		.auto {
			width: auto;
		}
		
		
		/* -- -- -- -- -- Large Buttons -- -- -- -- -- */
		
		.largeButtons {
			text-align: right;
		}
		
		.largeButtons ul li {
			display: inline;
		}
		
		.largeButtons ul li img {
			/* Button Icons disabled for now, maybe we get rid oft them */
			display: none;
		}
		
		/* buttons normal */
		input[type='reset'],
		input[type='submit'],
		.largeButtons ul li a {
			font-size: 100%;
			font-weight: bold;
			text-decoration: none;
			text-shadow: 0 1px 0 #fff;
			color: #999;
			
			border-width: 1px;
			border-style: solid;
			border-color: #ccc #bbb #aaa;
			border-radius: 30px;
			
			background-color: #fefefe;
			background-image: -webkit-linear-gradient(rgba(255, 255, 255, 1), rgba(245, 245, 245, 1) 2px, rgba(235, 235, 235, 1));
			background-image: -moz-linear-gradient(rgba(255, 255, 255, 1), rgba(245, 245, 245, 1) 2px, rgba(235, 235, 235, 1));
			background-image: -ms-linear-gradient(rgba(255, 255, 255, 1), rgba(245, 245, 245, 1) 2px, rgba(235, 235, 235, 1));
			background-image: -o-linear-gradient(rgba(255, 255, 255, 1), rgba(245, 245, 245, 1) 2px, rgba(235, 235, 235, 1));
			background-image: linear-gradient(rgba(255, 255, 255, 1), rgba(245, 245, 245, 1) 2px, rgba(235, 235, 235, 1));
			
			margin: 0 3px;
			padding: 5px 13px;
			display: inline-block;
			
			-webkit-transition: all .1s linear;
			-moz-transition: all .1s linear;
			-ms-transition: all .1s linear;
			-o-transition: all .1s linear;
			transition: all .1s linear;
		}
		
		/* buttons hover */
		input[type='reset']:hover,
		input[type='submit']:hover,
		.largeButtons ul li a:hover {
			color: #666;
			border-width: 1px;
			border-style: solid;
			border-color: #ffc053 #fa2 #fc9e07;
			
			background-color: #fff9f4;
			background-image: -webkit-linear-gradient(rgba(255, 255, 255, 1), rgba(255, 237, 217, 1) 2px, rgba(255, 229, 200, 1));
			background-image: -moz-linear-gradient(rgba(255, 255, 255, 1), rgba(255, 237, 217, 1) 2px, rgba(255, 229, 200, 1));
			background-image: -ms-linear-gradient(rgba(255, 255, 255, 1), rgba(255, 237, 217, 1) 2px, rgba(255, 229, 200, 1));
			background-image: -o-linear-gradient(rgba(255, 255, 255, 1), rgba(255, 237, 217, 1) 2px, rgba(255, 229, 200, 1));
			background-image: linear-gradient(rgba(255, 255, 255, 1), rgba(255, 237, 217, 1) 2px, rgba(255, 229, 200, 1));
		}
		
		/* buttons active */
		input[type='reset']:focus,
		input[type='submit']:focus,
		.largeButtons ul li a:focus,
		input[type='reset']:active,
		input[type='submit']:active,
		.largeButtons ul li a:active{
			color: #333;
			border-width: 1px;
			border-style: solid;
			border-color: #fc9e07 #fa2 #ffc053;
			
			background-color: #fff9f4;
			background-image: -webkit-linear-gradient(bottom, rgba(255, 255, 255, 1), rgba(255, 237, 217, 1) 2px, rgba(255, 229, 200, 1));
			background-image: -moz-linear-gradient(bottom, rgba(255, 255, 255, 1), rgba(255, 237, 217, 1) 2px, rgba(255, 229, 200, 1));
			background-image: -ms-linear-gradient(bottom, rgba(255, 255, 255, 1), rgba(255, 237, 217, 1) 2px, rgba(255, 229, 200, 1));
			background-image: -o-linear-gradient(bottom, rgba(255, 255, 255, 1), rgba(255, 237, 217, 1) 2px, rgba(255, 229, 200, 1));
			background-image: linear-gradient(bottom, rgba(255, 255, 255, 1), rgba(255, 237, 217, 1) 2px, rgba(255, 229, 200, 1));
			
			-webkit-box-shadow: inset 0 1px 3px rgba(0, 0, 0, .1);
			-moz-box-shadow: inset 0 1px 3px rgba(0, 0, 0, .1);
			-ms-box-shadow: inset 0 1px 3px rgba(0, 0, 0, .1);
			-o-box-shadow: inset 0 1px 3px rgba(0, 0, 0, .1);
			box-shadow: inset 0 1px 3px rgba(0, 0, 0, .1);
		}
		
		/* default buttons glow */
		@-webkit-keyframes glowLargeButtons {
			0% {
				-webkit-box-shadow: 0 0 13px rgba(102, 153, 204, .3);
			}
			100% {
				-webkit-box-shadow: 0 0 13px rgba(102, 153, 204, .1);
			}
		}
		@-moz-keyframes glowLargeButtons {
			0% {
				-moz-box-shadow: 0 0 13px rgba(102, 153, 204, .3);
			}
			100% {
				-moz-box-shadow: 0 0 13px rgba(102, 153, 204, .1);
			}
		}
		@-ms-keyframes glowLargeButtons {
			0% {
				-ms-box-shadow: 0 0 13px rgba(102, 153, 204, .3);
			}
			100% {
				-ms-box-shadow: 0 0 13px rgba(102, 153, 204, .1);
			}
		}
		@-o-keyframes glowLargeButtons {
			0% {
				-o-box-shadow: 0 0 13px rgba(102, 153, 204, .3);
			}
			100% {
				-o-box-shadow: 0 0 13px rgba(102, 153, 204, .1);
			}
		}
		@keyframes glowLargeButtons {
			0% {
				box-shadow: 0 0 13px rgba(102, 153, 204, .3);
			}
			100% {
				box-shadow: 0 0 13px rgba(102, 153, 204, .1);
			}
		}
		
		/* default buttons normal */
		input[type='submit'],
		.largeButtons ul li.default a {
			color: #69c;
			border-width: 1px;
			border-style: solid;
			border-color: #7aade0 #69c #5285b8;
			
			background-color: #e7f2fd;
			background-image: -webkit-linear-gradient(rgba(255, 255, 255, 1), rgba(233, 244, 255, 1) 2px, rgba(216, 231, 245, 1));
			background-image: -moz-linear-gradient(rgba(255, 255, 255, 1), rgba(233, 244, 255, 1) 2px, rgba(216, 231, 245, 1));
			background-image: -ms-linear-gradient(rgba(255, 255, 255, 1), rgba(233, 244, 255, 1) 2px, rgba(216, 231, 245, 1));
			background-image: -o-linear-gradient(rgba(255, 255, 255, 1), rgba(233, 244, 255, 1) 2px, rgba(216, 231, 245, 1));
			background-image: linear-gradient(rgba(255, 255, 255, 1), rgba(233, 244, 255, 1) 2px, rgba(216, 231, 245, 1));
			
			-webkit-box-shadow: 0 0 10px #369;
			-moz-box-shadow: 0 0 10px #369;
			-ms-box-shadow: 0 0 10px #369;
			-o-box-shadow: 0 0 10px #369;
			box-shadow: 0 0 10px #369;
			
			-webkit-animation-name: glowLargeButtons;
			-webkit-animation-duration: 1s;
			-webkit-animation-iteration-count: infinite;
			-webkit-animation-direction: alternate;
			-webkit-animation-timing-function: ease-in-out; 
			
			-moz-animation-name: glowLargeButtons;
			-moz-animation-duration: 1s;
			-moz-animation-iteration-count: infinite;
			-moz-animation-direction: alternate;
			-moz-animation-timing-function: ease-in-out;
			
			-ms-animation-name: glowLargeButtons;
			-ms-animation-duration: 1s;
			-ms-animation-iteration-count: infinite;
			-ms-animation-direction: alternate;
			-ms-animation-timing-function: ease-in-out; 
			
			-o-animation-name: glowLargeButtons;
			-o-animation-duration: 1s;
			-o-animation-iteration-count: infinite;
			-o-animation-direction: alternate;
			-o-animation-timing-function: ease-in-out;
			
			animation-name: glowLargeButtons;
			animation-duration: 1s;
			animation-iteration-count: infinite;
			animation-direction: alternate;
			animation-timing-function: ease-in-out;
		}
		
		/* default buttons hover glow */
		@-webkit-keyframes glowLargeButtonsHover {
			0% {
				-webkit-box-shadow: 0 0 13px rgba(255, 153, 51, .3);
			}
			100% {
				-webkit-box-shadow: 0 0 13px rgba(255, 153, 51, .1);
			}
		}
		@-moz-keyframes glowLargeButtonsHover {
			0% {
				-moz-box-shadow: 0 0 13px rgba(255, 153, 51, .3);
			}
			100% {
				-moz-box-shadow: 0 0 13px rgba(255, 153, 51, .1);
			}
		}
		@-ms-keyframes glowLargeButtonsHover {
			0% {
				-ms-box-shadow: 0 0 13px rgba(255, 153, 51, .3);
			}
			100% {
				-ms-box-shadow: 0 0 13px rgba(255, 153, 51, .1);
			}
		}
		@-o-keyframes glowLargeButtonsHover {
			0% {
				-o-box-shadow: 0 0 13px rgba(255, 153, 51, .3);
			}
			100% {
				-o-box-shadow: 0 0 13px rgba(255, 153, 51, .1);
			}
		}
		@keyframes glowLargeButtonsHover {
			0% {
				box-shadow: 0 0 13px rgba(255, 153, 51, .3);
			}
			100% {
				box-shadow: 0 0 13px rgba(255, 153, 51, .1);
			}
		}
		
		/* default buttons hover */
		input[type='submit']:hover,
		.largeButtons ul li.default a:hover {
			color: #666;
			
			-webkit-box-shadow: 0 0 10px #fa2;
			-moz-box-shadow: 0 0 10px #fa2;
			-ms-box-shadow: 0 0 10px #fa2;
			-o-box-shadow: 0 0 10px #fa2;
			box-shadow: 0 0 10px #fa2;
			
			-webkit-animation-name: glowLargeButtonsHover;
			-webkit-animation-duration: 1s;
			-webkit-animation-iteration-count: infinite;
			-webkit-animation-direction: alternate;
			-webkit-animation-timing-function: ease-in-out; 
			
			-moz-animation-name: glowLargeButtonsHover;
			-moz-animation-duration: 1s;
			-moz-animation-iteration-count: infinite;
			-moz-animation-direction: alternate;
			-moz-animation-timing-function: ease-in-out;
			
			-ms-animation-name: glowLargeButtonsHover;
			-ms-animation-duration: 1s;
			-ms-animation-iteration-count: infinite;
			-ms-animation-direction: alternate;
			-ms-animation-timing-function: ease-in-out; 
			
			-o-animation-name: glowLargeButtonsHover;
			-o-animation-duration: 1s;
			-o-animation-iteration-count: infinite;
			-o-animation-direction: alternate;
			-o-animation-timing-function: ease-in-out;
			
			animation-name: glowLargeButtonsHover;
			animation-duration: 1s;
			animation-iteration-count: infinite;
			animation-direction: alternate;
			animation-timing-function: ease-in-out;
		}
		
		/* default buttons active glow */
		@-webkit-keyframes glowDefaultLargeButtonsActive {
			0% {
				-webkit-box-shadow: 0 0 13px rgba(255, 153, 51, .3), inset 0 1px 3px rgba(0, 0, 0, .1);
			}
			100% {
				-webkit-box-shadow: 0 0 13px rgba(255, 153, 51, .1), inset 0 1px 3px rgba(0, 0, 0, .1);
			}
		}
		@-moz-keyframes glowDefaultLargeButtonsActive {
			0% {
				-moz-box-shadow: 0 0 13px rgba(255, 153, 51, .3), inset 0 1px 3px rgba(0, 0, 0, .1);
			}
			100% {
				-moz-box-shadow: 0 0 13px rgba(255, 153, 51, .1), inset 0 1px 3px rgba(0, 0, 0, .1);
			}
		}
		@-ms-keyframes glowDefaultLargeButtonsActive {
			0% {
				-ms-box-shadow: 0 0 13px rgba(255, 153, 51, .3), inset 0 1px 3px rgba(0, 0, 0, .1);
			}
			100% {
				-ms-box-shadow: 0 0 13px rgba(255, 153, 51, .1), inset 0 1px 3px rgba(0, 0, 0, .1);
			}
		}
		@-o-keyframes glowDefaultLargeButtonsActive {
			0% {
				-o-box-shadow: 0 0 13px rgba(255, 153, 51, .3), inset 0 1px 3px rgba(0, 0, 0, .1);
			}
			100% {
				-o-box-shadow: 0 0 13px rgba(255, 153, 51, .1), inset 0 1px 3px rgba(0, 0, 0, .1);
			}
		}
		@keyframes glowDefaultLargeButtonsActive {
			0% {
				box-shadow: 0 0 13px rgba(255, 153, 51, .3), inset 0 1px 3px rgba(0, 0, 0, .1);
			}
			100% {
				box-shadow: 0 0 13px rgba(255, 153, 51, .1), inset 0 1px 3px rgba(0, 0, 0, .1);
			}
		}
		
		/* default buttons active */
		input[type='submit']:focus,
		.largeButtons ul li.default a:focus,
		input[type='submit']:active,
		.largeButtons ul li.default a:active {
			color: #333;
			
			-webkit-box-shadow: inset 0 1px 3px rgba(0, 0, 0, .1);
			-moz-box-shadow: inset 0 1px 3px rgba(0, 0, 0, .1);
			-ms-box-shadow: inset 0 1px 3px rgba(0, 0, 0, .1);
			-o-box-shadow: inset 0 1px 3px rgba(0, 0, 0, .1);
			box-shadow: inset 0 1px 3px rgba(0, 0, 0, .1);
			
			-webkit-animation-name: glowDefaultLargeButtonsActive;
			-webkit-animation-duration: 1s;
			-webkit-animation-iteration-count: infinite;
			-webkit-animation-direction: alternate;
			-webkit-animation-timing-function: ease-in-out; 
			
			-moz-animation-name: glowDefaultLargeButtonsActive;
			-moz-animation-duration: 1s;
			-moz-animation-iteration-count: infinite;
			-moz-animation-direction: alternate;
			-moz-animation-timing-function: ease-in-out;
			
			-ms-animation-name: glowDefaultLargeButtonsActive;
			-ms-animation-duration: 1s;
			-ms-animation-iteration-count: infinite;
			-ms-animation-direction: alternate;
			-ms-animation-timing-function: ease-in-out; 
			
			-o-animation-name: glowDefaultLargeButtonsActive;
			-o-animation-duration: 1s;
			-o-animation-iteration-count: infinite;
			-o-animation-direction: alternate;
			-o-animation-timing-function: ease-in-out;
			
			animation-name: glowDefaultLargeButtonsActive;
			animation-duration: 1s;
			animation-iteration-count: infinite;
			animation-direction: alternate;
			animation-timing-function: ease-in-out;
		}
		
		/* ToDo: Images deprecated? */
		.largeButtons ul li a img {
			margin: 0;
			height: 24px;
			width: 24px;
			vertical-align: middle;
		}
		
		
		
		/* -- -- -- -- -- Border -- -- -- -- -- */
		
		.border {
			border: 1px solid #ccc;
			border-radius: 5px;
			margin: 15px 0 15px;
		}
		
		
		
		/* -- -- -- -- -- System Notifications -- -- -- -- -- */
		
		p.info,
		p.error,
		p.success,
		p.warning {
			text-shadow: 0 1px 0 #fff;
			line-height: 1.5;
			border-radius: 7px;
			margin-bottom: 14px;
			padding: 7px 14px 7px 50px;
			
			-webkit-box-shadow: 0 0 7px rgba(0, 0, 0, .1);
			-moz-box-shadow: 0 0 7px rgba(0, 0, 0, .1);
			-ms-box-shadow: 0 0 7px rgba(0, 0, 0, .1);
			-o-box-shadow: 0 0 7px rgba(0, 0, 0, .1);
			box-shadow: 0 0 7px rgba(0, 0, 0, .1);
			
			-webkit-transition: all .1s linear;
			-moz-transition: all .1s linear;
			-ms-transition: all .1s linear;
			-o-transition: all .1s linear;
			transition: all .1s linear;
		}
		
		p.info {
			color: #68b;
			border: 1px solid #9be;
			background-color: #def;
			background-image: url("../../icon/systemInfo.svg");
			background-size: 24px;
			background-position: 13px center;
			background-repeat: no-repeat;
		}
		
		p.success {
			color: #090;
			border: 1px solid #0c0;
			background-color: #efe;
			background-image: url("../../icon/systemSuccess.svg");
			background-size: 24px;
			background-position: 13px center;
			background-repeat: no-repeat;
		}
		
		p.warning {
			color: #990;
			border: 1px solid #cc0;
			background-color: #ffd;
			background-image: url("../../icon/systemWarning.svg");
			background-size: 24px;
			background-position: 13px center;
			background-repeat: no-repeat;
		}
		
		p.error {
			color: #c00;
			border: 1px solid #f99;
			background-color: #fee;
			background-image: url("../../icon/systemError.svg");
			background-size: 24px;
			background-position: 13px center;
			background-repeat: no-repeat;
		}
		
		
		
		/* -- -- -- -- -- Badges -- -- -- -- -- */
		
		/* Globals */
		
		.badge {
			font-size: 85%;
			text-shadow: none;
			color: #666;
			border: 1px solid #ccc;
			border-radius: 13px;
			background-color: #fff;
			margin-right: -3px;
			margin-left: 3px;
			padding: 1px 5px 2px;
			display: inline-block;
			position: relative;
			top: -1px;
		}
		
		/* Types */
		
		.badgeInfo {
			color: #68b;
			border: 1px solid #9be;
			background-color: #def;
		}
		
		.badgeSuccess {
			color: #090;
			border: 1px solid #0c0;
			background-color: #efe;
		}
		
		.badgeWarning {
			color: #990;
			border: 1px solid #cc0;
			background-color: #ffd;
		}
		
		.badgeError {
			color: #c00;
			border: 1px solid #f99;
			background-color: #fee;
		}
		
		/*]]>*/
	</style>
</head>

<body>
	<a id="top"></a>
	<!-- HEADER -->
	<header id="pageHeader" class="pageHeader">
		<div>
			<!-- no top menu -->
			
			<!-- logo -->
			<div id="logo" class="logo">
				<h1>Installation</h1>
				{* ToDo: include the correct header image <img src="{@RELATIVE_WCF_DIR}acp/images/wcfLogoWhite.svg" width="300" height="58" alt="Product-logo" title="Installation" /> *}
				
				<!-- no search area -->
			</div>
			<!-- /logo -->
			
			<!-- no main menu -->
			
			<!-- header navigation -->
			<nav class="headerNavigation">
				<div>
					<ul>
						{* TODO: include the correct image <li id="toBottomLink" class="toBottomLink"><a href="#bottom" title="{lang}wcf.global.scrollDown{/lang}" class="balloonTooltip"><img src="{@RELATIVE_WCF_DIR}icon/toBottom.svg" alt="" /> <span class="invisible">{lang}wcf.global.scrollDown{/lang}</span></a></li> *}
					</ul>
				</div>
			</nav>
			<!-- /header navigation -->
		</div>
	</header>
	<!-- /HEADER -->
	
	<!-- MAIN -->
	<div id="main" class="main">
		<div>
			
			<!-- CONTENT -->
			<section id="content" class="content">
				
				<header class="mainHeading setup">
					{* ToDo: Installation Icon
					<img src="{@RELATIVE_WCF_DIR}icon/cache1.svg" alt="" />
					/Installation Icon *}
					<hgroup>
						<h1>{lang}wcf.global.title{/lang}</h1>
						<h2>{lang}wcf.global.title.subtitle{/lang}</h2>
						{* ToDo: Progress bar *}
						<p><progress id="packageInstallationProgress" value="0" max="100" style="width: 300px;">0%</progress></p>
					</hgroup>
				</header>
