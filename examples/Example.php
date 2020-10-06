<?php

    namespace MarkupEngine;

	ini_set('error_reporting', E_ALL);
	ini_set('track_errors', '1');
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');

	require_once '../lib/MarkupEngine.php';
	
	$ct = new MarkupEngine(array(
		'parse_on_shutdown' 	=> true,
		'tag_directory' 		=> __DIR__.DIRECTORY_SEPARATOR.'tags'.DIRECTORY_SEPARATOR,
		'sniff_for_buried_tags' => true
	));
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">

<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Custom Tags - Example 1</title>
		<meta name="author" content="Daniel S. Davis">
		<!-- Date: 2020-10-06 -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
		
	</head>
	<body>
		<block>
			<row>
				<col size="2" />
				<col size="8">
					<header title="Header Title">Sample Body</header><br /><br />
					This is text that is unaffected by the tags as it is outside the tag scope.<br />
					<youtube src="QR-tZqiKCrg"/>

					<alert type="primary">Bootstrap Alert with Primary</alert>
					<alert type="secondary">Bootstrap Alert with Secondary</alert>
					<p>This entire content page is shelled within a column structure of 3 - 6 - 3 using bootstrap CDN. 
					Common layout divs have been replaced using Custom Markup Tags.</p>
					<h3>Before:</h3>
					<code>
						&lt;div class="container"&gt;&lt;div class="row"&gt;&lt;div class="col"&gt;&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;
					</code>
					<h3>After:</h3>
					<code>
						&lt;block&gt;&lt;row&gt;&lt;col&gt;&lt;/col&gt;&lt;/row&gt;&lt;/block&gt;
					</code>
					<p>
						This demonstrates MarkupEngines ability to parse recursively.
					</p>
				</col>
				<col size="2" />
			</row>
		</block>
		
	</body>
	<!-- JS, Popper.js, and jQuery -->
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
</html>