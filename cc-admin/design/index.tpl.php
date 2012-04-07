<!doctype html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<title><?php echo Admin::pageTitle(); ?> &lsaquo; <?php _e('admin', 'TopHat Admin'); ?></title>

		<?php
		load_library(array('bootstrap-css', 'jquery', 'bootstrap-js'));
		queue_css(TH_PUB_ADMIN.'design/styles.css');
		queue_js(TH_PUB_ADMIN.'design/admin.js');
		
		load_css();
		load_js();
		?>
	</head>

	<body class="<?php echo Admin::bodyClasses(); ?>">
		<div id="topnavbar" class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>
					<a class="brand" href="<?php echo TH_PUB_ROOT; ?>" rel="tooltip" title='<?php _e('admin', 'view-pub-site'); ?>' target='_blank'>
						<?php echo Settings::get('site', 'site name', true); ?>
					</a>
					<div class="nav-collapse">
						<?php echo Admin::menu(); ?>
						<ul class="nav pull-right">
							<li class="dropdown">
								<?php $editLink = Admin::link('users/edit-user', array('id' => Users::currentUser()->getId())); ?>
								<a href="<?php echo $editLink; ?>" class="dropdown-toggle" data-toggle="dropdown">
									<i class="icon-user icon-white"></i>
									<?php echo Users::currentUser()->getName() ?>
									<b class="caret"></b>
								</a>
								<ul class="dropdown-menu">
									<li><a href="<?php echo $editLink; ?>"><i class="icon-user"></i> Edit account</a></li>
									<li class="divider"></li>
									<li><a href="#"><i class="icon-eject"></i> Logout</a></li>
								</ul>
							</li>
						</ul>
					</div>
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
					<h2 class="hidden">Sidebar</h2>
					<?php echo AdminSidebar::get(); ?>
				</div>
			</div>
            <div id="footer">
                <p>Copyright &copy; <?php echo date('Y'); ?> Alec Gorge</p>
            </div>
		</div>
	</body>
</html>
