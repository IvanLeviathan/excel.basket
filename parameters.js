function OnConditionsInit(arParams){
	setTimeout(function(){
		var $inp = $(arParams.oInput),
			$parent = $inp.closest('.bxcompprop-content-table'),
			inpData = $inp.val();


		getFileData($parent, arParams);
	}, 500);
}

function getFileData($parent, arParams){
	var pathToFile = $parent.find('[name="'+arParams.data.FILE_INPUT_NAME+'"]').val();
	$.ajax({
		type: "POST",
		url: arParams.data.COMPONENT_PATH+'/get_file.php',
		data: {
			"filePath":pathToFile,
			"getFileData": 'Y'
		},
		success: function(data){
			var json = false;
			try {
				json = JSON.parse(data);
			} catch (e) {
				var json = false;
			}
			if(!!json)
				drawFields(json, arParams);
		},
		error: function(data){
			alert('Oops. Look in console.');
			console.log(data);
		}
	});



}
function drawFields(json, arParams){
	var columns = json.DATA.COLUMNS,
		rows = json.DATA.ROWS,
		$wrapper = $(arParams.oCont);


	$wrapper.find('.conditions-wrapper').remove();
	$wrapper.append('<div class="conditions-wrapper"></div>');
	$wrapper = $wrapper.find('.conditions-wrapper');

	drawProductCheckerSelect(columns, arParams, $wrapper);

}
function drawConditionsTemplate(arParams, selected = false){
	var conditionsTemplate = ''
		conditionsTemplate += '<div class="'+arParams.data.IS_PRODUCT_CONDITION_ID+'">';
			conditionsTemplate += '<select multiple id="'+arParams.data.IS_PRODUCT_CONDITION_ID+'">';
			for(var key in arParams.data.IS_PRODUCT_CONDITIONS){
				var condition = arParams.data.IS_PRODUCT_CONDITIONS[key];
				conditionsTemplate += '<option value="'+key+'">'+condition+'</option>';
			}
			conditionsTemplate += '</select>';
		conditionsTemplate += '</div>';
	return conditionsTemplate;
}

function changeNumberInName(name, newNumber){
	return name.replace(/[0-9]/g, newNumber);
}

function removeUserTypeCondition(minusBtnId){
	var $lastUserTypeWrappers = $('div[data-type="userTypeWrapper"]'),
		$lastUserTypeWrapper = $lastUserTypeWrappers.last();

	$lastUserTypeWrapper.slideUp(function(){
		$lastUserTypeWrapper.remove();
	})

	if($lastUserTypeWrappers.length <= 2)
		$('#'+minusBtnId).hide();


	return false;
}
function addUserTypeCondition(minusBtnId){
	var $lastUserTypeWrapper = $('div[data-type="userTypeWrapper"]').last(),
		lastNumber = $lastUserTypeWrapper.data('num'),
		newNumber = lastNumber + 1,
		$clone = $lastUserTypeWrapper.clone();

	$clone.insertAfter($lastUserTypeWrapper).hide().slideDown();
	$clone.data('num', newNumber).attr('data-num', newNumber);

	var $cloneInputs = $clone.find(":input");
	if($cloneInputs.length){
		$cloneInputs.each(function(){
			var $cloneInput = $(this),
				id = $cloneInput.attr('id');
			$cloneInput.attr('id', changeNumberInName(id, newNumber));
		});
	}

	if(newNumber > 1)
		$('#'+minusBtnId).show();

	return false;
}

function drawUserTypesSelect(columns, arParams){
	var num = 1;
	select = '';
	select += '<div class="condition" id="'+arParams.data.IS_USERTYPES_ID+'">';

		select += '<div data-type="userTypeWrapper" data-num="'+num+'" style="border-bottom: 2px solid #000;margin-bottom: 10px;padding-bottom: 10px;">';
			select += '<div class="and">'+arParams.data.IT_IS_USERTYPES_SYMBOL+'</div>';
			select += '<input type="text" id="'+arParams.data.IS_USERTYPES_SUMBOL_ID+'_'+num+'" />';



			select += '<div class="and">'+arParams.data.IT_IS_USERTYPES_PRICE+'</div>';
			select += '<select id="'+arParams.data.IS_USERTYPES_PRICE_COLUMN_ID+'_'+num+'">';
				for(var key in columns){
					var column = columns[key];
					select += '<option value="'+column+'">'+column+'</option>';
				}
			select += '</select>';

			select += '<div class="and">'+arParams.data.IT_IS_USERTYPES_QUANT+'</div>';
			select += '<select id="'+arParams.data.IS_USERTYPES_QUANT_COLUMN_ID+'_'+num+'">';
				for(var key in columns){
					var column = columns[key];
					select += '<option value="'+column+'">'+column+'</option>';
				}
			select += '</select>';

		select += '</div>';

		// btns
		select += '<div style="text-align:right; font-size: 25px;">';
			select += '<a id="'+arParams.data.USERTYPES_MINUS_ID+'" href="javascript:void(0);" onclick=\'removeUserTypeCondition("'+arParams.data.USERTYPES_MINUS_ID+'")\' title="'+arParams.data.MINUS+'" style="color: red;text-decoration:none;display:none;">-</a>';
			select += '&nbsp;';
			select += '<a id="'+arParams.data.USERTYPES_PLUS_ID+'" href="javascript:void(0);" onclick=\'addUserTypeCondition(\"'+arParams.data.USERTYPES_MINUS_ID+'\")\' title="'+arParams.data.PLUS+'" style="color: green;text-decoration:none;">+</a>';
		select += '</div>';

	select += '</div>';
	return select;
}

function drawProductCheckerSelect(columns, arParams, $wrapper){
	var select = '';

	select += '<div class="condition" id="'+arParams.data.IS_PRODUCT_ID+'">';
		select += '<div class="and">'+arParams.data.IT_IS_PRODUCT_1+'</div>';
		select += '<select id="'+arParams.data.IS_PRODUCT_SELECT_ID+'">';
			for(var key in columns){
				var column = columns[key];
				select += '<option value="'+column+'">'+column+'</option>';
			}
		select += '</select>';
		select += drawConditionsTemplate(arParams);
	select += '</div><hr/>';

	// is uniq?
	select += '<div class="condition" id="'+arParams.data.IS_UNIQ_ID+'">';
		select += '<div class="and">'+arParams.data.IT_IS_UNIQ+'</div>';
		select += '<select id="'+arParams.data.IS_UNIQ_SELECT_ID+'">';
			for(var key in columns){
				var column = columns[key];
				select += '<option value="'+column+'">'+column+'</option>';
			}
		select += '</select>';
	select += '</div><hr/>';

	select += drawUserTypesSelect(columns, arParams);

	// is price?
	// select += '<div class="condition" id="'+arParams.data.IS_PRICE_ID+'">';
	// 	select += '<div class="and">'+arParams.data.IT_IS_PRICE+'</div>';
	// 	select += '<select id="'+arParams.data.IS_PRICE_SELECT_ID+'">';
	// 		for(var key in columns){
	// 			var column = columns[key];
	// 			select += '<option value="'+column+'">'+column+'</option>';
	// 		}
	// 	select += '</select>';
	// select += '</div><hr/>';
	//
	// // is quant?
	// select += '<div class="condition" id="'+arParams.data.IS_QUANT_ID+'">';
	// 	select += '<div class="and">'+arParams.data.IT_IS_QUANT+'</div>';
	// 	select += '<select id="'+arParams.data.IS_QUANT_SELECT_ID+'">';
	// 		for(var key in columns){
	// 			var column = columns[key];
	// 			select += '<option value="'+column+'">'+column+'</option>';
	// 		}
	// 	select += '</select>';
	// select += '</div>';

	$(select).appendTo($wrapper);

	putDataToInputs(arParams);

	$('body').on('change', '.conditions-wrapper :input', function(){
		var values = {},
			$inputs = $('.conditions-wrapper :input');

		if($inputs.length){
			$inputs.each(function(){
				var $input = $(this),
					id = $input.attr('id'),
					dataId = $input.data('id');

				var val = $input.val();
				values[id] = val;
			});
		}
		$(arParams.oInput).val(base64_encode(JSON.stringify(values)));
	});


}
function putDataToInputs(arParams){
	var $valueInp = $(arParams.oInput),
		value = $valueInp.val(),
		json = false;
	try {
		json = JSON.parse(base64_decode(value));
	} catch (e) {

	}
	if(!json)
		return false;

	for (var key in json) {
		var val = json[key];

		if(key.indexOf(arParams.data.IS_USERTYPES_SUMBOL_ID) > -1 && key != arParams.data.IS_USERTYPES_SUMBOL_ID+'_1')
			addUserTypeCondition(arParams.data.USERTYPES_MINUS_ID);

		$('#'+key).val(val);
	}

}
function base64_encode( data ) {	// Encodes data with MIME base64
	//
	// +   original by: Tyler Akins (http://rumkin.com)
	// +   improved by: Bayron Guevara

	var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	var o1, o2, o3, h1, h2, h3, h4, bits, i=0, enc='';

	do { // pack three octets into four hexets
		o1 = data.charCodeAt(i++);
		o2 = data.charCodeAt(i++);
		o3 = data.charCodeAt(i++);

		bits = o1<<16 | o2<<8 | o3;

		h1 = bits>>18 & 0x3f;
		h2 = bits>>12 & 0x3f;
		h3 = bits>>6 & 0x3f;
		h4 = bits & 0x3f;

		// use hexets to index into b64, and append result to encoded string
		enc += b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
	} while (i < data.length);

	switch( data.length % 3 ){
		case 1:
			enc = enc.slice(0, -2) + '==';
		break;
		case 2:
			enc = enc.slice(0, -1) + '=';
		break;
	}

	return enc;
}

function base64_decode(s) {
    var e={},i,b=0,c,x,l=0,a,r='',w=String.fromCharCode,L=s.length;
    var A="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    for(i=0;i<64;i++){e[A.charAt(i)]=i;}
    for(x=0;x<L;x++){
        c=e[s.charAt(x)];b=(b<<6)+c;l+=6;
        while(l>=8){((a=(b>>>(l-=8))&0xff)||(x<(L-2)))&&(r+=w(a));}
    }
    return r;
};
