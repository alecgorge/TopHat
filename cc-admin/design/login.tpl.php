<?php i18n::set('admin'); ?><!doctype html>
<html>
	<head>
		<title><?php _e('login_cc'); ?></title>

		<?php
		load_library(array('form-layout', 'messages'));
		queue_css(CC_PUB_ADMIN.'design/styles.css');

		load_css();
		load_js();
		?>
	</head>
	<body>
		<div id="login-wrapper">
			<h2><?php _e('login_cc'); ?></h2>
			<?php
			$form = new Form('self', 'post', 'login');
				$form->startFieldset(__('Login'));
					$form->addInput(__("Username"), 'text', 'cc_login_uname');
					$form->addInput(__("Password"), 'password', 'cc_login_passwd');
					$form->addInput(__("remember-me"), 'checkbox', 'cc_login_remember', 'yes');
					$form->addSubmit('', 'cc_login_login', __('login_cc'));
				$form->endFieldset();
			echo $form->endAndGetHTML();

			i18n::restore();
			?>
		</div>
	</body>
</html>
