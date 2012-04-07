<!DOCTYPE html>
<html>
	<head>
		<title><?php echo breadcrumbs(' &lsaquo; '); ?> &laquo; <?php echo Settings::get('site.site name'); ?></title>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		
		<link rel="stylesheet" type="text/css" name="stylesheet" href="<?php echo theme_dir(); ?>style.css" />
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
		<script type="text/javascript">
			$(function () {
				$('img').each(function () {
					$this = $(this);
					var float = $this.css('float');
					if(float == 'left' || float == 'right') {
						$this.addClass('float-'+float);
						if($this.position().top == 0) {
							$this.addClass('no-top-margin');
						}
					}
				});
			});
		</script>
	</head>
	<body>
		<div id="wrapper">
			<div id="header">
				<h1>Hôtel-Pensao Sol Na Baïa à Brava</h1>
			</div>
			<div id="main">
				<div id="navigation">
					<?php echo nav(); ?>
				</div>
				<div id="content">
					<div class="gutter">
						<h2><?php echo title(); ?></h2>
						<div class="page" style="position:relative;"><?php echo content(); ?></div>
						<div class="clear"></div>
					</div>
				</div>
				<div id="footer">
					<div class="gutter">
						<p>Copyright &copy; 2011 Hôtel-Pensao Sol Na Baïa à Brava</p>
					</div>
				</div>			
			</div>
		</div>
	</body>
</html>