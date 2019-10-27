<?php
	require_once(__DIR__ .'/../includes/autoload.php');

 	$sqll = sprintf("SELECT * FROM " . TABLE_APPLY . " WHERE contest_id = '%s' ORDER BY id DESC", $contest_id);
 	try {
		$stmt = $DB->prepare($sqll);	
		$stmt->execute();
		$downloader = $stmt->fetchAll();
		$count = count($downloader);
	} catch (Exception $ex) {
	   echo errorMessage($ex->getMessage());
	}

	$endpage = ceil($count/$perpage);
	$startpage = 1;
	$nextpage = $curpage + 1;
	$previouspage = $curpage - 1;

	$cd = new contestDelivery;
	$cd->start = $start;
	$cd->perpage = $perpage;
	$results = $cd->viewApplications($contest_id, 0);
?>

<?php
foreach ($results as $rs) {
echo'	
  <tbody>
    <tr>
      <th scope="row">1</th>
      <td>'.$rs['firstname'].' '.$rs['lastname'].'</td>
      <td>'.$rs['city'].'</td>  
      <td>'.$rs['state'].'</td>
      <td>'.$rs['country'].'</td>  
    </tr>
    <tr> 
  </tbody>';
} ?>

<?php if ($endpage >1) { ?>	
	<div class="btn-group">
		<div class="w3-container w3-padding">
			<div class="w3-center">
				<div class="w3-bar w3-border w3-round navigator-color">
				
					<?php if($curpage != $startpage){ ?>
						<a href="?page=<?php echo $startpage ?>" tabindex="-1" class="btn btn-default"><i class="fa fa-angle-left"></i></a>
					<?php } ?>
					
					<?php if($curpage >= 2){ ?>
						<a href="?page=<?php echo $previouspage ?>"><span class="btn btn-default"> <?php echo $previouspage ?></span></a>
					<?php } ?>
						
						<a href="?page=<?php echo $curpage ?>" ><span class="btn btn-default <?php if(isset($curpage)){echo("active");}  ?>"><?php echo $curpage ?></span></a>
					
					<?php if($curpage != $endpage){ ?>
						<a href="?page=<?php echo $nextpage ?>" ><span class="btn btn-default"><?php echo $nextpage ?></span></a>
						<a href="?page=<?php echo $endpage ?>" class="btn btn-default"><i class="fa fa-angle-right"></i></a>
					<?php } ?>    
				</div>
			</div>
		</div>
	</div> 
<?php } ?> 
	 