function SubmitFormData() {
	var name = $("#username").val();
	var email = $("#email").val();
	var phone = $("#password").val(); 
	$.post("{$site_url}/connection/connect.php", { username: username, email: email, password: password },
	   function(data) {
		 $('#results').html(data);
		 $('#myForm')[0].reset();
	   });
}

