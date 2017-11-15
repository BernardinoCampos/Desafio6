<?php

$fatores = ['A'=>'2','B'=>'3','C'=>'5','D'=>'7','E'=>'11','F'=>'13','G'=>'17','H'=>'19','I'=>'23','J'=>'29','K'=>'31',
		    'L'=>'37','M'=>'41','N'=>'43','O'=>'47','P'=>'53','Q'=>'59','R'=>'61','S'=>'67','T'=>'71','U'=>'73','V'=>'79',
			'W'=>'83','X'=>'89','Y'=>'97','Z'=>'101'];
$dicionario = [];

bcscale(48);

function removeLetras(array $arr1, array $arr2) {
	foreach($arr2 as $k2=>$v2)
		foreach($arr1 as $k1=>$v1)
			if ($v1==$v2) {
				unset($arr1[$k1]);
				break;
			}

	return (array_values($arr1));
}

function calculaFator(array $word) {
	GLOBAL $fatores;

	$fator = "1";

	for ($ptr=0; $ptr<Count($word); $ptr++) {
		$letra = $word[$ptr];
		$fator=bcmul($fator,$fatores[$letra]);
	}
	return $fator;
}

function retAnagramas(array $word) {
	GLOBAL $dicionario;
	$fatorWord = calculaFator($word);

	$ret = [];
	foreach($dicionario as $fator=>$anagramas) {
		if (bccomp($fator,$fatorWord)>0)
			break;
		if (bccomp(bcmod($fatorWord,$fator),'0')==0) {
			foreach($anagramas as $anag) {
				$resto = retAnagramas(removeLetras($word, $anag));

				if (Count($resto)>0) {
					foreach ($resto as $str)
						$ret[] = implode('',$anag)." ".$str;
				}
				else
					$ret[] = implode('',$anag);
			}
		}
	}
	return $ret;
}

function getDicionario() {
	$dicionario = [];

	if (!file_exists('words.dict')) {
		$dict = explode("\n",file_get_contents("words.txt"));

		foreach($dict as $word) {
			$arr = str_split($word);
			$fator = calculaFator($arr);
			$dicionario[$fator][] = $arr;
		}

		unset($dicionario[0]);

		ksort($dicionario);

		file_put_contents('words.dict', gzdeflate(serialize($dicionario)));
	}
	else
		$dicionario = unserialize(gzinflate(file_get_contents('words.dict')));

	return $dicionario;
}

$dicionario = getDicionario();

$word = preg_replace( '/[^A-Z]/', '',strtoupper(filter_input(INPUT_POST, 'word', FILTER_SANITIZE_STRING)));

$result = [];

if (strlen($word)>1) {
	$inicio = microtime(True);
	$result = [];
	foreach (retAnagramas(str_split($word)) as $line) {
		if (strlen(preg_replace('/[^A-Z]/', '', $line))!=strlen($word))
			continue;
		$tmp = explode(' ',$line);
		sort($tmp);
		$result[] = implode(' ',$tmp);
	}
	sort($result);
	$result = array_unique($result);
	$tempo = microtime(True) - $inicio;
}
?>

<html>
	<body>
		<form action='/desafio6.php' method='post'>
			<input type='text' name='word' value='<?=$word?>'><br>
			<input type='submit' name='action' value='Processe'<br>
		</form>

	<? if (Count($result)>0) : ?>
		Tempo decorrido: <?= $tempo?><BR>
		Resultado:<BR>

		<? foreach($result as $res): ?>
			<?= $res ?><BR>
		<? endforeach; ?>
	<? endif; ?>
	</body>
</html>
