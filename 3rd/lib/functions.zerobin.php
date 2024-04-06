<?php
/**
 * Methods are from zerobin file zerobin-lib
 * 
 * @link http://sebsauvage.net/wiki/doku.php?id=php:zerobin
 * @author sebsauvage
 */

/*
 * Process a paste deletion request.
 * Returns an array ('',$ERRORMESSAGE,$STATUS)
 */
function processPasteDelete($pasteid, $deletetoken) {
	if (preg_match ( '/\A[a-f\d]{16}\z/', $pasteid )) // Is this a valid paste identifier ?
{
		$filename = dataid2path ( $pasteid ) . $pasteid;
		if (! is_file ( $filename )) // Check that paste exists.
{
			return array (
					'',
					'Url does not exist, has expired or has been deleted.',
					'' 
			);
		}
	} else {
		return array (
				'',
				'Invalid data',
				'' 
		);
	}
	
	if (! slow_equals ( $deletetoken, hash_hmac ( 'sha1', $pasteid, getServerSalt () ) )) // Make sure token is valid.
{
		return array (
				'',
				'Wrong deletion token. Url was not deleted.',
				'' 
		);
	}
	
	// Paste exists and deletion token is valid: Delete the paste.
	deletePaste ( $pasteid );
	return array (
			'',
			'',
			'Url was properly deleted.' 
	);
}
/*
 * Process a paste fetch request.
 * Returns an array ($CIPHERDATA,$ERRORMESSAGE,$STATUS)
 */
function processPasteFetch($pasteid) {
	if (preg_match ( '/\A[a-f\d]{16}\z/', $pasteid )) // Is this a valid paste identifier ?
{
		$filename = dataid2path ( $pasteid ) . $pasteid;
		if (! is_file ( $filename )) // Check that paste exists.
{
			return array (
					'',
					'Url does not exist, has expired or has been deleted.',
					'' 
			);
		}
	} else {
		return array (
				'',
				'Invalid data',
				'' 
		);
	}
	
	// Get the paste itself.
	$paste = json_decode ( file_get_contents ( $filename ) );
	
	// See if paste has expired.
	if (isset ( $paste->meta->expire_date ) && $paste->meta->expire_date < time ()) {
		deletePaste ( $pasteid ); // Delete the paste
		return array (
				'',
				'Url does not exist, has expired or has been deleted.',
				'' 
		);
	}
	
	// We kindly provide the remaining time before expiration (in seconds)
	if (property_exists ( $paste->meta, 'expire_date' ))
		$paste->meta->remaining_time = $paste->meta->expire_date - time ();
	
	$messages = array (
			$paste 
	); // The paste itself is the first in the list of encrypted messages.
	
	$CIPHERDATA = json_encode ( $messages );
	
	// If the paste was meant to be read only once, delete it.
	if (property_exists ( $paste->meta, 'burnafterreading' ) && $paste->meta->burnafterreading)
		deletePaste ( $pasteid );
	
	return array (
			$CIPHERDATA,
			'',
			'' 
	);
}

// Checks if a json string is a proper SJCL encrypted message.
// False if format is incorrect.
function validSJCL($jsonstring) {
	$accepted_keys = array (
			'iv',
			'v',
			'iter',
			'ks',
			'ts',
			'mode',
			'adata',
			'cipher',
			'salt',
			'ct' 
	);
	
	// Make sure content is valid json
	$decoded = json_decode ( $jsonstring );
	if ($decoded == null)
		return false;
	$decoded = ( array ) $decoded;
	
	// Make sure required fields are present
	foreach ( $accepted_keys as $k ) {
		if (! array_key_exists ( $k, $decoded )) {
			return false;
		}
	}
	
	// Make sure some fields are base64 data
	if (base64_decode ( $decoded ['iv'], $strict = true ) == null) {
		return false;
	}
	if (base64_decode ( $decoded ['salt'], $strict = true ) == null) {
		return false;
	}
	if (base64_decode ( $decoded ['cipher'], $strict = true ) == null) {
		return false;
	}
	
	// Make sure no additionnal keys were added.
	if (count ( array_intersect ( array_keys ( $decoded ), $accepted_keys ) ) != 10) {
		return false;
	}
	
	// Reject data if entropy is too low
	$ct = base64_decode ( $decoded ['ct'], $strict = true );
	if (strlen ( $ct ) > strlen ( gzdeflate ( $ct ) ))
		return false;
		
		// Make sure some fields have a reasonable size.
	if (strlen ( $decoded ['iv'] ) > 24)
		return false;
	if (strlen ( $decoded ['salt'] ) > 14)
		return false;
	return true;
}

/*
 * Convert paste id to storage path.
 * The idea is to creates subdirectories in order to limit the number of files per directory.
 * (A high number of files in a single directory can slow things down.)
 * eg. "f468483c313401e8" will be stored in "data/f4/68/f468483c313401e8"
 * High-trafic websites may want to deepen the directory structure (like Squid does).
 *
 * eg. input 'e3570978f9e4aa90' --> output 'data/e3/57/'
 */
function dataid2path($dataid) {
	return 'data/' . substr ( $dataid, 0, 2 ) . '/' . substr ( $dataid, 2, 2 ) . '/';
}

// Constant time string comparison.
// (Used to deter time attacks on hmac checking. See section 2.7 of https://defuse.ca/audits/zerobin.htm)
function slow_equals($a, $b) {
	$diff = strlen ( $a ) ^ strlen ( $b );
	for($i = 0; $i < strlen ( $a ) && $i < strlen ( $b ); $i ++) {
		$diff |= ord ( $a [$i] ) ^ ord ( $b [$i] );
	}
	return $diff === 0;
}

// Delete a paste and its discussion.
// Input: $pasteid : the paste identifier.
function deletePaste($pasteid) {
	// Delete the paste itself
	unlink ( dataid2path ( $pasteid ) . $pasteid );
	
	// Delete discussion if it exists.
	$discdir = dataid2discussionpath ( $pasteid );
	if (is_dir ( $discdir )) {
		// Delete all files in discussion directory
		$dhandle = opendir ( $discdir );
		while ( false !== ($filename = readdir ( $dhandle )) ) {
			if (is_file ( $discdir . $filename ))
				unlink ( $discdir . $filename );
		}
		closedir ( $dhandle );
		
		// Delete the discussion directory.
		rmdir ( $discdir );
	}
}

/*
 * Convert paste id to discussion storage path.
 * eg. 'e3570978f9e4aa90' --> 'data/e3/57/e3570978f9e4aa90.discussion/'
 */
function dataid2discussionpath($dataid) {
	return dataid2path ( $dataid ) . $dataid . '.discussion/';
}

// trafic_limiter : Make sure the IP address makes at most 1 request every 10 seconds.
// Will return false if IP address made a call less than 10 seconds ago.
function trafic_limiter_canPass($ip) {
	$tfilename = './data/trafic_limiter.php';
	if (! is_file ( $tfilename )) {
		file_put_contents ( $tfilename, "<?php\n\$GLOBALS['trafic_limiter']=array();\n?>", LOCK_EX );
		chmod ( $tfilename, 0705 );
	}
	require $tfilename;
	$tl = $GLOBALS ['trafic_limiter'];
	if (! empty ( $tl [$ip] ) && ($tl [$ip] + 10 >= time ())) {
		return false;
		// FIXME: purge file of expired IPs to keep it small
	}
	$tl [$ip] = time ();
	file_put_contents ( $tfilename, "<?php\n\$GLOBALS['trafic_limiter']=" . var_export ( $tl, true ) . ";\n?>", LOCK_EX );
	return true;
}