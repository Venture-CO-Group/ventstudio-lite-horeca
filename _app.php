<?php
/* Physical-page loader — lets every route work without URL rewriting.
   Each /page/index.php sets $LT_VIEW/$LT_PAGE/$LT_ROUTE then requires this. */
require __DIR__ . '/inc/bootstrap.php';
$GLOBALS['C'] = $C;
$GLOBALS['ROUTE_PATH'] = isset($LT_ROUTE) ? $LT_ROUTE : '';
$GLOBALS['PAGE'] = isset($LT_PAGE) ? $LT_PAGE : '';
http_response_code(200);
require __DIR__ . '/views/' . $LT_VIEW;
