<?php

$texts = explode("\n", file_get_contents("chinese.txt"));
$bg = "http://d3gbrb95pfitbz.cloudfront.net/message_photo/1436241183_5447329.jpeg";

echo "<table>";
for($j = 0; $j < count($texts); $j += 6) {
	echo "<tr>";
	for( $i = 1; $i <= 6; $i++ ) {
		$t = $texts[$j+$i];
		echo "<td style='width:180px; height: 240px; background-image:url($bg); margin:0px; padding:0px;background-size: cover;'>";
		echo "<img width=180px src='/koala/Shout/public/image?q=" . urlencode($t)."' />";
		echo "</td>\n"; 
	}
	echo "</tr><tr><td col=6>&nbsp;</td></tr>";
}
echo "</table>";

