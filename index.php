<link href="style.css" rel="stylesheet" type="text/css" />

<?php

require_once 'libs/Code39/barcode.php';

$pdo = new PDO('sqlite:../TenthStats/database.sqlite3');
global $pdo;

//$pdo = new PDO();

$q = $pdo->prepare("SELECT BarCode, DateOfBirth FROM People WHERE (FirstName = :f AND LastName = :l) OR (GoesByName = :f AND LastName = :l) ORDER BY IndividualNumber DESC;");
$q2 = $pdo->prepare("SELECT BarCode, DateOfBirth FROM People WHERE (FirstName LIKE :f AND LastName = :l) OR (GoesByName LIKE :f AND LastName = :l) ORDER BY IndividualNumber DESC;");


$files = glob('cropped/*.{jpg}', GLOB_BRACE);
foreach($files as $file) {
	$parsed = explode(".", $file);
	$parsed = explode(" ", $parsed[0]);

	if (count($parsed) > 3) {
		$parsed[1] = $parsed[1] . " " . $parsed[2];
		$parsed[2] = array_pop($parsed);
	}

	$q->execute([':f' => $parsed[1], ':l' => $parsed[2]]);
	$r = $q->fetchAll(PDO::FETCH_NUM);
	if (count($r) < 1) {
		$q2->execute([':f' => $parsed[1] . "%", ':l' => $parsed[2]]);
		$r = $q2->fetchAll(PDO::FETCH_NUM);
	}

//	if (count($r) > 1) {
//		throw new Exception("Ambiguous Records! for " . $parsed[1] . " " . $parsed[2]);
//	}

	if (!isset($r[0]) || $r[0][0] == '') {
		$c = new Code39($parsed[1] . $parsed[2]);
//		$c = new Code39(1);
	} else {
		$c = new Code39($r[0][0]);
	}

	?>
	<div class="passportPage">
		<img src="tenthLogo.png" class="tenthLogo" />
		<table>
			<tr>
				<td class="passportLabel">Passport<br />Passeport<br />Pasaporte</td>
				<td class="wowLabel">Wide Open World</td>
			</tr>
			<tr>
				<td><img class="passportPhoto" src="<?php echo $file ?>" /></td>
				<td>
					<table class="infoTable">
						<tr><td>Surname / Nom / Apellidos</td></tr>
						<tr><td><?php echo $parsed[2]; ?></td></tr>
						<tr><td>Given Names / Prénoms / Nombres</td></tr>
						<tr><td><?php echo $parsed[1]; ?></td></tr>

<?php if (isset($r[0]) && $r[0][1] != '') { ?>
						<tr><td>Birthday / Anniversaire / Cumpleaños</td></tr>
						<tr><td><?php echo substr($r[0][1],0,-5); ?></td></tr>
<?php } ?>

						<tr><td>Year of Issue / Année d'émission / Año de Emisión</td></tr>
						<tr><td><?php echo date("Y"); ?></td></tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<?php
					try {
						$c->toHtmlTag(1000, 10);
					} catch (Exception $e) {
						throw new Exception($parsed[1]. " " . $parsed[2]);
					}
					?>
				</td>
			</tr>
		</table>
	</div>
	<?php
}



