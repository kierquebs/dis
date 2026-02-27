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

/* **** AMOUNT DETAILS FUNCTION **** */
$( document ).on( 'keyup', '.qnty, .deno, .bfirst', function ( event ) {
	event.preventDefault();
	if($(this).hasClass('qnty')){
		$denoVal = $(this).prev('.deno').val();
		if($denoVal == '') $(this).prev('.deno').addClass('error'); 
		else amountDetails($(this)); 
	}else if($(this).hasClass('bfirst')){
		$qntyVal = $(this).prev('.qnty').val();
		console.log($qntyVal);
		if($qntyVal == '') $(this).prev('.qnty').addClass('error'); 
		else amountDetails($(this)); 
	}else{
		$(this).removeClass('error');
		amountDetails($(this));
	}
});
	function amountDetails($me, $parent = ""){
		$me.removeClass('error');
		var parentDiv = ($parent == "" ? $me.parent($parent) : $parent);
		$deno = $(parentDiv).find('.deno').val();
		$qnty = $(parentDiv).find('.qnty').val();
		$bfirst = $(parentDiv).find('.bfirst').val();		
		if($qnty != ''){
			$qnty = parseInt($qnty);
			$(parentDiv).find('.total').val(parseInt(($deno * $qnty)));
			if($bfirst != '') $(parentDiv).find('.blast').val(parseInt((($qnty + parseInt($bfirst))) - 1));
		}
		
	}	
/* **** END AMOUNT DETAILS FUNCTION **** */

(function () {
	suggestionBox();
})();
function suggestionBox(){
	/* auto company */
	$('.auto-company').autocomplete({
		serviceUrl: 'transaction/companies',
		onSelect: function (suggestion) {
			$('.auto-company').attr('data-id', suggestion.data);
		}
	});
	$('.auto-company').attr({'autocomplete':'off'});
	
	
	/* auto request distri */
	$('.auto-ocore, .auto-order').click(function(){
		$('.auto-ocore, .auto-order').val("");$("form[name='add-order'] input").val("");$('.search-div i.bal').text('');
	})
	$('.auto-ocore').autocomplete({
		serviceUrl: 'suggestion/suggest_order',
		onSelect: function (suggestion) {
			console.log(suggestion);
			$('.auto-ocore').attr({'data-id':suggestion.data});
		$arr = suggestion.arr;		
			$('.search-div input[name="ocid"]').val(suggestion.data);
			$('.search-div input[name="origqty"]').val($arr.qty);
			$('.search-div input[name="bal"]').val($arr.bal);
			$('.search-div input[name="prod"]').val($arr.prod);
			$('.search-div input.orderid').val($arr.orderid);
			$('.search-div input.po').val($arr.po);
			$('.search-div input.deno').val($arr.deno);
			$('.search-div i.bal').text($arr.bal);
		}
	});
	$('.auto-ocore').attr({'autocomplete':'off'});
	
	/* auto company */	
	$('.auto-dest').autocomplete({
		serviceUrl: 'suggestion/suggest_dest',
		onSelect: function (suggestion) {
			$('.auto-dest').attr({'data-id':suggestion.data});
		}
	});
	$('.auto-dest').attr({'autocomplete':'off'});
	
	$('.auto-order').autocomplete({
		serviceUrl: 'suggestion/li_order',
		onSelect: function (suggestion) {
			$('.auto-order').attr({'data-id':suggestion.data});
		}
	});
	$('.auto-po').autocomplete({
		serviceUrl: 'suggestion/li_po',
		onSelect: function (suggestion) {
			$('.auto-po').attr({'data-id':suggestion.data});
		}
	});
	$('.auto-deno').autocomplete({
		serviceUrl: 'suggestion/li_deno',
		onSelect: function (suggestion) {
			$('.auto-deno').attr({'data-id':suggestion.data});
		}
	});
	$('.auto-order, .auto-po, .auto-deno').attr({'autocomplete':'off'});
	
}

function dropMdel($element, $selected = 0){
	$element.empty();
	$.getJSON("suggestion/get_mdel", function(data) {
		$element.append("<option disabled "+($selected == 0 ? 'selected' : '')+">--- GET STATUS ---</option>");
		$.each(data.stat_mo, function(key, value) {
			if($selected != 0 && value.id == $selected) $element.append("<option value='" + value.id + "' selected>" + value.name + "</option>");
			else $element.append("<option value='" + value.id + "'>" + value.name + "</option>");
		});
	}); 
}


