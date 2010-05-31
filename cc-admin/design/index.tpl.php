<!doctype html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<title><?php echo Admin::title(); ?> &lsaquo; <?php _e('admin', 'CanyonCMS Admin'); ?></title>

		<link rel="stylesheet" type="text/css" href="<?php echo get_css('form-layout'); ?>" />
		<link rel="stylesheet" type="text/css" href="design/styles.css" />

		<script type="text/javascript" src="<?php echo CC_PUB_ROOT.CC_CONTENT; ?>libraries/js/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="design/admin.js"></script>
	</head>

	<body>
		<div id="header">
			<div class="gutter">
				<h1><?php echo Settings::get('site', 'site name', true); ?> <span><?php _e('admin', 'powered-by', '<a href="http://canyoncms.com/">CanyonCMS</a>'); ?></span></h1>
				<div id="nav">
					<?php echo Admin::menu(); ?>
					<br class="clear" />
				</div>
			</div>
		</div>
		<div id="wrapper">
			<div id="content">
				<div class="gutter">
					<?php echo Admin::content(); ?>
				</div>
			</div>
			<div id="sidebar">
				<div class="gutter">f
					<?php echo AdminSidebar::get(); ?>
				</div>
			</div>
		</div>
	</body>
</html>
