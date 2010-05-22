<?php i18n::set('admin'); ?>
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
