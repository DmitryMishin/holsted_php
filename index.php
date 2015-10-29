<html>
	<head>
		<title>Метрика Холстеда</title>
		<link type="text/css" href="css/style.css" rel="stylesheet">
		<link type="text/css" href="css/font-awesome.min.css" rel="stylesheet">
		<link type="text/css" href="css/tomorrow-night.css" rel="stylesheet">
	</head>
	<body>		
		<? include('holsted.php') ?>
		<?php
			$code = new holsted;
			$text = getData();
			$code->code = $code->setCode($text);
		?>
		<header>
			<div id="header">
				<div class="content">
					<div class="header-left title">Метрика Холстеда</div>
				</div>
			</div>
		</header>
		<div class="content">
			<div class="left-block">
				<div class="left-menu-block">
					<nav>
						<ul class="left-menu">
							<li class="menu-all-code active" rel="all-code"><i class="fa fa-code"></i>Весь код</li>
							<li class="menu-parsing-code" rel="parsing-code"><i class="fa fa-outdent"></i>Разбор кода</li>
							<li class="menu-indications" rel="indications"><i class="fa fa-bar-chart"></i>Показания метрики</li>
						</ul>
					</nav>
				</div>
			</div>
			<div class="right-block">
				<div class="all-code content_tab">
					<h1><i class="fa fa-file-code-o"></i>Весь код:</h1>
					<div>
						<pre>
							<code data-language="php">
								<?php echo $code->getCode(); ?>
							</code>
						</pre>
					</div>
				</div>	
				
				<div class="parsing-code content_tab">
					<h1><i class="fa fa-file-code-o"></i>Разбор кода:</h1>
					<?php echo $code->getFunctionCode(); ?>
				</div>
				
				<div class="indications content_tab">
					<h1 class="stats"><i class="fa fa-bar-chart-o"></i>Показания метрики:</h1>
					<? include('informations.php') ?>
				</div>
			</div>
		</div>
		<script src="js/rainbow-custom.min.js"></script>
		<script src="js/jquery-1.11.3.min.js"></script>
		<script src="js/scripts.js"></script>
	</body>
</html>