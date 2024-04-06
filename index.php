<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
/**
 * dapkdapk
 */
require_once "3rd/lib/serversalt.php";
require_once "3rd/lib/functions.zerobin.php";
require_once "cfg/config.inc.php";
require_once "lib/p1n.php";

$title = strtoupper ( $_SERVER ["HTTP_HOST"] );
$relBootStrapPath = "vendor/twbs/bootstrap/dist/";
$relJQueryPath = "vendor/components/jquery/";
$relKnockoutPath = "3rd/js/";
$relP1NPath = "lib/";
$rel3rdPath = "3rd/";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title><?=$title?> URL</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

<!-- JQuery -->
<script src="<?=$relJQueryPath?>jquery.min.js"></script>
<!-- Bootstrap -->
<link href="<?=$relBootStrapPath?>css/bootstrap.min.css"
	rel="stylesheet">
<!-- Knockout -->
<script src="<?=$relKnockoutPath?>knockout.js"></script>
<!-- 3rd -->
<script src="<?=$rel3rdPath?>js/sjcl.js"></script>
<script src="<?=$rel3rdPath?>js/functions.zerobin.js"></script>
<script src="<?=$rel3rdPath?>js/base64.js"></script>
<script src="<?=$rel3rdPath?>js/rawdeflate.js"></script>
<script src="<?=$rel3rdPath?>js/rawinflate.js"></script>

<!-- css -->
<link type="text/css" rel="stylesheet" href="<?=$relP1NPath?>p1n.css" />

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>

	<!-- Fixed navbar -->
	<nav class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">

			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed"
					data-toggle="collapse" data-target="#navbar" aria-expanded="false"
					aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span> <span
						class="icon-bar"></span> <span class="icon-bar"></span> <span
						class="icon-bar"></span>
				</button>
				<a class="navbar-brand"
					href="javascript:window.location=scriptLocation();"><?=$title?> URL</a>
			</div>

		</div>
	</nav>

	<div class="container" role="main">

		<div id="topalign">
			<p>&nbsp;</p>
			<p>&nbsp;</p>
		</div>

		<div data-bind="visible: showNewUrl" class="alert alert-success"
			role="alert">
			<!-- <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> -->
			<h4>Encrypted:</h4>
			<a data-bind="attr: {href: urlString}" target="_blank"> <span
				data-bind="text: urlString" style="font-weight: bold;"></span>
			</a> <br />
			<br />

			<button data-bind="visible: shortUrlButton, click: getShortUrl"
				type="button" class="btn btn-primary btn-xs">
				<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
				GET SHORTURL
			</button>

			<span data-bind="visible: shortUrlSpan">
				<h4>Shorturl:</h4> <a data-bind="attr: {href: shortUrlString}"
				target="_blank"> <span data-bind="text: shortUrlString"
					style="font-weight: bold;"></span>
			</a>
			</span>

			<div align="right">
				<a data-bind="attr: {href: deleteUrl}" target="_self"> <span
					class="glyphicon glyphicon-trash"></span>
				</a>
			</div>
		</div>

		<div data-bind="visible: errorBox" class="alert alert-danger"
			role="alert">
			<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
			<span class="sr-only">Error:</span> <span data-bind="text: errorText"></span>
		</div>

		<div data-bind="visible: statusBox" class="alert alert-info"
			role="alert">
			<span data-bind="visible: infoTextSpin"
				class="glyphicon glyphicon-refresh glyphicon-spin"
				aria-hidden="true"></span> <span data-bind="text: infoText"></span>
		</div>

		<form class="form" data-bind="visible: showForm">
			<h4 class="form-heading">Enter your url</h4>
			<label for="inputUrl" class="sr-only">http://</label> <input
				data-bind="textInput: rawUrlString, value: enterText, valueUpdate: 'afterkeydown', 
    event: { keypress: enterKeyboardCmd}"
				type="url" id="inputUrl" class="form-control" placeholder="https://"
				required autofocus>
			<button data-bind="click: generateUrl"
				class="btn btn-lg btn-primary btn-block" type="submit">GET</button>
		</form>
		<p>&nbsp;</p>

		<footer>
			<cite>Encrypted urls for one day!</cite> | <a
				href="https://github.com/dapkdapk/p1n-urlshorter" target="_blank">source</a> |

			<a href="#" data-toggle="modal" data-target=".bs-example-modal-lg">Disclaimer</a>
		</footer>


		<div class="modal fade bs-example-modal-lg" tabindex="-1"
			role="dialog" aria-labelledby="myLargeModalLabel">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<iframe
						src="<?=(@$_SERVER ['HTTPS'] || (strpos(@$_SERVER['HTTP_VIA'], "ssl") != "") ? "https" : "http")?>://ourdisclaimer.com/?i=<?=$title?>"
						width="100%" height="600"></iframe>
				</div>
			</div>
		</div>

	</div>
	<div id="cipherdata" style="display: none;"><?=$CIPHERDATA?></div>
	<div id="errormessage" style="display: none;"><?=$ERRORMESSAGE?></div>
	<div id="statusmessage" style="display: none;"><?=$STATUS?></div>

	<!-- p1n -->
	<script src="<?=$relP1NPath?>p1n.js"></script>
	<script src="<?=$relBootStrapPath?>js/bootstrap.min.js"></script>
</body>
</html>