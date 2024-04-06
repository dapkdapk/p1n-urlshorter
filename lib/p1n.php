<?php
function aksencode($v, $p = '', $zlen = 32)
{
	$ret = '';
	if ($p) {
		$ret = urlencode(base64_encode($v)) . '_' . substr(md5($p), -6);
	} else {
		$ret = urlencode(base64_encode($v));
	}
	return str_replace('%3D', '', $ret);
}

function aksdecode($v, $p = '')
{
	if ($p) {
		$vt = explode('_', $v);
		$pt = substr(md5($p), -6);
		if ($pt == $vt[1]) {
			return base64_decode(urldecode($vt[0]));
		}
	} else {
		return base64_decode(urldecode($v));
	}
}

function encrypt($decrypted, $password = '', $salt = '!kQm*fF3pXe1Kbm%9')
{
	$key = hash('SHA256', $salt . $password, true);
	srand();
	$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
	$encrypted = openssl_encrypt($decrypted, 'aes-256-cbc', $key, 0, $iv);
	return base64_encode($encrypted . '::' . $iv);
}

function decrypt($encrypted, $password = '', $salt = '!kQm*fF3pXe1Kbm%9')
{
	$key = hash('SHA256', $salt . $password, true);
	list($encrypted_data, $iv) = explode('::', base64_decode($encrypted), 2);
	return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

function cleanurl($u)
{
	$a = explode('://', $u);
	$b = str_replace('//', '/', $a[1]);
	$c = $a[0] . '://' . $b;
	if (substr($c, -2) == '//') {
		return substr($c, 0, -1);
	} else {
		return $c;
	}
}

function stripslashes_deep($value)
{
	$value = is_array($value)
		? array_map('stripslashes_deep', $value)
		: stripslashes($value);
	return $value;
}

if (
	(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) ||
	(ini_get('magic_quotes_sybase') && (strtolower(ini_get('magic_quotes_sybase')) != 'off'))
) {
	stripslashes_deep($_GET);
	stripslashes_deep($_POST);
	stripslashes_deep($_COOKIE);
}

if (!empty($_POST['shorturl'])) {
	header('Content-type: application/json');

	$shorturl = $_POST['shorturl'];

	if ($shorturl != '') {
		$e = explode('?', $shorturl);
		$code = array(
			substr($e[1], 0, 2),
			substr($e[1], 2, 2)
		);
		$codeenc = aksencode(serialize($code));

		file_put_contents('data/' . $code[0] . '/' . $code[1] . '/' . urlencode($codeenc), encrypt($shorturl));

		$newurl = @cleanurl((@$_SERVER['HTTPS'] || (@strpos(@$_SERVER['HTTP_VIA'], 'ssl') != '') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . (dirname($_SERVER['PHP_SELF']) == '\\' ? '' : dirname($_SERVER['PHP_SELF'])) . (dirname($_SERVER['PHP_SELF']) != '' ? '/' : '') . (ENABLE_MODREWRITE ? '' : '?s=') . $code[0] . $code[1]);
		echo json_encode(
			array(
				'status' => 0,
				'shorturl' => $newurl,
				'message' => 'Shorturl was created.'
			)
		);
		exit();
	}

	echo json_encode(
		array(
			'status' => 1,
			'message' => 'Server error.'
		)
	);
	exit();
}

if (!empty($_POST['data'])) {
	header('Content-type: application/json');

	$error = false;

	// Create storage directory if it does not exist.
	if (!is_dir('data')) {
		mkdir('data', 0705);
		file_put_contents('data/.htaccess', "Allow from none\nDeny from all\n", LOCK_EX);
	}

	// Make sure last paste from the IP address was more than 10 seconds ago.
	if (!trafic_limiter_canPass($_SERVER['REMOTE_ADDR'])) {
		echo json_encode(
			array(
				'status' => 1,
				'message' => 'Please wait 10 seconds between each post.'
			)
		);
		exit();
	}

	// Make sure content is not too big.
	$data = $_POST['data'];
	if (strlen($data) > 2000000) {
		echo json_encode(
			array(
				'status' => 1,
				'message' => 'Url is limited to 2 Mb of encrypted data.'
			)
		);
		exit();
	}

	// Make sure format is correct.
	if (!validSJCL($data)) {
		echo json_encode(
			array(
				'status' => 1,
				'message' => 'Invalid data.'
			)
		);
		exit();
	}

	// Read additional meta-information.
	$meta = array();

	// Read expiration date
	if (!empty($_POST['expire'])) {
		$expire = $_POST['expire'];
		if ($expire == '5min')
			$meta['expire_date'] = time() + 5 * 60;
		elseif ($expire == '10min')
			$meta['expire_date'] = time() + 10 * 60;
		elseif ($expire == '1hour')
			$meta['expire_date'] = time() + 60 * 60;
		elseif ($expire == '1day')
			$meta['expire_date'] = time() + 24 * 60 * 60;
		elseif ($expire == '1week')
			$meta['expire_date'] = time() + 7 * 24 * 60 * 60;
		elseif ($expire == '1month')
			$meta['expire_date'] = time() + 30 * 24 * 60 * 60;  // Well this is not *exactly* one month, it's 30 days.
		elseif ($expire == '1year')
			$meta['expire_date'] = time() + 365 * 24 * 60 * 60;
	}
	if ($error) {
		echo json_encode(
			array(
				'status' => 1,
				'message' => 'Invalid data.'
			)
		);
		exit();
	}

	// Add post date to meta.
	$meta['postdate'] = time();

	// We just want a small hash to avoid collisions: Half-MD5 (64 bits) will do the trick.
	$dataid = substr(hash('md5', $data), 0, 16);

	// $is_comment = (!empty($_POST['parentid']) && !empty($_POST['pasteid'])); // Is this post a comment ?
	$storage = array(
		'data' => $data
	);
	if (count($meta) > 0)
		$storage['meta'] = $meta;  // Add meta-information only if necessary.

	$storagedir = dataid2path($dataid);
	if (!is_dir($storagedir))
		mkdir($storagedir, $mode = 0705, $recursive = true);
	if (is_file($storagedir . $dataid))  // Oups... improbable collision.
	{
		echo json_encode(
			array(
				'status' => 1,
				'message' => 'You are unlucky. Try again.'
			)
		);
		exit();
	}
	// New paste
	file_put_contents($storagedir . $dataid, json_encode($storage), LOCK_EX);

	// Generate the "delete" token.
	// The token is the hmac of the pasteid signed with the server salt.
	// The paste can be delete by calling http://myserver.com/zerobin/?pasteid=<pasteid>&deletetoken=<deletetoken>
	$deletetoken = hash_hmac('sha1', $dataid, getServerSalt());

	echo json_encode(
		array(
			'status' => 0,
			'id' => $dataid,
			'deletetoken' => $deletetoken
		)
	);  // 0 = no error
	exit();
}

if (!empty($_GET['s']) && strlen($_GET['s']) == 4) {
	$urlc = $_GET['s'];
	$code = array(
		substr($urlc, 0, 2),
		substr($urlc, 2, 2)
	);
	$codeenc = aksencode(serialize($code));
	$file = 'data/' . $code[0] . '/' . $code[1] . '/' . urlencode($codeenc);

	if (file_exists($file)) {
		header('location: ' . decrypt(file_get_contents($file)));
		exit();
	}
}

$CIPHERDATA = '';
$ERRORMESSAGE = '';
$STATUS = '';

if (!empty($_GET['deletetoken']) && !empty($_GET['pasteid'])) {
	list($CIPHERDATA, $ERRORMESSAGE, $STATUS) = processPasteDelete($_GET['pasteid'], $_GET['deletetoken']);
} else if (!empty($_SERVER['QUERY_STRING'])) {
	list($CIPHERDATA, $ERRORMESSAGE, $STATUS) = processPasteFetch($_SERVER['QUERY_STRING']);
}
?>