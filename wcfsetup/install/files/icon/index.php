<?php
$icons = array();
$files = glob('*.svg');
if (is_array($files)) {
	foreach ($files as $file) {
		if (preg_match('/^(.*)(?<!Colored|Inverse|Green|Red|Yellow).svg$/', $file, $match)) {
			$icons[] = $match[1];
		}
	}
}
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
	<meta charset="utf-8" />
	
	<title>SVG Icons</title>
	
	<style type="text/css">	
		* {
			font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
			text-overflow: ellipsis;
			margin: 0;
			padding: 0;
		}
		
		body {
			padding: 21px 63px;
			background-color: #ddd;
		}
		
		img {
			border-width: 0;
		}

		article {
			padding-bottom: 21px;
		}

		h1 {
			text-shadow: 0 1px 0 #fff;
		}

		h2 {
			font-size: 85%;
			text-shadow: 0 1px 0 #fff;
			color: #999;
			padding-bottom: 7px;
		}
		
		article > ul > li {
			display: inline;
			list-style: none;
		}
		
		article.inverse {
			background-color: #369;
			padding: 14px 21px;
			border-radius: 6px;
		}
		
		article.inverse > h2 {
			text-shadow: 0 -1px 0 #000;
			color: #fff;
		}
	</style>
</head>

<body>
	<header>
		<h1>Available SVG Icons</span></a></h1>
	</header>
	
	<article>
		<h2>48px:</h2>
		
		<ul>
			<?php
				foreach ($icons as $icon) {
					?><li><img src="<?php echo $icon; ?>.svg" title="<?php echo $icon; ?>" alt="" style="width: 48px; height: 48px" /></li><?php
				}
			?>
		</ul>
	</article>
	
	<article>
		<h2>24px:</h2>
		
		<ul>
			<?php
				foreach ($icons as $icon) {
					?><li><img src="<?php echo $icon; ?>.svg" title="<?php echo $icon; ?>" alt="" style="width: 24px; height: 24px" /></li><?php
				}
			?>
		</ul>
	</article>
	
	<article>
		<h2>Default size (16px):</h2>
		
		<ul>
			<?php
				foreach ($icons as $icon) {
					?><li><img src="<?php echo $icon; ?>.svg" title="<?php echo $icon; ?>" alt="" /></li><?php
				}
			?>
		</ul>
	</article>
	
	<header>
		<h1>Colored SVG Icons</span></a></h1>
	</header>
	
	<article>
		<h2>48px:</h2>
		
		<ul>
			<?php
				foreach ($icons as $icon) {
					?><li><img src="<?php echo $icon; ?>Colored.svg" title="<?php echo $icon; ?>" alt="" style="width: 48px; height: 48px" /></li><?php
				}
			?>
		</ul>
	</article>
	
	<article class="inverse">
		<h2>Inverse (48px):</h2>
		
		<ul>
			<?php
				foreach ($icons as $icon) {
					?><li><img src="<?php echo $icon; ?>Inverse.svg" title="<?php echo $icon; ?>" alt="" style="width: 48px; height: 48px" /></li><?php
				}
			?>
		</ul>
	</article>
</body>
</html>