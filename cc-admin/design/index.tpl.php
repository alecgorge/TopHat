<!doctype html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<title><?php echo Admin::pageTitle(); ?> &lsaquo; <?php _e('admin', 'CanyonCMS Admin'); ?></title>

		<?php
		load_library(array('form-layout', 'jquery', 'messages'));
		queue_css(CC_PUB_ADMIN.'design/styles.css');
		queue_js(CC_PUB_ADMIN.'design/admin.js');
		
		load_css();
		load_js();
		?>
	</head>

	<body class="<?php echo Admin::bodyClasses(); ?>">
		<div id="header">
			<div class="gutter">
				<h1><a href="<?php echo CC_PUB_ROOT; ?>"><?php echo Settings::get('site', 'site name', true); ?></a> <span><?php _e('admin', 'powered-by', '<a href="http://canyoncms.com/">CanyonCMS</a>'); ?></span></h1>
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
					<div class="topper"><h2><?php list($title, $content) = Admin::content(); echo $title; ?></h2></div>
                    <div class="contents">
                        <?php echo $content; ?>
                    </div>
				</div>
			</div>
			<div id="sidebar">
				<div class="gutter">
					<?php echo AdminSidebar::get(); ?>
				</div>
			</div>
            <div id="footer">
                <p>Copyright &copy; 2011 Alec Gorge</p>
            </div>
		</div>
	</body>
</html>
