<?php
function json_decode_comment( &$txt )
{
	if ( empty($txt) )
		return 0;

	// CRLF -> LF
	if ( strpos($txt, "\r\n") !== false )
		$txt = str_replace("\r", ''  , $txt);
	if ( strpos($txt, "\r") !== false )
		$txt = str_replace("\r", "\n", $txt);

	// remove comments
	//   multi-line   /* ... */
	//   single-line  // ...
	while (1)
	{
		$st = strpos($txt, '/*');
		if ( $str === false )
			break;

		$ed = strpos($txt, '*/', $st+2);
		if ( $ed === false ) // missing end tag == until file end
			$ed = strlen($txt);
		else
			$ed += 2;

		$sub = substr($text, $st, $ed-$st);
		$txt = str_replace($sub, '', $text);
	} // while (1)

	$line = explode("\n", $txt);
	foreach ( $line as $k => $v )
	{
		$ln = trim($v);
		$st = strpos($ln, '//');
		if ( $st !== false )
			$ln = substr($ln, 0, $st);
		$line[$k] = $ln;
	} // foreach ( $line as $k => $v )

	$txt = implode('', $line);
	return json_decode($txt, true);
}
