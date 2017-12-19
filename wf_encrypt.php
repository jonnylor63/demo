<?php
// cript e decript stringhe

function wf_encrypt($string, $key, $urlencode=true) { 
	$result = ''; 
	for($i=0; $i<strlen($string); $i++) { 
		$char = substr($string, $i, 1); 
		$keychar = substr($key, ($i % strlen($key))-1, 1); 
		$char = chr(ord($char)+ord($keychar)); 
		$result.=$char; 
	}
	$result =base64_encode($result);
	if ($urlencode) {
		$result =urlencode($result);
	}
	return $result; 
}

function wf_decrypt($string, $key, $urlencode=true) { 
	$result = '';
	$string = base64_decode($string);
	for($i=0; $i<strlen($string); $i++) { 
		$char = substr($string, $i, 1); 
		$keychar = substr($key, ($i % strlen($key))-1, 1); 
		$char = chr(ord($char)-ord($keychar)); 
		$result.=$char; 
	}
	return urldecode($result); 
}