<!doctype html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<title><?php echo Admin::title(); ?> &lsaquo; <?php echo __('admin', 'CanyonCMS Admin'); ?></title>

		<link rel="stylesheet" type="text/css" href="design/css/main.css" />
	</head>

	<body>
		<div id="header">

		</div>
		<div id="wrapper">
			<div id="content">
				<div class="gutter">

				</div>
			</div>
			<div id="sidebar">
				<div class="gutter">

				</div>
			</div>
		</div>
	</body>
</html>

<title><?php echo Admin::title(); ?> &lsaquo; CanyonCMS Admin</title><?php
echo Admin::menu();
?><h2><?php echo Admin::title(); ?></h2><?php
 Admin::content();
?>
