<?php
require(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/app/uses.php');

unset($csss, $scripts);

$scripts = array();
$csss[] = '404';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo APP_CONFIG::$app['title']; ?></title>
<?php require(CONFIG_DIR . 'scripts.php'); ?>
</head>

<body>
<div id="box404">&nbsp;</div>
</body>
</html>
