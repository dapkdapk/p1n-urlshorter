/**
 * Methods are from zerobin file zerobin-js
 * 
 * @link http://sebsauvage.net/wiki/doku.php?id=php:zerobin
 * @author sebsauvage
 */

// Immediately start random number generator collector.
sjcl.random.startCollectors();

/**
 * Compress a message (deflate compression). Returns base64 encoded data.
 * 
 * @param string
 *            message
 * @return base64 string data
 */
function compress(message) {
	return Base64.toBase64(RawDeflate.deflate(Base64.utob(message)));
}

/**
 * Decompress a message compressed with compress().
 */
function decompress(data) {
	return Base64.btou(RawDeflate.inflate(Base64.fromBase64(data)));
}

/**
 * Compress, then encrypt message with key.
 * 
 * @param string
 *            key
 * @param string
 *            message
 * @return encrypted string data
 */
function zeroCipher(key, message) {
	return sjcl.encrypt(key, compress(message));
}
/**
 * Decrypt message with key, then decompress.
 * 
 * @param key
 * @param encrypted
 *            string data
 * @return string readable message
 */
function zeroDecipher(key, data) {
	return decompress(sjcl.decrypt(key, data));
}

function scriptLocation() {
	var scriptLocation = window.location.href.substring(0,
			window.location.href.length - window.location.search.length
					- window.location.hash.length);
	var hashIndex = scriptLocation.indexOf("#");
	if (hashIndex !== -1) {
		scriptLocation = scriptLocation.substring(0, hashIndex)
	}
	return scriptLocation
}

/**
 * Return the deciphering key stored in anchor part of the URL
 */
function pageKey() {
	var key = window.location.hash.substring(1); // Get key

	// Some stupid web 2.0 services and redirectors add data AFTER the anchor
	// (such as &utm_source=...).
	// We will strip any additional data.

	// First, strip everything after the equal sign (=) which signals end of
	// base64 string.
	i = key.indexOf('=');
	if (i > -1) {
		key = key.substring(0, i + 1);
	}

	// If the equal sign was not present, some parameters may remain:
	i = key.indexOf('&');
	if (i > -1) {
		key = key.substring(0, i);
	}

	// Then add trailing equal sign if it's missing
	if (key.charAt(key.length - 1) !== '=')
		key += '=';

	return key;
}