<?php if(isset($nav_div) && $nav_div == 1){ ?>
</div>

<!-- Modal -->
<div class="modal" id="" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id=""></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body"> </div>
      <div class="modal-footer">
			<button type="button" class="modal-btn btn btn-primary" data-id=""></button>
      </div>
    </div>
  </div>
</div>
<?php }	?>

</div>
<footer id="footer" class="container">
	<div class="row wrapper clearfix">
	   <div class="sodexo-contacts fright col-xs-12">
			<p>&#169; PLUXEE <?php echo date('Y')?></p>
		</div>
	</div>
</footer>

<script type="text/javascript" src="assets/js/jquery.min.js"></script> 
<script type="text/javascript" src="assets/js/jquery.autocomplete.js"></script> 
<?php if(isset($nav_div) && $nav_div == 1){ ?>
<script type="text/javascript" src="assets/js/bootstrap-datetimepicker.js"></script>
<script type="text/javascript" src="assets/jquery-loading-overlay-1.5.4/loadingoverlay.js"></script>
<script type="text/javascript" src="assets/jquery-loading-overlay-1.5.4/loadingoverlay.min.js"></script>
<script type="text/javascript" src="assets/js/bootstrap-notify.js"></script>
<script type="text/javascript" src="assets/js/bootstrap-select.js"></script>
<?php }?>
<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>

<?php 
if(isset($jsDT) && count($jsDT) != 0){
	for($ijsDT=0;$ijsDT<count($jsDT);$ijsDT++){
		if(!empty($jsDT[$ijsDT]))echo '<script src="assets/dataTables/'.$jsDT[$ijsDT].'"></script>';
	}
}	
?>
<script type='text/javascript' > 
  var AjaxReq = false;
  var iLoad = false;
  var myTable;
  var tblID;
  var page;
  var search = '';
  var BASEURL = '<?php echo base_url();?>';
  var checkArr;
</script>

<?php if(isset($nav_div) && $nav_div == 1){ ?>
<script type="text/javascript" src="assets/js/modules/main.js?<?php echo time()?>"></script>
<?php }?>
<?php 
if(isset($js) && count($js) != 0){
	for($ijs=0;$ijs<count($js);$ijs++){
		if(!empty($js[$ijs]))echo '<script src="assets/js/modules/'.$js[$ijs].'?'.time().'"></script>';
	}
}	
?>
<script>
if ( window.history.replaceState ) {
  window.history.replaceState( null, null, window.location.href );
}
</script>
</body>
</html>
