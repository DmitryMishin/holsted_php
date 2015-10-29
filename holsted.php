<?php 
	function getData() {
		$file = fopen("holsted.php", "r");
		if ($file) while (!feof($file)) $text = $text.fread($file, 999);
		fclose($file);
		/*
		$a = 15;
		$b = "string";
		$c = $a.$b;
		*/
		return $text;
	}

	abstract class regExp {
		public $symbolOperators = array(
			"+", "++", "-", "--", "*", "/", "%", "**",
			"=", "==", "===", ".=", "+=", "*=", "/=", "%=", "-=",
			"!", "&&", "||",
			"!=", "<>", "!==", "<", ">", "<=", ">=",
			"|", "^", "~", "<<", ">>", "."
		);
		public $functOperators = array(
			"class", "function", "extends", "public", "abstract", 
			"array", "var", "for", "if", "else", "elseif", "else", "return",
			"preg_match", "preg_match_all", "preg_split", "count", "break", 
			"ereg", "preg_replace", "and", "or", "xor", "echo", "fopen", "fclose", "feof", "fread"
		);
	}

	class holsted extends regExp {
		var $code;
		
		private function enterTabulate($arrCode) {
			$k = 0;
			for ($i = 0; $i < count($arrCode); $i++) { 
				$eregLine = $this->deleteString($arrCode[$i]);
				if (preg_match("/\}/", $eregLine)) $k--;
				for ($j = 0; $j < $k; $j++) 
					$arrCode[$i] = "   ".$arrCode[$i];
				if (preg_match("/\{/", $eregLine)) $k++;
			}
			return $arrCode;
		}
		
		public function getCode () {
			$code = $this->code;
			$arrCode = preg_split("/(\n)/", $code);
			$arrCode = $this->enterTabulate($arrCode);
			$code = "";
			for ($i = 1; $i < count($arrCode) - 1; $i++) {
				if (preg_match("/(\bfunction[^\"]\b)|(\bclass\b)/", $arrCode[$i]))
					$code .= "\n";
				$code .= $arrCode[$i]."\n";
			}
			return $code;
		}
		
		private function deleteString($code) {
			$code = preg_replace("/(\".{0,}?[^\\\]\")/", "\"\"", $code);
			return $code;
		}
		
		private function checkOperandParrent($arrOperands, $name) {
			$result = -1;
			for ($i = 0; $i < count($arrOperands); $i++) {
				if ($arrOperands[$i][0] == $name) {
					$result = $i;
					break;
				}
			}
			return $result;
		}
		
		private function getNameParrent($title, $arrCode, $i) {
			$result = "";
			if (ereg($title, $arrCode[$i])) {
				preg_match("/(".$title."[ ][\w$]+)/", $arrCode[$i], $nameArray);
				$result = $nameArray[1];
			}
			return $result;
		}
		
		private function checkIdentName($initArray, $ident) {
			$result = false;
			for ($i = 1; $i < count($initArray); $i++) 
				if ($initArray[$i] == $ident) $result = true;
			return $result;
		}
		
		private function addIdentClass_Function(&$arrOperands, $code, $checkLine, 
												&$arrayCounter, &$classCounter) {
			if ($checkLine == "class") $classCounter = $arrayCounter;
			elseif ($checkLine == "function") {
				preg_match_all("/([\$][\w\$]+)/", $code, $outOperands);
				for ($i = -1; $i < count($outOperands[1]); $i++) 
					$arrOperands[$arrayCounter][] = $outOperands[1][$i];
			} 
			return $classCounter;
		}
		
		private function getClass_Function(&$arrayCounter, &$classCounter, 
										   &$arrOperands, $code, $parrentElement) {
			$arrayCounter = $this->checkOperandParrent($arrOperands, $parrentElement);
			if ($arrayCounter == -1) $arrayCounter = count($arrOperands);
			
			$checkClass = preg_split("/ /", $parrentElement);
			$classCounter = $this->addIdentClass_Function(
			   $arrOperands, $code, $checkClass[0], $arrayCounter, $classCounter
			);
			return $parrentElement;
		}
		
		public function getStringOperands($code) {
			$code = $this->deleteString($code);
			preg_match_all("/\"\"/", $code, $outArrayStrings);
			return count($outArrayStrings[0]);
		}
		
		public function getNumbersOperands($code) {
			$code = $this->deleteString($code);
			preg_match_all("/[+-]?\d+\.?\d*/", $code, $outArrayNumbers);
			return $outArrayNumbers;
		}
		
		public function getIdentOperands($code) {
			preg_match_all("/[\$](\w+)/", $code, $allOperands);
			for ($i = 0; $i < count($allOperands[1]); $i++) {
				if ($allOperands[1][$i] == "this") unset($allOperands[1][$i]);
			}
			return count($allOperands[1]);
		}
		
		public function getUniqueIdent($code, &$arrOperands, $j, $indexClass, 
									   &$globalIdent) {
			preg_match_all("/([\$][\w\$]+)/", $code, $out);
			if (preg_match("/global\b/", $code) == 1) 
				for ($i = 0; $i < count($out[1]); $i++) 
					$globalIdent[] = $out[1][$i];
			else 
				for ($i = 0; $i < count($out[1]); $i++) 
					if (
						!$this->checkIdentName($globalIdent, $out[1][$i]) && 
						!$this->checkIdentName($arrOperands[$indexClass], $out[1][$i]) && 
						!$this->checkIdentName($arrOperands[$j], $out[1][$i])
					)	
						if ($out[1][$i] != "\$this") $arrOperands[$j][] = $out[1][$i];
			return count($out);
		}
		
		public function getUniqueIdentOperandsArray($code) {
			$arrCode = preg_split("/(\n)/", $code);
			$indexClass = $arrayCounter = $counterTab = 0;
			$arrOperands = array();
			$globalIdent = array();
			$arrOperands[][] = "";
			for ($i = 0; $i < count($arrCode); $i++) {
				if (ereg("\}", $arrCode[$i])) $counterTab--; 
				if (ereg("\{", $arrCode[$i])) $counterTab++;
				if ($counterTab == 0) $arrayCounter = $indexClass = 0;
				$parrentElement = $this->getNameParrent("(class|function)", $arrCode, $i);
				if ($parrentElement != "") {
					$arrOperands[$arrayCounter][0] = $this->getClass_Function(
					   $arrayCounter, $indexClass, $arrOperands, $arrCode[$i], $parrentElement
					);
					$globalIdent[0] = $globalIdent = array(); 
				} elseif (ereg("([\$])", $arrCode[$i])) 
					$this->getUniqueIdent(
					   $arrCode[$i], $arrOperands, $arrayCounter, $indexClass, $globalIdent
					);
			}
			return $arrOperands;
		}
		
		public function getUniqueIdentOperandsCount($code) {
			$identArray = $this->getUniqueIdentOperandsArray($code);
			$count = 0;
			for ($i = 0; $i < count($identArray); $i++)
				$count += count($identArray[$i]) - 1;
			return $count;
		}
		
		public function getUniqueOperandsCount($code) {
			$numbersCount = count($this->getNumbersOperands($code)[0]);
			$stringsCount = $this->getStringOperands($code);
			$identCount = $this->getUniqueIdentOperandsCount($code);
			return $identCount + $numbersCount + $stringsCount;
		}
		
		public function getAllOperandsCount($code) {
			$countIdent = $this->getIdentOperands($this->code);
			$countNumbers = count($this->getNumbersOperands($this->code)[0]);
			$countStrings = $this->getStringOperands($this->code);
			return $countIdent + $countNumbers + $countStrings;
		}
		
		private function getOperatorsSymb($index) {
			$procLine = "/([^[".preg_quote("/$+*<>=|!.-", "/")."\&](";
			$procLine .= preg_quote($this->symbolOperators[$index], "/");
			$procLine .= ")[^[".preg_quote("=+*><-")."])/";
			return $procLine;
		}
		
		private function getOperatorsFunc($index) {
			return "/[^\$]\b".$this->functOperators[$index]."\b/";
		}
		
		public function getUniqueOperatorsFunctArray($code) {
			$newCode = $this->deleteString($code);
			for ($i = 0; $i < count($this->functOperators); $i++)
				if (preg_match("/[^\$]\b".$this->functOperators[$i]."\b/", $newCode))
					$arrOperators[] = $this->functOperators[$i];
			return $arrOperators;
		}
		
		public function getUniqueOperatorsSymbArray($code) {
			$newCode = $this->deleteString($code);
			$arrOperators = array();
			for ($i = 0; $i < count($this->symbolOperators); $i++) {
				$procLine = $this->getOperatorsSymb($i);
				if (preg_match($procLine, $newCode))
					$arrOperators[] = $this->symbolOperators[$i];
			}
			return $arrOperators;
		}
		
		public function getUniqueOperators($code) {
			return array_merge([""], $this->getUniqueOperatorsFunctArray($code), $this->getUniqueOperatorsSymbArray($code)); 
		}
		
		public function getUniqueOperatorsCount($code) {
			return count($this->getUniqueOperators($code)) + count($this->getUniqueIdentOperandsArray($this->code)) - 2; 
		}
		
		private function getAllOperatorsPrototype($code, $operatorsArray, $funcCheck) {
			$code = $this->deleteString($code);
			$count = 0;
			for ($i = 0; $i < count($operatorsArray); $i++) {
				$regLine = $funcCheck ? $this->getOperatorsSymb($i) : $this->getOperatorsFunc($i);
				if (preg_match_all($regLine, $code, $outOperators))
					$count += count($outOperators[0]);
			}
			return $count;
		}
		
		public function getAllOperatorsSymbCount($code) {
			$count = $this->getAllOperatorsPrototype($code, $this->symbolOperators, true);
			return $count;
		}
		
		public function getAllOperatorsFunctCount($code) {
			$count = $this->getAllOperatorsPrototype($code, $this->functOperators, false);
			return $count;
		}
		
		private function countCallFunction($name) {
			$code = $this->code;
			preg_match_all("/\$this\-\>(".$name.")/", $code, $outArrayCall);
			return count($outArrayCall[1]);
		}
		
		private function countAllCallFunctions($arrayFunctions) {
			$count = 0;
			for ($i = 1; $i < count($arrayFunctions); $i++) {
				preg_match("/function (\w+)/", $arrayFunctions[$i][0], $name);
				$name = $name[1];
				$count += $this->countCallFunction($name);
			}
			return $count;
		}
		
		public function getAllOperatorsCount($code) {
			$countFunctions = $this->countAllCallFunctions(
			   $this->getUniqueIdentOperandsArray($this->code)
			);
			return $this->getAllOperatorsFunctCount($code) + 
			   $this->getAllOperatorsSymbCount($code); 
		}
		
		public function getDictionaryProgram($code) {
			return $this->getUniqueOperatorsCount($code) + $this->getUniqueOperandsCount($code);
		}
		
		public function getLengthProgram($code) {
			return $this->getAllOperatorsCount($code) + $this->getAllOperandsCount($code);
		}
		
		public function getScopeProgram($code) {
			return $this->getLengthProgram($code)*log($this->getDictionaryProgram($code),2);
		}
		
		private function getTheoreticFunctions() {
			preg_match_all("/public(.+)/", $this->code, $outArrayFunctions);
			return count($outArrayFunctions[1]);
		}
		
		private function getTheoreticIdent() {
			preg_match_all("/public(.+)/", $this->code, $outArrayFunctions);
			$count = 0;
			for ($i = 0; $i < count($outArrayFunctions[1]); $i++) {
				preg_match_all("/([\$][\w\$]+)/", $outArrayFunctions[1][$i], $outIdentArray);
				$count += count($outIdentArray[1]);
			}
			return $count;
		}
		
		public function getTheoreticDictionary() {
			return $this->getTheoreticIdent() + $this->getTheoreticFunctions();
		}
		
		public function getPotentialScope($code) {
			$n = $this->getTheoreticDictionary();
			return $n * log($n, 2);
		}
		
		public function getTheoreticLength() {
			$code = $this->code;
			$operands = $this->getUniqueOperandsCount($code);
			$operators = $this->getUniqueOperatorsCount($code);
			return $operators * log($operators,2) + $operands * log($operands, 2);
		}
		
		public function getMarkProgramming($code) {
			return $this->getPotentialScope($code) / $this->getScopeProgram($code);
		}
		
		public function getLevelProgram($code) {
			return 2 * $this->getUniqueOperandsCount($code) / ($this->getUniqueOperatorsCount($code) + $this->getAllOperandsCount($code));
		}
		
		public function getIntellectualContent($code) {
			return $this->getLevelProgram($code) * $this->getScopeProgram($code);
		}
		
		public function getIntellectEffort() {
			$code = $this->code;
			return $this->getTheoreticLength() * log(($this->getDictionaryProgram($code) / $this->getMarkProgramming($code)),2);
		}
		
		public function getRealLength() {
			$code = $this->code;
			return $this->getLengthProgram($code) * log(($this->getDictionaryProgram($code) / $this->getMarkProgramming($code)),2);
		}
		
		public function getLastValue() {
			$code = $this->code;
			return $this->getScopeProgram($code) * $this->getScopeProgram($code) / $this->getPotentialScope($code);
		}
		
		public function editCodeForPrint($code) {
			$code = preg_replace("/[\t]{1,}/", "", $code);
			$code = preg_replace("/\/\*.?+[\n\t\s\w\W]{0,}?\*\//", "", $code);
			$code = preg_replace("/\/\/.+/", "", $code);
			$code = preg_replace("/[^\\\]\#.+/", "", $code);
			$code = preg_replace("/\n{1,}/", "\n", $code);
			return $code;
		}
		
		public function setCode ($code) {
			$code = $this->editCodeForPrint($code);
			return $code;
		}
		
		// ---
		// Выделение функций
		// ---
		public function getFunctionArray() {
			$code = $this->code;
			preg_match_all(
			   "/[^\w$\"](function.+[\n\w\W;]{1,}?return.+\n})/", 
			   $code, $functArray
			);
			return $functArray;
		}
		
		public function getTabulateString($arrCode) {
			$code = "";
			for ($i = 0; $i < count($arrCode); $i++) 
				$code .= $arrCode[$i]."\n";
			return $code;
		}
		
		public function getNameFunction($line, $arrOperands) {
			preg_match("/function (\w+)/", $line, $arrName);
			return $arrName[0];
		}
		
		private function getFunctionIndex($name, $arrOperands) {
			$index = -1;
			for ($i = 0; $i < count($arrOperands); $i++) 
				if ($arrOperands[$i][0] == $name)
					$index = $i;
			return $index;
		}
		
		public function print_infoFunction($arrOperands, $className) {
			$result = "<ul class=\"".$className."\">";
			for ($i = 1; $i < count($arrOperands); $i++)
				$result .= "<li>".($i-1)." => ".$arrOperands[$i]."</li>";
			$result .= "</ul>";
			return $result;
		}
		
		private function getFunctionOperands($line, $arrOperands) {
			$name = $this->getNameFunction($line, $arrOperands);
			$functionIndex = $this->getFunctionIndex($name, $arrOperands);
			return $arrOperands[$functionIndex];
		}
		
		private function getCodeFunction_html($firstEl, $secondEl) {
			return "<div class='parsing-code-element'>".$firstEl."</div><div class='circle'></div><div class='parsing-code-element'>".$secondEl."</div>";
		}
		
		private function getAllInfoFunction($code, $arrOperands, $index) {
			$info = $this->print_infoFunction(
			   $this->getFunctionOperands($code, $arrOperands), "operandsName"
			);
			$info .= $this->print_infoFunction(
			   $this->getUniqueOperators($code), "operatorsName"
			);
			$arrCodeLine = preg_split("/(\n)/", $code);
			$code = "<pre><code data-language='php'>".
			   $this->getTabulateString($this->enterTabulate($arrCodeLine)).
			   "</code></pre>";
			return ($index + 1) % 2 ? $this->getCodeFunction_html($code, $info) : $this->getCodeFunction_html($info, $code);
		}
		
		public function getFunctionCode() {
			$codeArray = $this->getFunctionArray();
			$arrOperands = $this->getUniqueIdentOperandsArray($this->code);
			$count = count($codeArray[1]);
			for ($i = 0; $i < $count; $i++) {
				$code .= "<div class='parsing-code-all'>";
				$code .= $this->getAllInfoFunction($codeArray[1][$i], $arrOperands, $i + 1);
				$code .= "</div>";
			}
			return $code;
		}
	}
?>