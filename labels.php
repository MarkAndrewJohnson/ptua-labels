<?php
ini_set('memory_limit', '64M');
if(isset($_FILES['current']) || isset($_FILES['expired'])) {
	$members = (object) array('current' => array(), 'expired' => array());
	
	/* get current members */
	if(isset($_FILES['current'])) {
		if(!$_FILES['current']['size']) {
			unlink($_FILES['current']['tmp_name']);
		} else {
			$doc = explode("\n", file_get_contents($_FILES['current']['tmp_name']));
			$header = str_getcsv(array_shift($doc));
			unlink($_FILES['current']['tmp_name']);

			foreach($doc as $row) 
			{
				$row = str_getcsv($row);
				if(count($header) == count($row)) {
					$members->current[] = (object) array_combine($header, $row);
				}
			}

			unset($doc);

			foreach($members->current as $member) {
				$member->label_zip = '';
				$member->label_address1 = '';
				$member->label_address2 = '';
				$member->label_country = '';
				$member->label_state = '';
				$member->label_city = '';
				if($member->mailing_zip) {
					$member->label_zip = $member->mailing_zip;
					$member->label_address1 = $member->mailing_address1;
					$member->label_address2 = $member->mailing_address2;
					$member->label_country = $member->mailing_country;
					$member->label_state = $member->mailing_state;
					$member->label_city = $member->mailing_city;
				} else if($member->address_zip) {
					$member->label_zip = $member->address_zip;
					$member->label_address1 = $member->address_address1;
					$member->label_address2 = $member->address_address2;
					$member->label_country = $member->address_country;
					$member->label_state = $member->address_state;
					$member->label_city = $member->address_city;
				} else if($member->primary_zip) {
					$member->label_zip = $member->primary_zip;
					$member->label_address1 = $member->primary_address1;
					$member->label_address2 = $member->primary_address2;
					$member->label_country = $member->primary_country;
					$member->label_state = $member->primary_state;
					$member->label_city = $member->primary_city;
				}
				$member->label_expires = '';
				$member->label_member_name = $member->full_name;
			}
		}
	}

	/* get expired members */
	if(isset($_FILES['expired'])) {
		if(!$_FILES['expired']['size']) {
			unlink($_FILES['expired']['tmp_name']);
		} else {
			$doc = explode("\n", file_get_contents($_FILES['expired']['tmp_name']));
			$header = str_getcsv(array_shift($doc));
			unlink($_FILES['expired']['tmp_name']);

			foreach($doc as $row) 
			{
				$row = str_getcsv($row);
				if(count($header) == count($row)) {
					$members->expired[] = (object) array_combine($header, $row);
				}
			}
			
			unset($doc);

			foreach($members->expired as $member) {
				$member->label_zip = '';
				$member->label_address1 = '';
				$member->label_address2 = '';
				$member->label_country = '';
				$member->label_state = '';
				$member->label_city = '';
				if($member->mailing_zip) {
					$member->label_zip = $member->mailing_zip;
					$member->label_address1 = $member->mailing_address1;
					$member->label_address2 = $member->mailing_address2;
					$member->label_country = $member->mailing_country;
					$member->label_state = $member->mailing_state;
					$member->label_city = $member->mailing_city;
				} else if($member->address_zip) {
					$member->label_zip = $member->address_zip;
					$member->label_address1 = $member->address_address1;
					$member->label_address2 = $member->address_address2;
					$member->label_country = $member->address_country;
					$member->label_state = $member->address_state;
					$member->label_city = $member->address_city;
				} else if($member->primary_zip) {
					$member->label_zip = $member->primary_zip;
					$member->label_address1 = $member->primary_address1;
					$member->label_address2 = $member->primary_address2;
					$member->label_country = $member->primary_country;
					$member->label_state = $member->primary_state;
					$member->label_city = $member->primary_city;
				}
				$member->label_expires = 'Membership due';
				$member->label_member_name = $member->full_name;
			}
		}
	}

	define('EXPIRED_MONTHS', 6);

	if(preg_match('/Mac OS X/', $_SERVER['HTTP_USER_AGENT']))
	{
		define('LEFT_MARGIN', 10);		
		define('LEFT_OFFSET', 2);
		define('TOP_OFFSET', 18);
		define('CELL_SPACING', 3);
		define('CELL_WIDTH', 64);
		define('CELL_HEIGHT', 35);
	}
	else
	{
		define('LEFT_OFFSET', 0);
		define('TOP_OFFSET', 16);
		define('CELL_SPACING', 5);
		define('CELL_WIDTH', 64);
		define('CELL_HEIGHT', 35);
	}

/*
	header("Cache-Control: ");
	header("Pragma: ");
	header("Content-Type: text/PDF\n");
	header("Content-Disposition: attachment; filename=\"".$name."\"\n");
	header("Content-Transfer-Encoding: binary\n");
	header("Content-Length: " . filesize($full) . "\n");
	*/

	include_once('fpdf/fpdf.php');	
	$pdf = new FPDF('P','mm','A4');
	$pdf->SetFont('Arial','',12);
//	$pdf->SetFont('Arial','B',14);

	if(defined('LEFT_MARGIN'))
	{
		$pdf->SetLeftMargin(LEFT_MARGIN);
	}

	$i = 0;
	$j = 0;
	$page = 0;
	foreach($members->expired as $member)
	{
		// check row/column
		if($j == 3)
		{
			$j = 0;
			$i++;
		}
		if($i == 8) $i = 0;
		if($i == 0 && $j == 0)
		{
			$pdf->AddPage();
			$page++;
			$pdf->Text(85, 3, "Page {$page}");
		}

		$base_y = ($i * CELL_HEIGHT) + TOP_OFFSET;
		$base_x = ($j * CELL_WIDTH) + 11 + (($j - 1) * CELL_SPACING) + LEFT_OFFSET;
		$pdf->SetFont('Arial','B',9);
		$pdf->Text($base_x, $base_y, $member->label_expires);
		$pdf->setXY($base_x, $base_y + 1);
		$pdf->MultiCell(60, 4, $member->label_member_name, 0, 'L', 0);
		$pdf->SetFont('Arial','',9);
		$pdf->setXY($base_x, $base_y + (($pdf->getY() - $base_y) > 6 ? 10 : 5));
		if($member->label_country == 'Australia') {
			$pdf->MultiCell(60, 4,
				($member->label_address2 ? $member->label_address2 . ', ' . $member->label_address1 : $member->label_address1) . "\n" .  
				$member->label_city . ' ' . $member->label_state . ' ' . $member->label_zip, 0, 'L', 0);
		} else {
			$pdf->MultiCell(60, 4,
				($member->label_address2 ? $member->label_address2 . ', ' . $member->label_address1 : $member->label_address1) . "\n" .  
				$member->label_city . ' ' . $member->label_zip . "\n" .
				$member->label_country, 0, 'L', 0);
		}
		$j++;
	} // foreach:member

	$pdf->SetFont('Arial','',12);

	$i = 0;
	$j = 0;
	foreach($members->current as $member)
	{
		// check row/column
		if($j == 3)
		{
			$j = 0;
			$i++;
		}
		if($i == 8) $i = 0;
		if($i == 0 && $j == 0)
		{
			$pdf->AddPage();
			$page++;
			$pdf->Text(85, 3, "Page {$page}");
		}

		$base_y = ($i * CELL_HEIGHT) + TOP_OFFSET;
		$base_x = ($j * CELL_WIDTH) + 11 + (($j - 1) * CELL_SPACING) + LEFT_OFFSET;

		$pdf->SetFont('Arial','B',9);
		if(strpos(strtolower($member->membership_names), 'indefinite') >= 0) {
		   	$_exp = '';
		} else {
			$_exp = $member->label_expires;
		}
		$pdf->setXY($base_x, $base_y + 1);
		$pdf->MultiCell(65, 4, $member->label_member_name, 0, 'L', 0);
		$pdf->SetFont('Arial','',9);
		$pdf->setXY($base_x, $base_y + (($pdf->getY() - $base_y) > 6 ? 10 : 5));
		if($member->label_country == 'Australia') {
			$pdf->MultiCell(65, 4,
				($member->label_address2 ? $member->label_address2 . ', ' . $member->label_address1 : $member->label_address1) . "\n" .  
				$member->label_city . ' ' . $member->label_state . ' ' . $member->label_zip, 0, 'L', 0);
		} else {
			$pdf->MultiCell(65, 4,
				($member->label_address2 ? $member->label_address2 . ', ' . $member->label_address1 : $member->label_address1) . "\n" .  
				$member->label_city . ' ' . $member->label_zip . "\n" .
				$member->label_country, 0, 'L', 0);
		}
		$j++;
	} // foreach:member
	
	$pdf->Output();
} else { ?>
<html>
<head>
</head>
<body>
	<h1>PTUA Label Generator</h1>

	<form id="upload-form" action="/labels.php" enctype="multipart/form-data" method="post">
		<fieldset>
			<label for="file-current">Current Members</label>
			<input type="file" name="current" id="file-current" />
			<br />
			<label for="file-expired">Expired Members</label>
			<input type="file" name="expired" id="file-expired" />
			<br />
			<input type="submit" name="submit" value="Upload now" />
		</fieldset>
	</form>
</body>
</html>
<?php
}
?>
