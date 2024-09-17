<?php
$host = isset($_GET['host']) ? $_GET['host'] : 'thiswebsite';
$relBootStrapPath = 'vendor/twbs/bootstrap/dist/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Disclaimer <?php echo $title; ?> URL</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Bootstrap -->
<link href="<?php echo $relBootStrapPath; ?>css/bootstrap.min.css"
	rel="stylesheet">
</head>
    <body style="font-family: Verdana, Helvetica;background-color:#333;color:#efefef;padding: 20px;">
    <h3>Disclaimer for <?php echo $host; ?></h3>
<p>If you require any more information or have any questions about our site's disclaimer, please feel free to contact us by email at info@<?php echo strtolower($host); ?>.</p>

<h4>Disclaimers for <?php echo strtolower($host); ?></h4>
<p>All the information on this website is published in good faith and for general information purpose only. Website Name does not make any warranties about the completeness, reliability and accuracy of this information. Any action you take upon the information you find on this website (<?php echo strtolower($host); ?>), is strictly at your own risk. will not be liable for any losses and/or damages in connection with the use of our website.</p>

<p>From our website, you can visit other websites by following hyperlinks to such external sites. While we strive to provide only quality links to useful and ethical websites, we have no control over the content and nature of these sites. These links to other websites do not imply a recommendation for all the content found on these sites. Site owners and content may change without notice and may occur before we have the opportunity to remove a link which may have gone ‘bad'.</p>

<p>Please be also aware that when you leave our website, other sites may have different privacy policies and terms which are beyond our control. Please be sure to check the Privacy Policies of these sites as well as their "Terms of Service" before engaging in any business or uploading any information.</p>

<h4>Consent</h4>
<p>By using our website, you hereby consent to our disclaimer and agree to its terms.</p>

<h4>Update</h4>
<p>Should we update, amend or make any changes to this document, those changes will be prominently posted here.</p>
    </body>
</html>