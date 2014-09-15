<?php 
	$barang = $GLOBALS['barang'];
	$host = 'http://' . $_SERVER['HTTP_HOST'];
	extract($barang);
	
	$harga_jual = '<strong class="text-success">Rp. ' . $jual . ',-</strong>';
	if ( ! empty($diskon)) {
		$h = number_format(str_replace('.', '', $jual) - str_replace('.', '', $diskon), 0, '.', ',');
		$harga_jual = '<strong class="text-error wrong">Rp. ' . $jual . ',-</strong> ';
		$harga_jual .= '<strong class="text-success">Rp. ' . $h . ',-</strong>';
	}
?>
<div class="box transparent">
	<div class="padded10">
		<div class="row-fluid">
			<div class="span5">
				<div class="well">
					<img src="<?php echo $host . '/' . $foto ?>" alt="barang">
				</div>
			</div>
			<div class="span7">
				<table class="table">
					<tbody>
						<tr>
							<td colspan="2">
								<h4 class="section-title"><?php echo $nama ?></h4>
							</td>
						</tr>
						<tr>
							<td width="30%"><strong>Kode Barang</strong></td>
							<td>: BLUE</td>
						</tr>
						<tr>
							<td><strong>Harga Jual</strong></td>
							<td>: <?php echo $harga_jual ?></td>
						</tr>
						<tr>
							<td><strong>Stok Barang</strong></td>
							<td>: <?php echo $stok ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12 well">
				<blockquote>
					<p><?php echo $info ?></p>
				</blockquote>
			</div>
		</div>
	</div>
</div>