var counter = 2;


$( document ).on("keypress keyup blur", ".number", function (event) {
	$(this).val($(this).val().replace(/[^0-9\.]/g,''));
	if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
		event.preventDefault();
	}
});
/*--- validate & redeeme form ----*/
$( document ).on("keyup", ".barcode", function (event) {
	$(this).val($(this).val().replace(/[^0-9\.]/g,''));
	if (event.which == 13 || event.which == 0)  event.preventDefault();
	checkDigit($(this), event);
});

function checkDigit($this, event){
	$num = 20;
	$numLenght = $this.val().length;
	$('.error' ).remove();
	if($numLenght == $num){
		event.preventDefault();	
		if(counter <= 100){
			load_tbl($this.val());
			$('.barcode').val("").last().focus();
		}
	}else if ($numLenght == 0 && counter>1){
		$this.closest( '.tbl_prod tr.tr_li' ).remove();
		counter--;	
	}
}
$( document ).on( 'click', '.reset', function ( event ) {
	event.preventDefault();
	$('.tbl_prod tr.tr_li:not(:first), .error' ).remove();
	counter = 1;
	computeTotal();	
} );

$( document ).on( 'click', '.delete', function ( event ) {
	event.preventDefault();
	$(this).closest( '.tbl_prod tr.tr_li' ).remove();
	counter--;	
	computeTotal();	
} );

/******** ----------------- ***********/

if ( window.history.replaceState ) window.history.replaceState( null, null, window.location.href );

/***** --- SELECTION OF PRODUCT ---- *****/
function load_tbl($barcode){
	var table = $('.tbl_prod tr.tr_li:last');
	var table_new = table.clone();	
		table_new.find( 'td:not(:first)' ).html('');	
		table_new.find( '.tbl_voucher' ).html($barcode);
		table_new.find( 'input.voucher' ).val($barcode);	
		table_new.insertAfter( table ).removeClass('hide').removeClass('tablesorter-ignoreRow');
	counter++;	
	computeTotal();
}

function computeTotal(){
	/*-- COMPUTE TOTAL --*/
	$unit = 0;	
	$('.tbl_fv').each(function (index, value) {
		$unit = $unit + parseFloat($(this).html());
	});
	$('.t_unit').html($unit);
	console.log(counter);
}
/******** ----------------- ***********/

(function () {
	console.log('load me');
	computeTotal();
	$('.tablesorter-filter-row').remove();
})();

