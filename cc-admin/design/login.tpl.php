<?php i18n::set('admin'); ?>
<h2><?php _e('login'); ?></h2>
<?php 
$form = new Form('self', 'post', 'test');
$form->addInput("Username", 'text', 'uname');
$form->addInput("Password", 'password', 'passwd');

echo $form->endAndGetHTML();

i18n::restore();
?>
