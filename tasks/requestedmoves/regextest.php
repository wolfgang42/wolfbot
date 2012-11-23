<?php
// Talk:F. B. Hurndall
$contents="==Requested move==
{{Requested move/dated|Frank Brereton Hurndall}}

[[F. B. Hurndall]] → {{no redirect|Frank Brereton Hurndall}} – per references{{unsigned|Richard Arthur Norton (1958- )}} 00:07, 22 November 2012 (UTC)
";
$regex1 = "/\{{2}\s?(Requested move\/dated|movereq|rename)\s?[^}]*\}{2}";$regex2="/i";
$regex2 = "([0-2]\d):([0-5]\d),\s(\d{1,2})\s(\w*)\s(\d{4})\s\(UTC\).*/i";
$regex1 .= "\n*.*";
preg_match($regex1 . $regex2, $contents, $m);
var_dump($m);

