wind = window.dialogArguments || opener || parent || top;

jQuery(document).ready( function($) {

	 $('#salsapress_salsa_activate').click( function() {
			if( $('#salsapress_salsa_activate:checked').length < 1 ) $('input[type=password], input[type=text]').attr('readonly',true);
			else $('input[type=password], input[type=text]').attr('readonly',false);
		});

	$('.option .salsa_key').change( function() {
		if( $(this).val() != '' ) {
			$('.picked').removeClass('picked');
			$(this).parents('.option').addClass('picked');
			$('.option:not(.picked)').each( function () {
				$(this).find('.salsa_key').val('');
				$(this).fadeTo('fast',0.3);
				$(this).children('input[type=checkbox]').attr('checked',false);
			});
			$('input[type=submit]').show();
		} else {
			$(this).parents('form').removeClass('picked');
			$('input[type=submit]').hide();
			$('.option').fadeTo('fast',1);
		}
	});

	$('.option:not(.picked)').hover(
		function() {
			$(this).fadeTo('slow',1);
		},
		function() {
			if( $('.picked').length > 0 && !$(this).hasClass('picked') ) $(this).stop().fadeTo(300,0.3);
		}
	);
	$('#report .salsa_key').change( function() {
		var key = '';
		inputs = '';

		$( $(this).val().slice( $(this).val().search(/\?/) + 1 ).split('&') ).each( function() {
			var line = this.split('=');
			if( line.length > 1 ) {
				key = line[0].toLowerCase().search(/key/) > -1 ? line[1] : key;
				inputs += line[0][0] == 'u' ? 'inputs[]='+line[1] : inputs;
			} else key = this.length == 5 ? this.toString() : key;
		});
		if( key.length > 4 ) {
			$(this).val(key);
			$(this).notice( objectL10n.hold_tight_ok );
			$('input[type=submit]').hide();
			$.get(
				document.location.href.split('?')[0],
				'action=salsapress_salsa_report_render&key='+key+'&'+inputs,
				function(response) {
					if( response[4] > 0 ) $('input[type=submit]').show();
					$('.preview').show().html(response).addClass('active');
					for(i=0; i<$('.preview table th').length ; i=i+1) {
						$('input[name=columns]').val( $('input[name=columns]').val()+i+',');
					}
				}
			);
		} else {
			$(this).notice('mmmm no report there buddy','oops');
			$('input[type=submit]').hide();
			$(this).parents('div.option').removeClass('picked');
		}

	});
	$('#resubmit').live('click',function(e){
		$(this).hide().notice('Getting the updated report...');
		e.preventDefault();
		var input ='';
		$('.preview li.user_variables input').each( function() {
			if( jQuery(this).val() != '' ) input += 'inputs[]='+jQuery(this).val()+'&';
		});
		$.get(
			document.location.href.split('?')[0],
			'action=salsapress_salsa_report_render&key='+$('#report .salsa_key').val()+'&'+input,
			function(response) {
				if( response[4] > 0 ) $('input[type=submit]').show();
				$('div.preview').html(response).addClass('active');
				for(i=0; i<$('.preview table th').length ; i=i+1) {
					$('input[name=columns]').val( $('input[name=columns]').val()+i+',');
				}
			}
		);
	});
	$('span.hide').live('click',function() {
		if( !$(this).hasClass('hidding') ) {
			$(this).text('(+)').addClass('hidding').parents('th').addClass('hiding');
			$(this).parents('table').find('tr').find('td:eq('+$(this).parents('th').index()+')').css('font-size',0);
			$(this).parents('th').find('input[type=text]').hide();
			$('input[name=columns]').val( $('input[name=columns]').val().replace($(this).parents('th').index()+',','') );
		} else {
			$(this).removeClass('hidding').text('(hide)').parents('th').removeClass('hiding');
			if( $('#header:checked').length ) $(this).parents('th').find('input').hide();
			$(this).parents('table').find('tr').find('td:eq('+$(this).parents('th').index()+')').css('font-size','13px');
			$(this).parents('th').find('input[type=text]').show();
			$('input[name=columns]').val( $('input[name=columns]').val()+$(this).parents('th').index()+',' );
		}
	});
	$('form.option').submit(function(event){
		event.preventDefault();
	});
	$('#insert').click(function(event){
		if( $('input[type=submit]:visible').length > 0 ) {
			var is_tinymce_active = (typeof window.parent.tinyMCE != "undefined") && window.parent.tinyMCE.activeEditor
			if( $('.picked textarea[name=after-save]').length > 0 ) $('.picked textarea[name=after-save]').hide().val(escape($('.picked textarea[name=after-save]').val().replace(/\n/g,'<br>')))
			var serial = JSON.stringify($('.picked').serializeArray())
			serial = serial.substring(1, serial.length-1);
			if( is_tinymce_active) {
				var shortcode = '<img class="salsa mceItem" style="border: 1px dashed #888;" title="';
				shortcode += "salsa data='"
				shortcode += serial.replace(/&/g, "&amp;").replace(/>/g, "&gt;").replace(/</g, "&lt;").replace(/"/g, "&quot;")
				shortcode += "'"
				shortcode += ' \' " src="'+SalsaPressVars.stylesheet_directory+'images/salsaembed.png" alt="" data-mce-src="'+SalsaPressVars.stylesheet_directory+'images/salsaembed.png" data-mce-style="border: 1px dashed #888;">';
			} else {
				var shortcode  = ["[salsa data=", serial, "]"].join("'")
			}
			wind.send_to_editor(shortcode);
		}
		event.stopPropagation();
		return false;
	});
	$('#cancel').click(function(event){
		w.tb_remove();
		event.stopPropagation();
		return false;
	});
	$('#header:not(:checked)').live('click',function() {
		$('.preview table th:not(.hiding) input').show();
	});
	$('#header:checked').live('click',function() {
		$('.preview table th input').hide();
	});
	$('#list:checked').live('click',function() {
		$('#list_options').show();
	});
	$('#list:not(:checked)').live('click',function() {
		$('#list_options').hide();
	});
	$('h3.reset_caches').click( function() {
		$this = $(this);
		$.getJSON(SalsaPressVars.ajaxurl,{
			action : 'salsapress_reset_caches'
		}, function( response ) {
			if( response.success ) { $this.parent('td').text( objectL10n.success ); }
		});
	});
});




