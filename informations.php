<div class="main_info">
	<div class="main_all_info">
		<p>
		<strong>Длина</strong> программы: <?php echo $code->getLengthProgram($code->code); ?>
		</p>
		<p>
			Число <strong>всех</strong> операторов: <?php echo $code->getAllOperatorsCount($code->code); ?>
		</p>
		<p>
			Число <strong>всех</strong> операндов: <?php echo $code->getAllOperandsCount($code->code); ?>. Из них:
			<ul>
				<li>Строковых операндов: <?php echo $code->getStringOperands($code->code); ?></li>
				<li>Числовых операндов: <?php echo count($code->getNumbersOperands($code->code)[0]) ?></li>
				<li>Идентификаторов: <?php echo $code->getIdentOperands($code->code); ?></li>
			</ul>
		</p>
	</div>

	<div class="main_unique_info">
		<p>
			<strong>Словарь</strong> программы: <?php echo $code->getDictionaryProgram($code->code); ?>
		</p>
		<p>
			Число <strong>уникальных</strong> операторов: <?php echo $code->getUniqueOperatorsCount($code->code); ?>
		</p>
		<p>
			Число <strong>уникальных</strong> операндов: <?php echo $code->getUniqueOperandsCount($code->code); ?>. Из них:
			<ul>
				<li>Строковых операндов: <?php echo $code->getStringOperands($code->code); ?></li>
				<li>Числовых операндов: <?php echo count($code->getNumbersOperands($code->code)[0]); ?></li>
				<li>Идентификаторов: <?php echo $code->getUniqueIdentOperandsCount($code->code); ?></li>
			</ul>
		</p>
	</div>
</div>

<div class="main_info_answer"><span>Объём программы: <?php echo $code->getScopeProgram($code->code); ?></span></div>
<div class="info_other">
	<span>Остальные показатели:</span>
	<ul class="info_other_list">
		<li>Теоретический словарь программы: <?php echo $code->getTheoreticDictionary(); ?></li>
		<li>Теоретический объём программы: <?php echo $code->getPotentialScope($code->code); ?></li>
		<li>Теоретическая длина программы: <?php echo $code->getTheoreticLength(); ?></li>
		<li>Качество программирования: <?php echo $code->getMarkProgramming($code->code); ?></li>
		<li>Уровень программы: <?php echo $code->getLevelProgram($code->code); ?></li>
		<li>Интеллектуальное содержание программы: <?php echo $code->getIntellectualContent($code->code); ?></li>
		<li>Необходимые интеллектуальные усилия: <?php echo $code->getIntellectEffort(); ?></li>
		<li>Реальная длина: <?php echo $code->getRealLength(); ?></li>
	</ul>
</div>