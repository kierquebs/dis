var counter = 2;
$( document ).on( 'keyup', '.order-input', function ( event ) {
	event.preventDefault();
	$(this).val(this.value.match(/[0-9]*/));
});

$( document ).on("keypress keyup blur", ".number", function (event) {
	$(this).val($(this).val().replace(/[^0-9\.]/g,''));
	if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
		event.preventDefault();
	}
});
$( document ).on( 'click', '.btn-add', function ( event ) {
	event.preventDefault();
	if(counter>10){
		
	}else{
		var field = $(this).closest( '.multi-div' );
		var field_new = field.clone();

		$(this)
			.toggleClass( 'btn-default' )
			.toggleClass( 'btn-add' )
			.toggleClass( 'btn-danger' )
			.toggleClass( 'btn-remove' )
			.html( '&ndash;' );

		field_new.find( 'input' ).val('').removeClass('error');
		field_new.insertAfter( field );
		
		counter++;
	}
});
$( document ).on( 'click', '.btn-remove', function ( event ) {
	event.preventDefault();
	if(counter>1){		
		$(this).closest( '.multi-div' ).remove();
		computeFV(); counter--;				
	}	
} );
$( document ).on( 'click', '#clickSubmit', function ( event ) {
	event.stopPropagation();
});
$( document ).on( 'keyup', '.qnty', function ( event ) {
	event.preventDefault();
	$denoVal = $(this).prev('.deno').val();
	if($denoVal == '') $(this).prev('.deno').addClass('error'); 
	else computeFV(); 
});
$( document ).on( 'keyup', '.deno', function ( event ) {
	event.preventDefault();
	$(this).removeClass('error');
	computeFV(); 
});
function computeFV($operation = 1){	
	var multiDIV = $(".qnty-deno");
	var totalAmount = 0;  
	$.each(multiDIV, function(i, item) {  //i=index, item=element in array
		$deno = $(item).find('.deno').val();
		$qnty = $(item).find('.qnty').val();
		totalAmount += ($deno * $qnty); 
	});
	$('#amount').val(parseInt(totalAmount));
}

(function () {
	suggestionBox();
})();
function suggestionBox(){
	$('.auto-company').autocomplete({
		serviceUrl: 'home/companies',
		onSelect: function (suggestion) {
			$('.auto-company').attr('data-id', suggestion.data);
		}
	});
	$('.auto-company').attr({'autocomplete':'off'});
}

