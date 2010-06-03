<!doctype html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<title><?php echo Admin::title(); ?> &lsaquo; <?php _e('admin', 'CanyonCMS Admin'); ?></title>

		<?php
		load_library(array('form-layout', 'jquery', 'messages'));
		queue_css(CC_PUB_ADMIN.'design/styles.css');
		queue_js(CC_PUB_ADMIN.'design/admin.js');
		
		load_css();
		load_js();
		?>
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
			<div id="status">
				<p>Status here.</p>
			</div>
			<div id="content">
				<div class="gutter">
					<?php echo Admin::content(); ?>
				</div>
			</div>
			<div id="sidebar">
				<div class="gutter">
					<?php echo AdminSidebar::get(); ?>
				</div>
			</div>
		</div>
	</body>
</html>
