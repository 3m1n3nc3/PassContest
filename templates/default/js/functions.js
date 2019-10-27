$(document).ready(function() { 
	restrictedTab('basic-tab1', 'l', 1);  	
	restrictedTab('photo-tab1', 'l', 1); 

	// Load all notifications
	loadNotifications(0); 
	loadNotifications(0, 1);

	// Set the notifications popover 
	// $("#notifications_popover").popover({
	//   'title' : 'Notifications', 
	//   'html' : true,
	//   'placement' : 'bottom',
	//   'content' : $(".notification_list").html()
	// });		

	$(document).on('keydown', 'textarea#write_msg', function(e) {
		if(e.keyCode==13) {
			// Store the message into var
			var message = $('textarea#write_msg').val();
			var id = $('#message-receiver').attr('value');
			if(message) {
				// Remove chat errors if any
				$('.chat-error').remove();
				
				// Show the progress animation
				$('#loader').html('<div id="remove" class="progress md-progress young-passion-gradient"> <div class="indeterminate"></div> </div>');
				
				// Reset the chat input area			
				$('textarea#write_msg').val('');
				 
				$.ajax({
					type: "POST",
					url: siteUrl+"/connection/send_message.php",
					data: 'message='+encodeURIComponent(message)+'&id='+id,
					cache: false,
					success: function(html) {
						// Check if in the mean time any message was sent
						checkNewMessages();
						
						// Append the new chat to the div chat container
						$('.msg_history').append(html);
						$('#loader').hide(); 
						
						// Scroll at the bottom of the div (focus new content)
						$(".msg_history").scrollTop($(".msg_history")[0].scrollHeight);
					}
				});
			}
		}
	});	

	$(document).on('keyup', "#chat-search", searchMessages);

}); 
function fetch_state() { 
	var country = document.getElementById("country");
	var country_id = country.options[country.selectedIndex].id; 
	$.ajax({
		type: 'POST',
		url: siteUrl+'/connection/location.php',
		data: {country_id:country_id, type:1},  
		success: function(html) { 
			$('#state').html(html); 
			$('#state').attr('onchange', 'fetch_city()'); 
		}		
	})
}

function fetch_city() { 
	var state = document.getElementById("state");
	var state_id = state.options[state.selectedIndex].id; 
	$.ajax({
		type: 'POST',
		url: siteUrl+'/connection/location.php',
		data: {state_id:state_id, type:2},  
		success: function(html) {
			$('#city').html(html);  console.log(html);
		}	
	})
}

function loadMessages(user_id, username, chat_id, start) {
	if(!chat_id) {
		$('#top-header').show();
	} else {
		$('#loader').html('<div id="remove" class="progress md-progress young-passion-gradient"> <div class="indeterminate"></div> </div>');
	}
	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/load_message.php",
		data: "user_id="+user_id+"&chat_id="+chat_id+"&start="+start, 
		cache: false,
		success: function(html) {
			// Remove the loader animation
			if(!chat_id) {
				$('.msg_history').empty();
				$('#top-header').hide();
			} else {
				$('.more-messages').remove();
			} 
			
			// Append the new message
			$('.msg_history').prepend(html);
			$('#loader').hide();
		
			if(username) { 
				$(".msg_history").scrollTop($(".msg_history")[0].scrollHeight);
			} 
		}
	});
}

function checkNewMessages(){
	var user_id = $('#message-receiver').attr('value');
	// Check whether user_id is defined or not (avoid making requests when out of the chat page)
	if(user_id) {
		$.ajax({
			type: "POST",
			url: siteUrl+"/connection/load_message.php",
			data: "user_id="+user_id+"&type=1",
			success: function(html) {
				if(html) {
					$('.msg_history').append(html);
					 
					$(".msg_history").scrollTop($(".msg_history")[0].scrollHeight);
				}
		   }
		});
	}
}

function searchMessages() {
	var q = $('#chat-search').val();
	$('.inbox_chat').empty();
	
	// If the text input is 0, remove everything instantly by setting the MS to 1
	
	$('#search-loader').html('<div id="remove" class="progress md-progress young-passion-gradient"> <div class="indeterminate"></div> </div>'); 
	var ms = 200;
	
	setTimeout(function() {
		if(q == $('#chat-search').val()) {
			
			$.ajax({
				type: "POST",
				url: siteUrl+"/connection/load_message.php",
				data: 'q='+q+'&search=1&list=1',  
				cache: false,
				success: function(html) {
					$('#remove').hide();
					$('.inbox_chat').html(html);
				}
			});
		}
	}, ms);
}

function blockAction(id, type, style) {
	// type 0: View status
	// type 1: Block
	$('#block_').html(loaderSM); 
	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/block.php",
		data: "id="+id+"&type="+type+"&style="+style, 
		cache: false,
		success: function(html) {
			$('#block_').html(html);
		}
	});
}

function modal_destroyer(modal_name) { 
	// $('#'+modal_name).modal('dispose');
	$('#'+modal_name).modal('toggle');
}

function restrictedTab(tab_id, lr, type) {
	// lr l: left
	// lr r: right
	// Type 1: add attribute
	// Type 0: Remove attribute

	if (lr == 'l') {
		var lr = 'left';
	} else {
		var lr = 'right';
	}
	if (type == 1) {
		$('#'+tab_id).attr('data-placement', lr);
		$('#'+tab_id).attr('data-toggle', 'popover');
		$('#'+tab_id).attr('title', 'Restricted');
		$('#'+tab_id).attr('data-content', 'Sorry, but you can not skip a step, if you are done click \'save\'');		
	} else {
		$('#'+tab_id).popover('dispose');
		$('#'+tab_id).removeAttr('data-placement');
		$('#'+tab_id).removeAttr('data-toggle');
		$('#'+tab_id).removeAttr('title');
		$('#'+tab_id).removeAttr('data-original-title');
		$('#'+tab_id).removeAttr('data-content');
		$('#'+tab_id).attr('data-toggle', 'tab'); 		
	}
} 

(function() {
'use strict';
window.addEventListener('load', function() {
  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  var forms = document.getElementsByClassName('needs-validation');
  // Loop over them and prevent submission
  var validation = Array.prototype.filter.call(forms, function(form) {
    form.addEventListener('submit', function(event) {
      if (form.checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
}, false);
})(); 

function changeAlias(id) {
    var title = $.trim($("#page_title"+id).val());
    title = title.replace(/[^a-zA-Z0-9-]+/g, '-');
    $("#page_alias"+id).val(title.toLowerCase());
}

function submit_form(id) {
	document.getElementById(id).submit();
}

function show_connect_modal(){
	$('#connectModal').modal('show');
}

function safeInterval(func, wait, times){
    var interv = function(w, t){
        return function(){
            if(typeof t === "undefined" || t-- > 0){
                setTimeout(interv, w);
                try{
                    func.call(null);
                }
                catch(e){
                    t = 0;
                    throw e.toString();
                }
            }
        };
    }(wait, times);

    setTimeout(interv, wait);
};


safeInterval(function(){
	// Refresh the notifications
   loadNotifications(0);
   loadNotifications(0, 1) 
	// Refresh the chat 
   checkNewMessages();
}, 5000, 20);
  

function reloadSave(type) {

	$('.saving-load').show(); 

	location.reload();
}

function connector(type, ref) {
	// Type 0: Login
	// Type 1: Signup
	
	
	if(type == 0) {
		$('#loader').html('<div id="remove" class="progress md-progress young-passion-gradient"> <div class="indeterminate"></div> </div>'); 
		$('#login-btn').removeAttr('onclick');
		
		var username = $('input[name="username"]').val();  
		var password = $('input[name="password"]').val();
		
		$.ajax({
			type: "POST",
			url: siteUrl+"/connection/connect.php",
			data: "username="+username+"&password="+password+"&referrer="+ref+"&login=true",
			dataType:"json",
			cache: false,
			success: function(html) {
				$('#login-btn').attr('onclick', 'connector('+type+', '+ref+')'); 
 
				$('#remove').remove();
				$('#login-message').html(html.message); 
				if (html.header != 0) { 
					window.top.location=html.header;
				}
			},
		    error: function(xhr, status, error){
		        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
		        $('#login-message').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        	);
		    }	
		});
	} else { 
		$('#loader-2').html('<div id="remove" class="progress md-progress young-passion-gradient"> <div class="indeterminate"></div> </div>'); 
		$('#create2').removeAttr('onclick'); 
		$('#signup-btn').removeAttr('onclick');
		
		var username = $('input[id="username2"]').val();  
		var email = $('input[id="email2"]').val(); 
		var password = $('input[id="password2"]').val(); 
		var recaptcha = $('input[id="recaptcha2"]').val(); 
		var invite_code = $('input[id="invite_code"]').val(); 
		var phone = encodeURIComponent($('input[id="phone"]').val()); 

		$('#signup').addClass('active').siblings().removeClass('active');
		$('#signup-tab').addClass('show active').siblings().removeClass('show active');
		  
		$.ajax({
			type: "POST",
			url: siteUrl+"/connection/connect.php",
			data: {username:username, password:password, email:email, recaptcha:recaptcha, invite_code:invite_code, phone:phone, referrer:ref, signup:true},
			dataType:"json",
			cache: false,
			success: function(html) {
				$('#signup-btn').attr('onclick', 'connector('+type+', '+ref+')'); 
 
				$('#remove').remove();
				$('#recaptcha-img').html('<img src="'+captcha_url+'&gocache='+(+new Date)+'">');
				$('#signup-message').html(html.message);   
				if (html.header != 0) { 
					window.top.location=html.header;
				}
			},
		    error: function(xhr, status, error){
		        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
		        $('#signup-message').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );
		    }	
		});
	}
}

function saveprofile(type) {
	// Type 0: Personal Info
	// Type 1: Other Info
	
	$('.saving-load').show();
	
	if(type == 1) {
		$('#save1').removeAttr('onclick');
		
		var firstname = $('input[name="firstname"]').val(); 
		var lastname = $('input[name="lastname"]').val(); 
		var gender = $('select[name="gender"] option:selected').val();
		var city = $('select[name="city"] option:selected').val();
		var country = $('select[name="country"] option:selected').val();
		var state = $('select[name="state"] option:selected').val();
		var phone = encodeURIComponent($('input[name="phone"]').val());
		var address = $('input[name="address"]').val();

		if (state) {var state = state;} else {state = '';}
		if (city) {var city = city;} else {city = '';}
		
		$.ajax({
			type: "POST",
			url: siteUrl+"/connection/update.php",
			data: "firstname="+firstname+"&lastname="+lastname+"&gender="+gender+"&city="+city+"&state="+state+"&country="+country+"&phone="+phone+"&address="+address+"&save=1", 
			cache: false,
			success: function(html) {
				if(html == 1) {
					location.reload();
				} else {
					$('.saving-load').hide();
					$('#return-message').html(html);
					$('#save1').attr('onclick', 'saveprofile(1)');
				}
			},
		    error: function(xhr, status, error){
		        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
		        $('#return-message').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );
		    }
		});
	} else {
		$('#save2').removeAttr('onclick');
		var profession = $('input[name="profession"]').val();
		var facebook = $('input[name="facebook"]').val();
		var twitter = $('input[name="twitter"]').val();
		var instagram = $('input[name="instagram"]').val();
		var lovesto = $('input[name="lovesto"]').val();
		var intro = $('textarea[name="intro"]').val(); 

		$.ajax({
			type: "POST",
			url: siteUrl+"/connection/update.php",
			data: "profession="+profession+"&intro="+intro+"&facebook="+facebook+"&twitter="+twitter+"&instagram="+instagram+"&lovesto="+lovesto+"&save=2", 
			cache: false,
			success: function(html) {
				if(html == 1) {
					location.reload();
				} else {
					$('.saving-load').hide();
					$('#return-message2').html(html);
					$('#save2').attr('onclick', 'saveprofile(2)');
				}
			},
		    error: function(xhr, status, error){
		        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
		        $('#return-message2').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );
		    }
		});
	}
}

function save_bank(type) {
	// Type 0:  
	// Type 1:  
	
	$('.saving-load').show();
	 
	$('#savebank').removeAttr('onclick');
	
	var paypal = $('input[name="paypal"]').val(); 
	var bank = $('input[name="bank"]').val();
	var bank_address = $('input[name="bank_address"]').val();
	var sort = $('input[name="sort"]').val(); 
	var account_name = $('input[name="account_name"]').val();
	var account_number = $('input[name="account_number"]').val();
	var routing = $('input[name="routing"]').val();

	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/update.php",
		data: "paypal="+paypal+"&bank="+bank+"&bank_address="+bank_address+"&sort="+sort+"&account_name="+account_name+"&account_number="+account_number+"&routing="+routing+"&save=3", 
		cache: false,
		success: function(html) { 
			if(html == 1) {
				location.reload();
			} else {
				$('.saving-load').hide();
				$('#return-messageX').html(html);
				$('#savebank').attr('onclick', 'save_bank()');
			}
		},
	    error: function(xhr, status, error){
	        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
	        $('#return-messageX').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );
	    }
	}); 
}

function createContestant(type, user_id) {
	// Type 1: Personal Info
	// Type 2: Other Info
	
	$('.saving-load').show();
	if (user_id == 0) {
		var user_id = 0;
		var contestant_id = $('input[name="get_user_id"]').val();
	} else {
		var user_id = user_id;
		var contestant_id = user_id;
	}
	
	if(type == 1) {
		$('#create1').removeAttr('onclick');
		
		var firstname = $('input[name="firstname"]').val(); 
		var lastname = $('input[name="lastname"]').val();
		var city = $('input[name="city"]').val();
		var state = $('input[name="state"]').val();
		var country = $('select[name="country"] option:selected').val();
		var phone = encodeURIComponent($('input[name="phone"]').val());
		var email = $('input[name="email"]').val();
		var contest_id = $('input[name="c_id"]').val(); 
		restrictedTab('basic-tab1', 'l', 0);
		$.ajax({
			type: "POST",
			url: siteUrl+"/connection/create_contestant.php",
			data: "firstname="+firstname+"&lastname="+lastname+"&city="+city+"&state="+state+"&country="+country+"&phone="+phone+"&email="+email+"&contest_id="+contest_id+"&user_id="+user_id+"&create=1", 
			cache: false,
			success: function(html) { 
				if(html == 1) {
					location.reload();
				} else {
					$('.saving-load').hide();
					$('.return-message').html(html); 
					$('#create1').attr('onclick', 'createContestant(1, '+user_id+')'); 

				}
			},
		    error: function(xhr, status, error){
		        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
		        $('.return-message').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );
		    }
		});
	} else {
		$('#create2').removeAttr('onclick');
		var profession = $('input[name="profession"]').val();
		var facebook = $('input[name="facebook"]').val();
		var twitter = $('input[name="twitter"]').val();
		var instagram = $('input[name="instagram"]').val();
		var lovesto = $('input[name="lovesto"]').val();
		var intro = $('textarea[name="intro"]').val(); 
		var contest_id = $('input[name="c_id"]').val();
		restrictedTab('basic-tab1', 'l', 0);
		restrictedTab('photo-tab1', 'l', 0);

		$.ajax({
			type: "POST",
			url: siteUrl+"/connection/create_contestant.php",
			data: "profession="+profession+"&intro="+intro+"&facebook="+facebook+"&twitter="+twitter+"&instagram="+instagram+"&lovesto="+lovesto+"&contest_id="+contest_id+"&contestant_id="+contestant_id+"&create=2", 
			cache: false,
			success: function(html) {
				if(html == 1) {
					location.reload();
				} else { 
					$('.return-message2').html(html);
					$('#create2').attr('onclick', 'createContestant(2, '+user_id+')'); 
					$('#loader').html('<div class="progress md-progress peach-gradient"> <div class="indeterminate"></div> </div>');
					window.top.location=siteUrl+'/index.php?a=enter&create='+contest_id+'&photo='+contestant_id;
					$('#photo-tab1').attr('href', siteUrl+'/index.php?a=enter&create='+contest_id+'&photo='+contestant_id); 
				}
			},
		    error: function(xhr, status, error){
		        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
		        $('.return-message2').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );
		    }
		});
	}
}

function addContest(type) {
	
	$('.saving-load').show();
	$('#save1').hide();
		
		var title = $('input[name="title"]').val(); 
		var type = $('select[name="type"] option:selected').val();
		var slogan = $('input[name="slogan"]').val();
		var facebook = $('input[name="facebook"]').val();
		var twitter = $('input[name="twitter"]').val();
		var instagram = $('input[name="instagram"]').val();
		var email = $('input[name="email"]').val();	
		var phone = encodeURIComponent($('input[name="phone"]').val());	  	

		$.ajax({
			type: "POST",
			url: siteUrl+"/connection/add_contest.php",
			data: "title="+title+"&type="+type+"&slogan="+slogan+"&facebook="+facebook+"&twitter="+twitter+"&instagram="+instagram+"&email="+email+"&phone="+phone+"&save=1", 
			cache: false,
			success: function(html) { 
				if(html == 1) {
					location.reload();
				} else {
					$('.saving-load').hide();
					$('#return-message').html(html);  
					$('#returnto').show(); 
				}
			},
		    error: function(xhr, status, error){
		        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
		        $('#return-message').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );
		    }
		});
}

function updateContest(type) {
	// Type 0: Basic Info
	// Type 1: Other Info
	
	$('.saving-load').show();
	
	if(type == 1) {
		$('#save1').removeAttr('onclick');
		
		var title = $('input[name="title"]').val(); 
		var type = $('select[name="type"] option:selected').val();
		var slogan = $('input[name="slogan"]').val();
		var facebook = $('input[name="facebook"]').val();
		var twitter = $('input[name="twitter"]').val();
		var instagram = $('input[name="instagram"]').val();
		var email = $('input[name="email"]').val();	
		var phone = encodeURIComponent($('input[name="phone"]').val());	 
		var id = $('input[name="id"]').val();	

		$.ajax({
			type: "POST",
			url: siteUrl+"/connection/add_contest.php",
			data: "title="+title+"&type="+type+"&slogan="+slogan+"&facebook="+facebook+"&twitter="+twitter+"&instagram="+instagram+"&email="+email+"&phone="+phone+"&id="+id+"&save=1", 
			cache: false,
			success: function(html) { 
				if(html == 1) {
					location.reload();
				} else {
					$('.saving-load').hide();
					$('#return-message').html(html);
					$('#return-message123').html(html);
					$('#save1').attr('onclick', 'updateContest(1)');
					$('#returnto').show(); 
				}
			},
		    error: function(xhr, status, error){
		        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
		        $('#return-message').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );
		    }
		});
	} else {
		$('#save2').removeAttr('onclick');
		var country = $('select[name="country"] option:selected').val();
		var intro = $('textarea[name="intro"]').val();
		var prize = $('textarea[name="prize"]').val();
		var eligibilty = $('textarea[name="eligibility"]').val();
		var address = $('textarea[name="address"]').val(); 
		var id = $('input[name="id"]').val();	

		$.ajax({
			type: "POST",
			url: siteUrl+"/connection/add_contest.php",
			data: "country="+country+"&intro="+intro+"&eligibility="+eligibilty+"&prize="+prize+"&address="+address+"&id="+id+"&save=2", 
			cache: false,
			success: function(html) {
				if(html == 1) {
					location.reload();
				} else {
					$('.saving-load').hide();
					$('#return-message2').html(html);
					$('#save2').attr('onclick', 'updateContest(2)');
				}
			},
		    error: function(xhr, status, error){
		        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
		        $('#return-message2').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );
		    }
		});
	}
}

function activateContest(type) {
	 
	if(type == 1) {		
		$('#status').removeAttr('onchange');
		var id = $('input[name="id"]').val();
		var active = 'active=1';
		var status = $('input[name="status"]').val(function(){
		    if(this.checked == true) {
		        s = '1'; }
		    else {
		        s = '0'; } 
		}); 
	} else if(type == 2) {
		$('#voting').removeAttr('onchange');
		var id = $('input[name="vid"]').val();
		var active = 'allow_vote=1';
		var status = $('input[name="voting"]').val(function(){

		    if(this.checked == true) {
		        s = '1'; }
		    else {
		        s = '0'; } 
		}); 			
	}  else {
		$('#social').removeAttr('onchange');
		var id = $('input[name="sid"]').val();
		var active = 'social_require=1';
		var status = $('input[name="social"]').val(function(){

		    if(this.checked == true) {
		        s = '1'; }
		    else {
		        s = '0'; } 
		}); 			
	}  	

	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/manage.php",
		data: "status="+status+"&s="+s+"&id="+id+"&"+active, 
		cache: false,
		success: function(html) { 
			if(html == 1) {
				location.reload();
			} else {  
				if (type == 1) {
					$('#status').attr('onclick', 'activateContest(1)'); 
					$('#s_msg').html(html);
				} else if (type == 2) {
					$('#voting').attr('onclick', 'activateContest(2)');
					$('#v_msg').html(html);
				} else {
					$('#social').attr('onclick', 'activateContest(3)');
					$('#soc_msg').html(html);
				}			
			}
		},
	    error: function(xhr, status, error){
	        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
	        $('#s_msg, #v_msg, #soc_msg').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );
	    }
	});
}

function settingSwitch(type) {
	// Type 1: Email Notifications
	// Type 2: Site wide notifications
	// Type 3: Premium site
	// Type 4: Facebook Login
	// Type 5: permalinks
	 
	if(type == 1) {		
		$('#email_notifications').removeAttr('onchange');
		var id = $('input[name="id"]').val(); 
		var active = '&type=1&action=true';
		var action = 'update';
		var status = $('input[name="email_notifications"]').val(function(){

		    if(this.checked == true) {
		        s = '1';
		    }
		    else {
		        s = '0';        
		    }
		});
	} else if(type == 2) {
		$('#site_notifications').removeAttr('onchange');
		var id = $('input[name="id"]').val();  
		var active = '&type=2&action=true';
		var action = 'update';
		var status = $('input[name="site_notifications"]').val(function(){

		    if(this.checked == true) {
		        s = '1';
		    }
		    else {
		        s = '0';        
		    } 
		}); 			
	} else if(type == 3) {
		$('#premium_site').removeAttr('onchange');
		var id = null;  
		var active = '&type=1&action=true';
		var action = 'settings';
		var status = $('input[name="premium_site"]').val(function(){

		    if(this.checked == true) {
		        s = '1';
		    }
		    else {
		        s = '0';        
		    } 
		}); 			
	} else if(type == 4) {
		$('#fb_access').removeAttr('onchange');
		var id = null;  
		var active = '&type=2&action=true';
		var action = 'settings';
		var status = $('input[name="fb_access"]').val(function(){

		    if(this.checked == true) {
		        s = '1';
		    }
		    else {
		        s = '0';        
		    } 
		}); 			
	} else if(type == 5) {
		$('#permalinks').removeAttr('onchange');
		var id = null;  
		var active = '&type=3&action=true';
		var action = 'settings';
		var status = $('input[name="permalinks"]').val(function(){

		    if(this.checked == true) {
		        s = '1';
		    }
		    else {
		        s = '0';        
		    } 
		}); 			
	}  	

	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/"+action+".php",
		data: "status="+status+"&s="+s+"&id="+id+active, 
		cache: false,
		success: function(html) { 
			if(html == 10) {
				location.reload();
			} else {  
				if (type == 1) {
					$('#email_notifications').attr('onclick', 'settingSwitch(1)'); 
					$('#s_msg').html(html);
				} else if (type == 2) {
					$('#site_notifications').attr('onclick', 'settingSwitch(2)');
					$('#v_msg').html(html);
				} else if (type == 3) {
					$('#premium_site').attr('onclick', 'settingSwitch(3)');
					$('#s_msg').html(html);
				} else if (type == 4) {
					$('#fb_access').attr('onclick', 'settingSwitch(4)');
					$('#v_msg').html(html);
				} else if (type == 5) {
					$('#permalinks').attr('onclick', 'settingSwitch(5)');
					$('#p_msg').html(html);
				}
			}
		},
	    error: function(xhr, status, error){
	        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
	        $('#s_msg, #v_msg, #p_msg').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );  
	    }
	});
}
 
 function siteSettings(type) {
	// Type 0: Settings
	// Type 1: Category
	
	$('#msg'+type).html('<div class="progress md-progress peach-gradient"> <div class="indeterminate"></div> </div>');
	
	if(type == 0) {
		$('#save').removeAttr('onclick'); 
		var site_mode = $('select[name="site_mode"] option:selected').val();
		var activation = $('select[name="activation"] option:selected').val(); 
		var sidebar = $('select[name="sidebar"] option:selected').val(); 
		var direction = $('select[name="direction"] option:selected').val(); 
		var recommend = $('select[name="recommend"] option:selected').val(); 
		var tracking = $('textarea[name="tracking"]').val(); 
		var sitename = $('input[name="sitename"]').val();  
		var sitephone = $('input[name="sitephone"]').val();  
		var form_data = 'site_mode='+site_mode+'&activation='+activation+'&sidebar='+sidebar+'&direction='+direction+'&tracking='+tracking+'&sitename='+sitename+'&sitephone='+sitephone+'&recommend='+recommend+'&save=1'; 
	} else if(type == 1){
		$('#save1').removeAttr('onclick');
		var explore = $('input[name="perpage_explore"]').val(); 
		var featured = $('input[name="perpage_featured"]').val();
		var notifications = $('input[name="perpage_notifications"]').val();
		var messenger = $('input[name="perpage_messenger"]').val();
		var notifications_drop = $('input[name="perpage_notifications_drop"]').val();
		var table = $('input[name="perpage_table"]').val();
		var contest = $('input[name="perpage_contest"]').val();
		var voting = $('input[name="perpage_voting"]').val(); 
		var form_data = 'explore='+explore+'&featured='+featured+'&notifications='+notifications+'&messenger='+messenger+'&notifications_drop='+notifications_drop+'&table='+table+'&voting='+voting+'&contest='+contest+'&save=2'; 
	} else if(type == 2){
		$('#save2').removeAttr('onclick');
		var captcha = $('select[name="captcha"] option:selected').val(); 
		var invite = $('select[name="invite"] option:selected').val(); 
		var fb_appid = $('input[name="fb_appid"]').val();
		var fb_secret = $('input[name="fb_secret"]').val(); 
		var form_data = 'captcha='+captcha+'&invite='+invite+'&fb_appid='+fb_appid+'&fb_secret='+fb_secret+'&save=3'; 
	} else if(type == 3){
		$('#save3').removeAttr('onclick');
		var twilio_phone = $('input[name="twilio_phone"]').val();
		var twilio_sid = $('input[name="twilio_sid"]').val();
		var twilio_token = $('input[name="twilio_token"]').val();

		var email_apply = $('select[name="email_apply"] option:selected').val();
		var email_approved = $('select[name="email_approved"] option:selected').val();
		var email_social = $('select[name="email_social"] option:selected').val();
		var email_vote = $('select[name="email_vote"] option:selected').val();
		var email_comment = $('select[name="email_comment"] option:selected').val();
		var email_welcome = $('select[name="email_welcome"] option:selected').val();

		var send_sms = $('select[name="send_sms"] option:selected').val();
		var premium_sms = $('select[name="premium_sms"] option:selected').val();
		var smtp = $('select[name="smtp"] option:selected').val();
		var smtp_secure = $('select[name="smtp_secure"] option:selected').val(); 
		var smtp_auth = $('select[name="smtp_auth"] option:selected').val(); 
		var smtp_port = $('input[name="smtp_port"]').val();
		var smtp_server = $('input[name="smtp_server"]').val(); 
		var smtp_username = $('input[name="smtp_username"]').val(); 
		var smtp_password = $('input[name="smtp_password"]').val();   
		var form_data = 'send_sms='+send_sms+'&premium_sms='+premium_sms+'&twilio_phone='+twilio_phone+'&twilio_sid='+twilio_sid+'&twilio_token='+twilio_token+'&email_apply='+email_apply+'&email_approved='+email_approved+'&email_social='+email_social+'&email_vote='+email_vote+"&email_comment="+email_comment+"&email_welcome="+email_welcome+"&smtp="+smtp+"&smtp_secure="+smtp_secure+"&smtp_auth="+smtp_auth+"&smtp_port="+smtp_port+"&smtp_server="+smtp_server+"&smtp_username="+smtp_username+"&smtp_password="+smtp_password+'&save=4'; 
	} else if(type == 4){
		$('#save4').removeAttr('onclick'); 
		var approved = $('textarea[name="approved_temp"]').val();
		var declined = $('textarea[name="declined_temp"]').val();
		var comment = $('textarea[name="comment_temp"]').val();
		var reply = $('textarea[name="reply_temp"]').val();
		var vote = $('textarea[name="vote_temp"]').val(); 
		var apply = $('textarea[name="apply_temp"]').val(); 
		var register = $('textarea[name="reg_temp"]').val(); 
		var recover = $('textarea[name="recover_temp"]').val(); 
		var form_data = 'approved='+approved+'&declined='+declined+'&reply='+reply+'&comment='+comment+'&vote='+vote+'&apply='+apply+'&register='+register+'&recover='+recover+'&save=5'; 
	} else if(type == 5){
		$('#save4').removeAttr('onclick'); 
		var ads_status = $('select[name="ads_status"] option:selected').val();
		var unit_1 = $('textarea[name="unit_1"]').val(); 
		var unit_2 = $('textarea[name="unit_2"]').val(); 
		var unit_3 = $('textarea[name="unit_3"]').val(); 
		var unit_4 = $('textarea[name="unit_4"]').val(); 
		var unit_5 = $('textarea[name="unit_5"]').val(); 
		var unit_6 = $('textarea[name="unit_6"]').val(); 
		var form_data = 'unit_1='+unit_1+'&unit_2='+unit_2+'&unit_3='+unit_3+'&unit_4='+unit_4+'&unit_5='+unit_5+'&unit_6='+unit_6+'&status='+ads_status+'&save=6'; 
	}

	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/settings.php",
		data: form_data, 
		cache: false,
		success: function(html) { 
			if(html == 1) {
				location.reload();
			} else {  
				$('#msg'+type).html(html); 
				if (type == 0) {
					$('#save').attr('onclick', 'siteSettings('+type+')');
				} else if (type == 1) {
					$('#save1').attr('onclick', 'siteSettings('+type+')');
				} else if (type == 2) {
					$('#save2').attr('onclick', 'siteSettings('+type+')');
				} else if (type == 3) {
					$('#save3').attr('onclick', 'siteSettings('+type+')');
				} else if (type == 4) {
					$('#save4').attr('onclick', 'siteSettings('+type+')');
				}  else if (type == 5) {  
					$('#save5').attr('onclick', 'siteSettings('+type+')');
				} 
			}
		},
	    error: function(xhr, status, error){
	        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
	        $('#msg'+type).html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );  
	    }
	});	
}
 
 function addSomething(type) {
	// Type 0: Schedule
	// Type 1: Category
	
	$('.saving-load').show();
	
	if(type == 0) {
		$('#addactivity').removeAttr('onclick');
		 
		var date = $('input[name="date"]').val();
		var time = $('input[name="time"]').val();
		var activity = $('input[name="activity"]').val(); 
		var description = $('textarea[name="description"]').val(); 
		var id = $('input[name="id"]').val(); 	

		$.ajax({
			type: "POST",
			url: siteUrl+"/connection/manage.php",
			data: "date="+date+"&time="+time+"&activity="+activity+"&description="+description+"&id="+id+"&addactivity=1", 
			cache: false,
			success: function(html) { 
				if(html == 1) {
					location.reload();
				} else {
					$('.saving-load').hide();
					$('#add-message').html(html); 
					$('#addactivity').attr('onclick', 'addSomething(0)'); 
				}
			},
		    error: function(xhr, status, error){
		        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
		        $('#add-message').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );  
		    }
		});
	} else {
		$('#addcategory_btn').removeAttr('onclick');
		 
		var category = $('input[name="category"]').val();
		var requirement = $('textarea[name="requirement"]').val(); 
		var description = $('textarea[name="cdescription"]').val(); 
		var id = $('input[name="id"]').val(); 	

		$.ajax({
			type: "POST",
			url: siteUrl+"/connection/manage.php",
			data: "category="+category+"&requirement="+requirement+"&description="+description+"&id="+id+"&addcategory=1", 
			cache: false,
			success: function(html) { 
				if(html == 1) {
					location.reload();
				} else {
					$('.saving-load').hide();
					$('#add-message22').html(html); 
					$('#addcategory_btn').attr('onclick', 'addSomething(1)'); 
				}
			},
		    error: function(xhr, status, error){
		        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
		        $('#add-message22').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );  
		    }
		});
	}
}
 
function delete_the(id, type, contest='', master='') {
	// Type 0: Schedule
	// Type 1: Category
	// Type 2: Created Profiles
	// Type 3: Notifications
	// Type 4: Decline contest application
	// Type 5: Admin delete support ticket
	// Type 7: Gallery Photo
	// Type 9: Comment
	// Type 10: Message

	if(type == 0) {
		$('#schedule_'+id).html('<div class="progress md-progress peach-gradient"> <div class="indeterminate"></div> </div>');
	} else if(type == 1) {
		$('#category_'+id).html('<div class="progress md-progress peach-gradient"> <div class="indeterminate"></div> </div>');
	} else if(type == 2) {
		$('#user_'+id).html('<div class="progress md-progress peach-gradient"> <div class="indeterminate"></div> </div>');
	} else if(type == 3) {
		$('#notification_'+id).html('<div class="progress md-progress peach-gradient"> <div class="indeterminate"></div> </div>');
	} else if(type == 4 || type == 5) {
		$('#name'+id).html('<div class="progress md-progress peach-gradient"> <div class="indeterminate"></div> </div>');
	} else if(type == 6) {
		$('#ticket_'+id).html('<div class="progress md-progress peach-gradient"> <div class="indeterminate"></div> </div>');
	} else if(type == 7 || type == 8) {
		$('#photo_'+id).html('<div class="progress md-progress peach-gradient"> <div class="indeterminate"></div> </div>');
	}
	
	if (contest) {
		var get_contest = '&contest_id='+contest;
	} else {
		var get_contest = '';
	}

	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/delete.php",
		data: "id="+id+"&type="+type+'&master='+master+get_contest,
		cache: false,
		success: function(html) {
			if(type == 0) {
				$('#sche-message').html(html);
				$('#schedule_'+id).fadeOut(500, function() { $('#schedule_'+id).remove(); });
			} else if(type == 1) {
				$('#cate-message').html(html);
				$('#category_'+id).fadeOut(500, function() { $('#category_'+id).remove(); });
			} else if(type == 2) {
				$('#set-message').html(html);
				$('#user_'+id).fadeOut(500, function() { $('#user_'+id).remove(); });
			} else if(type == 3) {
				$('#set-message_'+id).html(html);
				$('#notification_'+id).fadeOut(500, function() { $('#notification_'+id).remove(); }); 
			} else if(type == 4 || type == 5) {
				$('#notice').html(html);
				$('#row'+id).fadeOut(500, function() { $('#row'+id).remove(); }); 
			} else if(type == 6) {
				$('#set-message_'+id).html(html);
				$('#ticket_'+id).fadeOut(500, function() { $('#ticket_'+id).remove(); });
			} else if(type == 7 || type == 8) {
				$('#set-message_'+id).html(html);
				$('#photo_'+id).fadeOut(500, function() { $('#photo_'+id).remove(); });
			} else if(type == 9) { 
				// $('#new-comment').html(html); 
				$('#comment_'+id).fadeOut(500, function() { $('#comment_'+id).remove(); });
				//$('#notice_'+id).html(html);
			} else if(type == 10) {  
				$('#message_'+id).fadeOut(500, function() { $('#message_'+id).remove(); });
			} 
		}
	});
}

function approveApplication(user_id, contest_id) { 
	
	$('#saving-load'+user_id).show();

	$('#approve'+user_id).removeAttr('onclick');	

	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/approve_application.php",
		data: "user_id="+user_id+"&contest_id="+contest_id+"&approve=1", 
		cache: false,
		success: function(html) { 
			if(html == 1) {
				location.reload();
			} else {
				$('#saving-load'+user_id).hide();
				$('#notice').html(html);  
				$('#row'+user_id).fadeOut(500, function() { $('#row'+user_id).remove(); }); 
			}
		}
	});
}

function enterSerial(type, contest) { 
	// type 0: Validate coupon
	// type 1: Enter contest

	var coupon = $('input[name="gift_card"]').val();
	if (type == 0) {
		var action = 'validate';
		var data_type = 'html';
	} else if (type == 1) { 
		var action = 'enter';
		var data_type = 'html';
	} else if (type == 2) {
		var action = 'credit';
		var data_type = 'json';
	}
	//console.log(coupon);

	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/process_coupon.php",
		data: "contest="+contest+"&coupon="+coupon+"&action="+action, 
		cache: false, 
		dataType: data_type,
		success: function(html) { 
			if(html == 1) {
				location.reload();
			} else { 
				$('#status_val').html(html);
				if (type == 1) { 				
					$('#waiter').html(' Please wait...');
					setTimeout(function() {
						$('#loader').html('<div id="remove" class="progress md-progress young-passion-gradient"> <div class="indeterminate"></div> </div>');
					}, 1000);
					setTimeout(function() {
						window.top.location=siteUrl+'/index.php?a=enter&success='+contest+'&process=giftcard';
					}, 5000);
				} else if (type == 2) {
					$('#status_val').html(html.message);
					$('#balance').html(html.balance);
				}						   
			}
		},
	    error: function(xhr, status, error){
	        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
	        $('#status_val').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );  
	    }
	});
}

function giveVote(contestant_id, contest_id, types) {
	// id = unique id of the contest 

	// Type 1: Vote from profile
	// Type 0: Vote from Contest
 	if (types == 0) {
 		var type = 'contest';
 	}else if (types == 1) {
 		var type = 'profile';
 	}
	$('#vote'+contestant_id).removeAttr('onclick');
	$('#vote'+contestant_id).html('<div class="loader small"></div>');
	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/vote.php",
		data: "contestant_id="+contestant_id+"&contest_id="+contest_id+"&type="+type, 
		dataType:"json",
		success: function(data) {
			
			if (data.bal == 1) {
				$('#charge'+contestant_id).html(data.notice);
				$('#vote'+contestant_id).attr('onclick', 'giveVote('+contestant_id+', '+contest_id+', '+types+')');
				$('#vote'+contestant_id).html('Vote');
			} else {
				$('#charge'+contestant_id).html(data.notice);
				$('#vote'+contestant_id).hide();
			}
			$('#count_votes'+contestant_id).html(data.count);
 			var $msg = $('#vote-msg'+contestant_id).html(data.message);
			$('#vote-msg'+contestant_id).prepend($msg);
			setTimeout(function() {
			    $('vote-msg'+contestant_id).hide();
			}, 3000);
		},
	    error: function(xhr, status, error){
	        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error; 
	        $('#vote-msg'+contestant_id).html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );  
	    }
	});
}

function loadExplorer(start, limit, page, type) { 
	$('.saving-load').show();
	if(type == 1) {
		var url2go = 'contests';
		$('#t2').attr('class', 'nav-item nav-link');
		$('#t1').attr('class', 'nav-item nav-link active');
	} else {
		var url2go = 'profiles';
		$('#t1').attr('class', 'nav-item nav-link');
		$('#t2').attr('class', 'nav-item nav-link active'); 
	}
	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/explore_"+url2go+".php",
		data: "start="+start+"&limit="+limit+"&page="+page, 
		cache: false,
		success: function(html) {
			$('#load-more').remove();
			$('#saving-load').remove();
			$('#search_mast').remove();
			
			// Append the new content to the div id
			$('#content_collector').html(html); 
		},
	    error: function(xhr, status, error){
	        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
	        $('#content_collector').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );  
	    }
	});
}

function do_Asearch(type) { 
	// Type 1:  Explore Search
	// Type 2:  Search box search

	if(type == 1) { 
		$('.saving-load').show();
		$('#navigation_explore').html('<div id="search_mast" class="col-md-12 text-white p-1 h4">Search</div>'); 
		$('#navigation_explore').attr('class', 'aqua-gradient');
		$('#loader_bar').html('<div id="remove" class="progress md-progress young-passion-gradient"> <div class="indeterminate"></div> </div>');
		var query = $('input[name="search_contest"]').val();

		$('#t2').attr('class', 'nav-item nav-link');
		$('#t1').attr('class', 'nav-item nav-link active');
	} else { 
 		var query = $('input[name="search"]').val();
	}	
 	 
	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/search.php",
		data: "search="+query+"&type="+type, 
		cache: false,
		success: function(html) { 
			$('#remove').remove();
			if (query.length>=1 && query !=' ' && query !='') {
				if(type == 1) {
					// Append the search result content to the div id
					$('#content_collector').html(html); 
				} else {
					$('#search_results').html(html); 
				}
			}
		}
	});
}

function loadFeature(start, limit, page, type) { 
	$('.saving-load').show();
	if(type == 1) {
		var url2go = 'contests';
		$('#t2').attr('class', 'nav-item nav-link');
		$('#t1').attr('class', 'nav-item nav-link active');
	} else {
		var url2go = 'profiles';
		$('#t1').attr('class', 'nav-item nav-link');
		$('#t2').attr('class', 'nav-item nav-link active'); 
	}
	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/featured_"+url2go+".php",
		data: "start="+start+"&limit="+limit+"&page="+page, 
		cache: false,
		success: function(html) {
			$('#load-more').remove();
			$('#saving-load').remove();
			
			// Append the new content to the div id
			$('#content_collector').html(html); 
		}
	});
}

function loadTable(start, limit, page, type, contest) {  
	if(type == 1) {
		var table = 'created_profiles'; 
	} else {
		var table = 'profiles';  
	}
	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/"+table+"_tables.php",
		data: "start="+start+"&limit="+limit+"&page="+page+"&contest="+contest, 
		cache: false,
		dataType:"json",
		success: function(data) { 
			$('#saving-load').remove();
			
			// Append the data to the div id
			$('#table_body').html(data.table_content); 
			$('#navigation').html(data.navigation);  
			$('#loaders').html(data.loader); 
		},
	    error: function(xhr, status, error){
	        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
	        $('#table_body').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );  
	    }
	});
}

function load_manage_admin(start, limit, page, type, per=null) {   
	// Type 0: Contest
	// Type 1: Users
	// Type 2: Payments
	// Type 3: Cashout requests
	
	if (per !==null) {
		var limit = $('input[name="perpage"]').val();
	} else {
		var limit = limit; 
	}
	$('#main_loader').html('<div id="remove" class="progress md-progress peach-gradient"> <div class="indeterminate"></div> </div>');
		
	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/administer.php",
		data: "start="+start+"&limit="+limit+"&page="+page+"&type="+type, 
		cache: false,
		dataType:"json",
		success: function(data) {  
			
			// Append the data to the div id 
			$('#content').html(data.main_content); 
			$('#navigation').html(data.navigation);
			$('#remove').remove();  
		},
	    error: function(xhr, status, error){
	        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
	        $('#content').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );  
	    }
	});
}

function navNavigator(start, limit, page, id) {   
	$.ajax({
		type: "POST",
		url: siteUrl+"/index.php?a=voting&id=",
		data: "start="+start+"&limit="+limit+"&page="+page+"&id="+id, 
		cache: false, 
		success: function(data) {   
		}
	});
}

function loadAccountsTable(start, limit, page, type) { 
	$('.saving-load').show();
	if(type == 1) {
		var table = 'my_votes'; 
	} else {
		var table = 'my_votes';  
	}
	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/"+table+"_tables.php",
		data: "start="+start+"&limit="+limit+"&page="+page, 
		cache: false,
		dataType:"json",
		success: function(data) { 
			$('#saving-load').remove();
			
			// Append the data to the div id
			$('#table_body').html(data.table_content); 
			$('#navigation').html(data.navigation);  
			$('#loaders').html(data.loader);  
			$('#no_content').html(data.no_content); 
		},
	    error: function(xhr, status, error){
	        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
	        $('#table_body').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );  
	    }
	});
}

function profileModal(contestant_id, contest, type) {  
	
	if(type == 0) {
		var view = 'detail';
		var modal = 'profile'; 
		var action = 'profile';
	} else if(type == 1) {
		var comme = $('input[name="contestant"]').val();
		var view = 'comments';  
		var modal = 'comments';
		var action = 'comments';
	} else {
		var view = 'detail';
		var modal = 'profile'; 
		var action = 'contest';
	} 

	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/view_"+view+".php",
		data: "contestant_id="+contestant_id+"&contest_id="+contest+"&view="+action, 
		dataType:"json",
		success: function(data) { 
			$('#'+modal+'Modal').modal('show');
			// Append the new content to the div id
			$('#'+modal+'-container').html(data.view_content);  
			$('#contestant_name').html(data.contestant_n); 
			$('#comment_footer').html(data.footer); 
		},
	    error: function(xhr, status, error){
	        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error; 
	        $('#'+modal+'Modal').modal('show');
	        $('#'+modal+'-container').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );
	    }		
	});
}

function shareModal(type, id) {  
	// id: Contest or contestant 
	// Type 1: Share Contest
	// Type 2: Share Contestant

	// Type 3: Follow Contest on social
	// Type 3: Post

	if (type == 1) {
		var content = 'contest';
		var modal = 'share';
	} else if (type == 2) {
		var content = 'contestant';
		var modal = 'share';
	} else if (type == 3) {
		var content = 'contest';
		var modal = 'follow';
	} else if (type == 4) {
		var content = 'post';
		var modal = 'share';
	}
	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/"+modal+"_modal.php",
		data: "type="+content+"&id="+id, 
		success: function(data) { 
			// Append the new content to the div id
			if (type == 1 || type == 2 || type == 4) {
				$('#sharer').html(data);
				$('#sharingModal').modal('show');
				$('#fb_sharer').attr('onclick', 'shareNow(1, '+type+', '+id+')'); 
				$('#tw_sharer').attr('onclick', 'shareNow(2, '+type+', '+id+')'); 
				$('#gplus_sharer').attr('onclick', 'shareNow(3, '+type+', '+id+')'); 
				$('#pin_sharer').attr('onclick', 'shareNow(4, '+type+', '+id+')');
			} else if (type == 3) {
				$('#follow_first').html(data);
				$('#followModal').modal('show');
			}
		}		
	});
}

function shareNow(where, type, id) {
	// where 1: Facebook
	// where 2: Twitter
	// where 3: Google+
	// where 4: Pinterest 
	
	// Type 1: Contest
	// Type 2: Contestant
	
	if(type == 1) {
		var photo = encodeURIComponent($("#photo_"+id).attr('src'));
		var url = encodeURIComponent($("#contest-url"+id).attr('href'));
		var title = 'Vote your favorite contestant in '+encodeURIComponent($("#contest-url"+id).text());
	} else if(type == 2) {
		var photo = encodeURIComponent($("#photo_"+id).attr('src'));
		var url = encodeURIComponent($("#profile-url"+id).attr('href'));
		var title = 'Vote for '+encodeURIComponent($("#profile-url"+id).text());
	} else {
		var photo = encodeURIComponent($("#post_photo_"+id).attr('src'));
		if (photo == 'undefined') {
			var photo = encodeURIComponent($("#post_photo_alt"+id).attr('src'));
		} 
		var url = encodeURIComponent($("#post_share_url_"+id).attr('value'));
		var title = encodeURIComponent($("#post_share_title_"+id).attr('value'));
	}
	
	if(where == 1) {
		window.open("https://www.facebook.com/sharer/sharer.php?u="+url, "", "width=500, height=300");
	} else if(where == 2) {
		window.open("https://twitter.com/intent/tweet?text="+title+"&url="+url, "", "width=500, height=300");
	} else if(where == 3) {
		window.open("https://plus.google.com/share?url="+url, "", "width=500, height=300");
	} else if(where == 4) {
		window.open("https://pinterest.com/pin/create/button/?url="+url+"&description="+title+"&media="+photo, "", "width=500, height=300");
	} 
}

// Process new comments
function addComment(sender, receiver, master, type, reply_to) {
	 
	if (reply_to) {
		var content = $('input[name="reply"]').val();
		reply = reply_to;
		var type = 1;
		var po = 'r';
	} else {
		var content = $('textarea[name="comment"]').val(); 
		reply = null;
		var type = 3;
		var po = 'c';
	}
	$('#save-load').show();
	$('#reply').removeAttr('onclick');	
	$('#comment-btn').removeAttr('onclick');	  	

	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/add_comment.php",
		data:{comment:content,sender:sender,receiver:receiver,master:master,type:type,reply:reply},
		cache: false,
		success: function(html) { 
			if(html == 1) {
				location.reload();
			} else { 
				$('#'+po+'_popover').data('content', html);  
				//$('#return-message').html(html); 
				
				var $pop = $('#'+po+'_popover').popover('show');
				$('#'+po+'_popover').prepend($pop);
				setTimeout(function() {
				    $('#'+po+'_popover').popover('hide');  
				    profileModal(receiver, master, 1); 
					$('#save-load').hide();
				}, 2000);

				$('#reply').attr('onclick', 'addComment('+sender+', '+receiver+', '+master+', '+type+', '+reply_to+')'); 
				$('#comment-btn').attr('onclick', 'addComment('+sender+', '+receiver+', '+master+', '+type+')'); 
			}
		}
	});
}

// Main Comments
function write_real_comment(sender, receiver, master, type, reply_to) { 
	if (reply_to) { 
		type = 0;
		reply = reply_to;		
		var content = $('input[id="reply_'+reply_to+'"]').val();
	} else { 
		type = type;
		reply = null;
		var content = $('input[id="comment_'+master+'"]').val();
	}

	$('#comment_block_'+master).html('<div class="progress md-progress peach-gradient"> <div class="indeterminate"></div> </div>');
	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/add_comment.php",
		data:{comment:content,sender:sender,receiver:receiver,master:master,type:type,reply:reply},   
		dataType:"html",
		success: function(data) { 
			// Append the notifications to the id
			// $('#comment_block_'+post_id).html(data); 
			if (reply_to) {
				$('#new-reply_'+reply_to).append(data);
				$('input[id="reply_'+reply_to+'"]').val('');
			} else {
				$('#new-comment').append(data);
				$('input[id="comment_'+master+'"]').val('');
			}
			
			$('.progress').hide();
		},
	    error: function(xhr, status, error){
	        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
	        $('#comment_block_'+post_id).html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );  
	    }
	});
}

// Load notifications
function loadNotifications(view = '', type) { 
	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/load_notifications_all.php",
		data:{view:view,type:type},   
		dataType:"json",
		success: function(datas) {
			// Append the notifications to the id 
			if (type) {
				$('#message-notifications').html(datas.notification); 
				$('#msg-counter').html(datas.count); 
				$('#msg-noticeX').html(datas.notice);
			} else {
				$('#notifications-menu').html(datas.notification); 
				$('#counter').html(datas.count); 
				$('#noticeX').html(datas.notice);				
			}

		},
	    error: function(xhr, status, error){
	        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error; 
	        	$('#message-menu, #notifications-menu').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );
	    }
	});
}

// View all notifications in the notifications page
function viewAllNotifications(view = '', start, limit, page, id = '') { 
	if (id != '') {
		var nid = id; 
	} else {
		var nid = 0; 
	} 
	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/load_notifications.php",
		data: "view="+view+"&start="+start+"&limit="+limit+"&page="+page+"&notification_id="+nid, 
		dataType:"json",
		success: function(data) { 
			
			// Append the notifications to the id
			$('#notifications-container').html(data.n_content); 
			$('#notice').html(data.notice);
			$('#pager,#pager2').html(data.page); 
		},
	    error: function(xhr, status, error){
	        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error;  
	        $('#notifications-container').html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );  
	    }
	});
}

function readmore(id, type) {

	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/readmore.php",
		data: "id="+id+"&type="+type, 
		dataType:"html",
		success: function(data) {
			$('#description_'+id).html(data);
		}
	});
}

function relate(the_id, type, user_id='', c_type='') {
	// Type 0: Follow
	// Type 1: UnFollow
	// Type 2: Like
	// Type 3: Unlike
	// Type 4: Show likes
	// Type 5: Profile Card

	if (type == 0) {
		var action = 'follow';
	} else if (type == 1) {
		var action = 'unfollow';		
	} else if (type == 2) {
		var action = 'like';		
	} else if (type == 3) {
		var action = 'unlike';		
	} else if (type == 4) {
		$('#modal_menu').html('');	
		$('#modal_btn_'+the_id).removeAttr('onclick');
		var action = 'showlike';	
	} else if (type == 5) {
		var action = 'quickinfo'; 		
	} 

	// c_type 1: post
	if (c_type == 1) {
		var content_type = 'post';
	}

	$.ajax({
		type: "POST",
		url: siteUrl+"/connection/relationships.php",
		data: "leader="+the_id+"&user_id="+user_id+"&action="+action+"&type="+content_type, 
		dataType:"json",
		success: function(data) {
			if (type == 0 || type == 1) {
				if (data.status == 1) {
					$('#follow_link_'+the_id+', #modal_follow_link_'+the_id).attr('onclick', 'relate('+the_id+', 1)');  
				} else {
					$('#follow_link_'+the_id+', #modal_follow_link_'+the_id).attr('onclick', 'relate('+the_id+', 0)');  
				}
				$('#follow_link_'+the_id+', #modal_follow_link_'+the_id).html(data.new_action); 
				$('#followers_count_'+the_id+', #modal_followers_count_'+the_id).html(data.count); 	 				
			} else if (type == 2 || type == 3) {
				if (data.status == 1) {
					$('#like_btn_'+the_id).attr('onclick', 'relate('+the_id+', 3, '+user_id+', '+c_type+')');
					$('#thumb_'+the_id).attr('class', 'fa fa-thumbs-up text-info');
				} else {
					$('#like_btn_'+the_id).attr('onclick', 'relate('+the_id+', 2, '+user_id+', '+c_type+')');
					$('#thumb_'+the_id).attr('class', 'fa fa-thumbs-up');
				}
				$('#like_count_'+the_id).html(data.count+' '+data.like);				
			} else if (type == 4) {  
				$('#sharer').html(data.modal);
				$('#showLikesModal').modal('show'); 
			} else if (type == 5) {
				$('#profile_link_'+the_id).append(data.infoCard); 
			}
		},
	    error: function(xhr, status, error){
	        var errorMessage = 'An Error Occurred - ' + xhr.status + ': ' + xhr.statusText + '<br> ' + error; 
	        $('#follow_link_, #followers_count_'+the_id+', #like_count_'+the_id).html( 
	        	'<div class="card m-2 text-center">'+
	        		'<div class="card-header p-2">Server Response: </div>'+
	        		'<div class="card-body p-2 text-info">'+
	        			'<div class="card-text font-weight-bold text-danger">'+errorMessage+'</div>'
	        			+xhr.responseText+
	        		'</div>'+
	        	'</div>'
	        );    
	    }
	});
}

function hidden_form(form) {
	$('#form_'+form).slideToggle(); 
}
