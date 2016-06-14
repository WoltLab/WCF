<div style="padding: 5em; background-color: lightgrey">
<?php
// @codingStandardsIgnoreFile
$flags = glob('*.svg');
foreach ($flags as $flag) {
	if ($flag == 'de-informal.svg') {
		continue;
	}
	
	echo '<img src="'.$flag.'" style="height: 15px; width: 24px; margin: 1em; box-shadow: 0px 0px 15px rgba(102, 102, 102, .8)" alt="">';
	echo '<img src="'.$flag.'" style="height: 30px; width: 48px; margin: 1em; box-shadow: 0px 0px 15px rgba(102, 102, 102, .8)" alt="">';
	echo '<img src="'.$flag.'" style="height: 60px; width: 96px; margin: 1em; box-shadow: 0px 0px 15px rgba(102, 102, 102, .8)" alt="">';
	echo '<img src="'.$flag.'" style="height: 120px; width: 192px; margin: 1em; box-shadow: 0px 0px 15px rgba(102, 102, 102, .8)" alt="">';
	echo '<hr>';
}
?>
</div>