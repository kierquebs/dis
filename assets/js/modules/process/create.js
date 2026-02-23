(function () {
	datePick();
	setDatatbl('#queue-tbl', false, true);
	addItem();
	window.setTimeout($(".alert-success").fadeOut("slow", function(){
		$(".alert").remove(); 
		window.location.href = BASEURL+'reqdist';}) , 6000);
})();
$( document ).on( 'keyup', '.qntyReq', function ( event ) {
	event.preventDefault();
	$myParent = $(this).parent(".form-div").parent();
	$reqVal = parseInt($(this).val());
	$($myParent).find('.total').val(0);
	if($('.search-div input[name="bal"]').val() < $reqVal){
		$(this).addClass('error'); 
		$('.today-error').html("<span class='error'>Invalid Quantity!</span>");
	}else{
		$(this).removeClass('error'); $('.today-error').html("");	
		if($reqVal != ''){
			$deno = $($myParent).find('.deno').val();
			$($myParent).find('.total').val(parseInt(($deno * $reqVal)));
		}
	}	
});

function addItem(){				
	$('#add-item').click(function(){
		$OCID = $('.search-div input[name="ocid"]').val();
		$ORIGQTY = $('.search-div input[name="origqty"]').val();
		$BAL = $('.search-div input[name="bal"]').val();
		$PROD = $('.search-div input[name="prod"]').val();
		$MDEL = $('.search-div select[name="mdel"]').val();
		$reqQty = $('.search-div .qntyReq').val();
		
		/*CHECK REQUIRED DATA*/
		$error = 0;$('.today-error').html("");
		$("input.required").each(function(){
			if($(this).val() == ''){
				$(this).addClass('error');
				$('.today-error').html("<span class='error'>PLEASE FILL UP REQUIRED FIELDS!</span>");	
				$error = 1;
			}
		});
		/*$(".queue-tr").each(function(){
			if($OCID == $(this).attr('data-id')){
				$error = 1;				
				$('.today-error').html("<span class='error'>ITEM ALREADY EXIST!</span>");	
			}
		});*/
		
		if($MDEL == '' || $MDEL == null){
			$error = 1;
			$('.today-error').html("<span class='error'>EMPTY MODE OF DELIVERY!</span>");
		} 
		
		if($OCID != '' && $ORIGQTY != ''&& $error == 0 && ($MDEL != '' && $MDEL != null) && $reqQty > 0){			
			var result = [];
			for (x = 1; x <= 11; x++) { 
				result.push(''); 
			}
			$dest = $('.search-div .dest').val();
			$mdel = $('.search-div select[name="mdel"]').val();
			$mdelName = $('.search-div select[name="mdel"] :selected').text();
			$remarks = $('.search-div .remarks').val();
			$pcode = $('.search-div .pcode').val();
			
			$inputOc = $('<input />').attr({'type': 'hidden', 'name':'create[ocid][]', 'value':$OCID});
			$inputQty = $('<input />').attr({'type': 'hidden', 'name':'create[reqty][]', 'value':$reqQty });
			$inputDest = $('<input />').attr({'type': 'hidden', 'name':'create[dest][]', 'value':$dest });
			$inputMdel = $('<input />').attr({'type': 'hidden', 'name':'create[mdel][]', 'value':$mdel });
			$inputRemarks = $('<input />').attr({'type': 'hidden', 'name':'create[remarks][]', 'value':$remarks });
			$inputPcode = $('<input />').attr({'type': 'hidden', 'name':'create[pcode][]', 'value':$pcode });
				
			
			$delBTN = $('<button></button>').addClass('li-remove btn btn-danger glyphicon glyphicon-trash cs-btn').attr({'title':'DELETE', 'data-id':$OCID});		
				
			var newRow = myTable.row.add(result).draw().node(); 
				$(newRow).addClass('queue-tr').attr('data-id', $OCID) 					
						.find('td:nth-child(1)').text($PROD).end()		
						.find('td:nth-child(2)').text($pcode).append($inputPcode).end()			
						.find('td:nth-child(3)').append($('.search-div .orderid').val()).append($inputOc).append($inputQty).end()
						.find('td:nth-child(4)').text($('.search-div .po').val()).end()
						.find('td:nth-child(5)').addClass('number').text($('.search-div .deno').val()).end()
						.find('td:nth-child(6)').addClass('qty number').text($reqQty).end()
						.find('td:nth-child(7)').addClass('total number').text($('.search-div .total').val()).end()
						.find('td:nth-child(8)').text($dest).append($inputDest).end()
						.find('td:nth-child(9)').text($mdelName).append($inputMdel).end()	
						.find('td:nth-child(10)').text($remarks).append($inputRemarks).end()	
						.find('td:nth-child(11)').append($delBTN).end();
			totalFooter();
			$("form[name='add-order'] input, .search-div select[name='mdel']").val("");$('.search-div i.bal').text('');	 
		}else $('.today-error').append(" <span class='error'>INVALID REQUEST!</span>");
	})
}

$( document ).on( 'click', '.li-remove', function ( event ) {
	event.preventDefault();
	//$(this).closest( '.queue-tr' ).remove();	
	myTable.row($(this).closest( '.queue-tr') ).remove().draw();
	$("form[name='add-order'] input").val("");$('.search-div i.bal').text('');	
	totalFooter();
} );

function totalFooter(){
	var total = qty = 0;
	$(".queue-tr").each(function(){
		total += parseInt($(this).find('td.total').text());
		qty += parseInt($(this).find('td.qty').text());
	});
	$("#foot_total").text(total);
	$("#foot_qty").text(qty);
	
	if(total != 0) $("#create-req").show();
	else $("#create-req").hide();	
	
	$(".number").digits();	
}

