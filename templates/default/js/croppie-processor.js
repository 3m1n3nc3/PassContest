var resize = $('#upload-profile').croppie({
    enableExif: true,
    enableOrientation: true,    
    viewport: { // Default { width: 100, height: 100, type: 'square' } 
      width: 300,
      height: 300,
      type: 'square' //square
    },
    boundary: {
      width: 310,
      height: 310
    }
}); 

var resize_cover = $('#upload-cover').croppie({
    enableExif: true,
    enableOrientation: true,    
    viewport: { // Default { width: 100, height: 100, type: 'square' } 
      width: 400,
      height: 230,
      type: 'square' //square
    },
    boundary: {
      width: 410,
      height: 240
    }
}); 

$('#prof-image').on('change', function () { 
    $('#upload-profile').show();
  var reader = new FileReader();
    reader.onload = function (e) {
      resize.croppie('bind',{
        url: e.target.result
      }).then(function(){
        console.log('jQuery bind complete');
      });
    }
    reader.readAsDataURL(this.files[0]);
});

$('#cover-image').on('change', function () { 
    $('#upload-cover').show();
  var reader = new FileReader();
    reader.onload = function (e) {
      resize_cover.croppie('bind',{
        url: e.target.result
      }).then(function(){
        console.log('jQuery bind complete');
      });
    }
    reader.readAsDataURL(this.files[0]);
});

function upload_action(type, user) {
  // type: 0 Profile
  // type: 1 Cover
  // type: 2 Contest Cover
  $('#saving-load').html('<div id="remove" class="progress md-progress young-passion-gradient"> <div class="indeterminate"></div> </div>'); 
  if (user != 0) {
    var uid = '&id='+user;
  } else {
    var uid = '';
  }

    if (type == 0) {
      resize.croppie('result', {
        type: 'canvas',
        size: {
            width: 500
        }
      }).then(function (img) {
        $.ajax({
          url: siteUrl+"/connection/upload.php?d=profile"+uid,
          type: "POST",
          data: {"ajax_image":img},
          success: function (data) {
            html = '<img src="' + img + '" />';
            $("#preview-crop-profile").html(html);
            $("#profile-photo-message").html(data);
            $("#upload-profile").hide();
            $("#saving-load").hide();
            $("#action-buttons").html('<div class="pt-2">&nbsp</div>');
          }
        });
      });
    } else if (type == 1) { 
      resize_cover.croppie('result', {
        type: 'canvas',
        size: {
            width: 1500
        }
      }).then(function (img) {
        $.ajax({
          url: siteUrl+"/connection/upload.php?d=cover"+uid,
          type: "POST",
          data: {"ajax_image":img},
          success: function (data) {
            html = '<img src="' + img + '" />';
            $("#preview-crop-cover").html(html);
            $("#cover-photo-message").html(data);
            $("#upload-cover").hide();
            $("#saving-load").hide();
            $("#action-buttons-2").html('<div class="pt-2">&nbsp</div>');
          }
        });
      });
    } else if (type == 2) { 
      resize_cover.croppie('result', {
        type: 'canvas',
        size: {
            width: 1500
        }
      }).then(function (img) {
        $.ajax({
          url: siteUrl+"/connection/upload.php?d=contest"+user,
          type: "POST",
          data: {"ajax_image":img},
          success: function (data) {
            html = '<img src="' + img + '" />';
            $("#preview-crop-cover").html(html);
            $("#cover-photo-message").html(data);
            $("#upload-cover").hide();
            $("#saving-load").hide();
            $("#action-buttons-2").html('<div class="pt-2">&nbsp</div>');
          }
        });
      });
    }
}  