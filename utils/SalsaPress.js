var count = 0;
(function( $ ){

	$.fn.notice = function(msg, flavor, time) {

	if ( time == undefined ) var time = 5000;

	var thisclass = 'notice '+flavor;

	this.next('span').remove();
	this.after('<span class="'+thisclass+'">'+msg+'</span>').next('span').fadeOut(time, function() {$(this).remove() });
	return this;
	}

	$.fn.grabevents = function() {
			if( running ) {
			count++;
			running == false;
			$.post(SalsaPressVars.ajaxurl,
			{
				action : 'myajax-event-lookup',
				request : 'object=event&condition=Start<='+high_date.getFullYear()+'-'+("0"+(high_date.getMonth()+1)).slice(-2)+'-'+('0'+high_date.getDate()).slice(-2)+'&condition=Start>='+low_date.getFullYear()+'-'+("0"+(low_date.getMonth()+1)).slice(-2)+'-'+('0'+low_date.getDate()).slice(-2)+'&include=Event_Name&include=Start&include=End&include=Description&include=This_Event_Costs_Money&orderBy=Start'+$screen,
				SalsaAjax : SalsaPressVars.SalsaAjax,
				object : 'event',
				doing : 'gets'
			},function( response ) {
				if( typeof response[0] != 'undefined' && response[0].result == 'error' ) {
					running = true;
					$().grabevents();
					if( count > 2 ) $('#loading').append('<p>This is getting kind of old aint it? Guess something\'s broke, send an email to <a href="mailto:support@busproject.org">support@busproject.org</a> tell us it\'s broken</p>')
				} else {
					events = events.concat(response);
					var r = new Array();
					o:for(var i = 0, n = events.length; i < n; i++) {
						for(var x = 0, y = r.length; x < y; x++) {
							if(r[x].key==events[i].key) continue o;
						}
						r[r.length] = events[i];
					}
					events = r;
					if( high_date > max_high_date) max_high_date = new Date(high_date);
					if( low_date < max_low_date) max_low_date = new Date(low_date);
					running = true;
					$().drawcalendar();
					count = 0;
				}
			});
			}
	}

	$.fn.rangechecker = function() {
		var new_high_date = new Date(this_day.getFullYear(),this_day.getMonth()+3,7);
		var new_low_date = new Date(this_day.getFullYear(),this_day.getMonth()-3,25);

		//$('#agenda').html('<strong>max_high_date:</strong>'+max_high_date+' <strong>max_low_date:</strong>'+max_low_date+' <strong>running:</strong> '+running);
		if( max_low_date > new_low_date && max_high_date < new_high_date ) {
			low_date = new Date(this_day.getFullYear(),this_day.getMonth()-9,25);
			high_date = new Date(this_day.getFullYear(),this_day.getMonth()+9,25);
			$().grabevents();
		} else if( max_high_date < new_high_date ) {
			low_date = high_date;
			high_date = new Date(this_day.getFullYear(),this_day.getMonth()+9,7);
			$().grabevents();
		} else if ( max_low_date > new_low_date ) {
			high_date = low_date;
			low_date = new Date(this_day.getFullYear(),this_day.getMonth()-9,25);
			$().grabevents();
		}
	}

	$.fn.resizer = function(offset) {
		if( !offset ) var offset = 70;
		var the_thing = (
			parseInt(this.css('margin-bottom').split('p')[0])+
			parseInt(this.css('margin-top').split('p')[0])+
			parseInt(this.css('padding-bottom').split('p')[0])+
			parseInt(this.css('padding-top').split('p')[0])+
			parseInt(this.css('border-bottom-width').split('p')[0])+
			parseInt(this.css('border-top-width').split('p')[0])+
			this.height()+
			this.position().top+
			offset
		);
		var parent_thing = (
			parseInt(this.parent().css('margin-bottom').split('p')[0])+
			parseInt(this.parent().css('margin-top').split('p')[0])+
			parseInt(this.parent().css('padding-bottom').split('p')[0])+
			parseInt(this.parent().css('padding-top').split('p')[0])+
			parseInt(this.parent().css('border-bottom-width').split('p')[0])+
			parseInt(this.parent().css('border-top-width').split('p')[0])+
			this.parent().height()+
			this.parent().position().top
		);
		if( the_thing > parent_thing ) {
			this.parent().height(this.parent().height()+the_thing-parent_thing);
		}
	}

	$.fn.startcalendar = function() {

		if( document.location.hash.length == 6 && !isNaN(parseInt(document.location.hash.slice(1)))) {
			this_day = new Date(new Date(events[0].Start).getFullYear(), new Date(events[0].Start).getMonth(), new Date(events[0].Start).getDate());
			var left = parseInt(jQuery('#content').css('padding-left').slice(0,-2))+parseInt(jQuery('#content').css('margin-left').slice(0,-2))+jQuery('#content').position().left;
			var top = parseInt(jQuery('#content').css('padding-top').slice(0,-2))+parseInt(jQuery('#content').css('margin-top').slice(0,-2))+jQuery('#content').position().top;
			$('#pop').resizer();
			$('.close').click( function() {
				$('#pop').parent().height('').children('#pop').remove();
				document.location.hash = '';
				document.title = 'Calendar |'+document.title.split('|')[1];
			});
		}
		else if( document.location.hash.length == 7 && !isNaN(parseInt(document.location.hash.slice(1)))) this_day = new Date(document.location.hash.slice(1,5),document.location.hash.slice(5,7)-1,1);
		else this_day = new Date;

		if( typeof events[0] != 'undefined' &&  events[0].result == "error"  ) {
			$().nowloading();
			today = new Date;
			max_high_date = new Date();
			max_low_date = new Date();
			high_date = new Date(this_day.getFullYear(),this_day.getMonth()+9,7);
			low_date = new Date(this_day.getFullYear(),this_day.getMonth()-9,25);
			running = true;
			$().grabevents();
		} else {
			today = new Date;
			max_high_date = new Date(this_day.getFullYear(),this_day.getMonth()+1,7)
			max_low_date = new Date(this_day.getFullYear(),this_day.getMonth()-1,25)
			running = true;
			$().drawcalendar();

			// Second data call, first series of date information already pulled in by PHP
			high_date = new Date(this_day.getFullYear(),this_day.getMonth()+9,7);
			low_date = new Date(this_day.getFullYear(),this_day.getMonth()-9,25);
			$().grabevents();
			//if( today.getFullYear() == this_day.getFullYear() && today.getMonth() == this_day.getMonth() ) document.location.hash = '!';
			}

	}

	$.fn.nowloading = function() {
		$(this).addClass('loading');
		$('#loading').show();
	}
	$.fn.endloading = function() {
		$(this).removeClass('loading');
		$('#loading').hide();
	}

	$.fn.drawcalendar = function() {

		//if( $('#pop').length > 0 ) $('#pop').parent().height('').children('#pop').remove();
		$('div.multiday').removeClass('multiday');
		$('div.event').addClass('loading');
		if( new Date(this_day.getFullYear(), this_day.getMonth(), 8)  > max_high_date || new Date(this_day.getFullYear(), this_day.getMonth(), 24) < max_low_date ) $('table.calendar').nowloading();
		else $('table.calendar').endloading();

		var months = ["January","February","March","April","May","June","July","August","September","October","November","December"]
		$('#this-month').text(months[this_day.getMonth()]+' '+this_day.getFullYear());
		var start_day = new Date(this_day.getFullYear(), this_day.getMonth(), 1).getDay();
		var month_days = 32 - new Date(this_day.getFullYear(), this_day.getMonth(), 32).getDate();
		$('td.calendar-day').removeClass('today');

		//Occassionally due to day placement we need six weeks instead of five
		if( month_days + start_day > 35 && $('tr.calendar-week').length == 5 ) {
			$('#calendar tbody').append('<tr class="calendar-week"></tr>');
			for($x = 0; $x < 7; $x++) $('#calendar tbody tr.calendar-week:last').append('<td class="calendar-day"><div class="day-number">&nbsp;</div></td>')
			$('tr.calendar-week').addClass('six-weeks');
		} else if( month_days + start_day <= 35 && $('tr.calendar-week').length > 5 ) {
			$('tr.calendar-week').removeClass('six-weeks');
			$('#calendar tbody tr.calendar-week:last').remove();
		}



		for(x = 0; x < $('td.calendar-day div.day-number').length; x++) {
			var the_day = new Date(this_day.getFullYear(), this_day.getMonth(), 1+x- start_day);
			var days_events = events.filter( function(element) {
					var testday = new Date(element.Start.slice(0,-15));
					if( testday.getFullYear() == the_day.getFullYear() && testday.getMonth() == the_day.getMonth() && testday.getDate() == the_day.getDate() ) return true;
					else return false;
			}).sort( function(a,b) {
				return new Date(a.Start).getTime() - new Date(b.Start).getTime();
			} );

			$('td.calendar-day:eq('+x+') div.event:not(div.multiday)').remove();
			if( x < start_day ) $('td.calendar-day:eq('+x+') div.day-number').addClass('non-month').text( the_day.getDate() );
			if( x < month_days + start_day && x >= start_day ) $('td.calendar-day:eq('+x+') div.day-number').removeClass('non-month').text(the_day.getDate());
			if( x >= month_days + start_day ) $('td.calendar-day:eq('+x+') div.day-number').addClass('non-month').text( the_day.getDate() );
			if( days_events.length > 0 ) $(days_events).each( function() {
				var time = new Date(this.Start.slice(0,-15));
				if( time.getHours() + time.getMinutes() != 0 ) {
					if( time.getMinutes() != 0) {
						var min = ":"+("0"+time.getMinutes()).slice(-2);
					} else min = '';
					var time = time.getHours();
					if( time > 12 ) {
						var time = time - 12;
						var m = 'p';
					} else if( time == 12 ) var m = 'p';
					else if( time == 0 ) var time = 12;
					else var m = '';
				} else {
					var time = '';
					var m = '';
					var min = '';
				}
				$('td.calendar-day:eq('+x+')').append('<div class="event" key="'+this.key+'"><strong>'+time+min+m+'</strong> '+this.Event_Name+'</div>');
				if(this.This_Event_Costs_Money) $('td.calendar-day:eq('+x+') div.event:last').addClass('secure');
				if( new Date(this.Start.slice(0,-15)).getDate() != new Date(this.End.slice(0,-15)).getDate() && this.End.length > 0 ) for(y=0; y < new Date(this.End.slice(0,-15)).getDate() - new Date(this.Start.slice(0,-15)).getDate(); y++ ) {
					$('td.calendar-day:eq('+(x+1+y)+')').append('<div class="event multiday">'+this.Event_Name+'</div>');
				};
			});
		};
		$('div.event:not(.secure)').click( function() { $('#pop').parent().height('').children('#pop').remove(); $(this).salsapop($(this).attr('key'), $('div.event:first').html().substr($('div.event:first').html().search('</strong>')+'</strong>'.length+1)); });
		$('div.secure').click( function() { newwindow=window.open(base_url+'/p/salsa/event/common/public/?event_KEY='+$(this).attr('key'),$('div.secure').text().slice(-$('div.secure').text().length+1+$('div.secure strong').text().length),'scrollbars,resizable,height=900,width=1040');if (window.focus) {newwindow.focus()}; });
		if( today.getFullYear() == this_day.getFullYear() && today.getMonth() == this_day.getMonth() ) $('td.calendar-day:eq('+(today.getDate()+start_day-1)+')').addClass('today');
		else if(today.getFullYear() == this_day.getFullYear() && today.getMonth() == this_day.getMonth() + 1 ) $('tr.calendar-week:last td.calendar-day div.non-month:contains("'+today.getDate()+'")').parent().addClass('today');
		else if(today.getFullYear() == this_day.getFullYear() && today.getMonth() == this_day.getMonth() - 1 ) $('tr.calendar-week:first td.calendar-day div.non-month:contains("'+today.getDate()+'")').parent().addClass('today');
		$('div.event').removeClass('loading');
		$('a#month-link').attr('href','this_day.getFullYear()+("0"+(this_day.getMonth()+1)).slice(-2)');
	}

	$.fn.salsapop = function(key, title) {
		$('#main').append('<div id="pop"><img class="close" src="'+$('.close').attr('src')+'"><h3>Loading...</h3></div>');
		document.location.hash = key;
		document.title = title+' |'+document.title.split('|')[1];
		var poop = new Object();
		poop['event_compact'] = 'on';
		poop['type'] = 'event';
		poop['salsa_key'] = key;
		poop['event_url'] = document.location.href;
		poop['after_save'] = escape('<br><br><h2>Thanks for signing up</h2><br>We\'ll see you soon.<br>In the meantime, tell all your friends');
		$('.close').click( function() {$('#pop').parent().height('').children('#pop').remove(); });
		$.post(SalsaPressVars.ajaxurl,{
			action : 'myajax-salsa-pop',
			shortcode : JSON.stringify(poop),
			SalsaAjax : SalsaPressVars.SalsaAjax
		},function( response ) {
			$('#pop').html('<img class="close" src="'+$('.close').attr('src')+'"><div class="content">'+response+'</div>').resizer();
			$('.salsa-form').salsaformed();
			$('.close').click( function() {$('#pop').parent().height('').children('#pop').remove(); document.location.hash = ''; document.title = 'Calendar |'+document.title.split('|')[1];});
		});
	}

	$.fn.salsaformed = function() {

		this.submit( function(e) {
			var error = true;
			var missing = '';
			var $form = $(this);

			$form.find('label:contains(*)').removeClass('oops').each( function() {
				var $this = $(this),
					$input = $('#'+$this.attr('for'),$form)

				if( $input.length > 0 && ( $input.val().length < 1  || $input.is('[type=checkbox]:not(:checked)') || $input.is('[type=radio]:not(:checked)') ) ) {
					error = false;
					$this.addClass('oops');
					$input.addClass('oops');
					$('p.required').addClass('oops');
					missing += '<strong>'+$(this).text().split(' *')[0]+'</strong>, ';
				}
			});

			if ( error ) {
				var self = $(this);
				$(this).find('input[type=submit]').attr('disabled',true).val(objectL10n.saving_wait_one_sec);
	// Variables to fire (nonce, ajaxurl are automatic)
				$.post(
					SalsaPressVars.ajaxurl,
					{
						action : 'myajax-submit',
						request : $(this).serialize(),
						SalsaAjax : SalsaPressVars.SalsaAjax,
						object : 'supporter',
						doing : 'save'
					},
	// Function to execute upon response, passing the variable as 'response'
					function( response ) {
						data = response[0];
						if( data.result == 'success' ) {
							self.notice(objectL10n.success,'success');

							if( typeof self.attr('redirect_path') != 'undefined' && self.attr('redirect_path').length > 0 ) document.location = self.attr('redirect_path');
							
							var after_save_html = self.nextAll('.after_save:first').html() || ''

							if( after_save_html.length > 0 ) {
								self.nextAll('.after_save').show();
								self.remove();
							} else {
								// From http://www.learningjquery.com/2007/08/clearing-form-data
								self.find(':input', '.salsa-form').each(function() {
									var type = this.type;
									var tag = this.tagName.toLowerCase();
									if (type == 'text' || type == 'password' || tag == 'textarea') this.value = "";
									else if (type == 'checkbox' || type == 'radio') this.checked = false;
									else if (tag == 'select') this.selectedIndex = 0;
								  });
								self.find('input[type=submit]').attr('disabled',false).val(objectL10n.click_to_go_again);
							}
						}
						else if ( (typeof data.messages == 'undefined' ? '' : data.messages.toString() ).match(/Please enter a valid email address/).length > 0 ) {
							self.find('label.required').addClass('oops');
							self.find('input[name=Email]').parent().find('label').addClass('oops');
							self.find('input[type=submit]').attr('disabled',false).val(objectL10n.click_to_try_again);
							self.notice( objectL10n.please_enter_valid_email_address )
						}
						else if ( data.messages.length > 0 ) self.notice(response.messages,'bad');
						else {
							self.notice(objectL10n.try_again,'bad');
							self.find('input[type=submit]').attr('disabled',false).val(objectL10n.click_to_try_again,'bad');
						}
					});
	// This could be more or less automatic, don't see why anything else would be needed here
			} else {
				$(this).find('label.required').addClass('oops');
				$(this).next('span.notice').remove();
				$(this).notice(objectL10n.seem_to_be_missing+' '+missing.slice(0,missing.length-2),'bad');
			}
			e.preventDefault();
		});
	}

})(jQuery);


jQuery(document).ready( function($) {


	$('.salsa-form').salsaformed();

});
