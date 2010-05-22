<?php i18n::set('admin'); ?><!doctype html>
<html>
	<head>
		<title><?php _e('login_cc'); ?></title>
		<link type="text/css" rel="stylesheet" href="<?php echo get_css('form-layout'); ?>" />
		<link type="text/css" rel="stylesheet" href="<?php echo CC_PUB_ADMIN.'design/styles.css'; ?>" />
	</head>
	<body>
		<h2><?php _e('login_cc'); ?></h2>
		<?php
		$form = new Form('self', 'post', 'test');
		$form->startFieldset(__('Login'));
			$form->addInput(__("Username"), 'text', 'uname');
			$form->addInput(__("Password"), 'password', 'passwd');
			$form->addSubmit('', 'login', __('login_cc'));
		$form->endFieldset();

		echo $form->endAndGetHTML();

		i18n::restore();
		?>
	</body>
</html>
