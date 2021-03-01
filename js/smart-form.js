	jQuery(document).ready(function($){

				function reloadCaptcha(){ $("#captchax").attr("src","php/captcha/captcha.php?r=" + Math.random()); }
				$('.captcode').click(function(e){
					e.preventDefault();
					reloadCaptcha();
				});
				
				function swapButton(){
					var txtswap = $(".smartforms-modal-footer button[type='submit']");
					if (txtswap.text() == txtswap.data("btntext-sending")) {
						txtswap.text(txtswap.data("btntext-original"));
					} else {
						txtswap.data("btntext-original", txtswap.text());
						txtswap.text(txtswap.data("btntext-sending"));
					}
				}
			   
				$("#smart-form").validate({
          /* @validation states + elements 
						------------------------------------------- */
          errorClass: "state-error",
          validClass: "state-success",
          errorElement: "em",
          onkeyup: false,
          onclick: false,

          /* @validation rules 
						------------------------------------------ */
          rules: {
            firstname: {
              required: true,
              minlength: 2,
            },
            lastname: {
              required: true,
              minlength: 2,
            },
            emailaddress: {
              required: true,
              email: true,
            },
            telephone: {
              required: true,
              minlength: 7,
            },
            address1: {
              required: true,
              minlength: 7,
            },
            buildingsize: {
              required: true,
            },
            numberoffloors: {
              required: true,
            },
            captcha: {
              required: true,
              remote: "php/captcha/process.php",
            }
          },
          messages: {
            firstname: {
              required: "Enter your first name",
              minlength: "Enter at least 2 characters",
            },
            lastname: {
              required: "Enter your last name",
              minlength: "Enter at least 2 characters",
            },
            emailaddress: {
              required: "Enter your email address",
              email: "Enter a VALID email address",
            },
            telephone: {
              required: "Enter your telephone number",
              minlength: "Number must be at least 7 digits",
            },
            address1: {
              required: "Enter your physical address",
              minlength: "Address must be at least 7 characters",
            },
            buildingsize: {
              required: "Select the size range for your building",
            },
            numberoffloors: {
              required: "Select the number of floors for your building",
            },
            captcha: {
              required: "You must enter the captcha code",
              remote: "Captcha code is incorrect",
            }
          },

          /* @validation highlighting + error placement  
						---------------------------------------------------- */
          highlight: function (element, errorClass, validClass) {
            $(element)
              .closest(".field")
              .addClass(errorClass)
              .removeClass(validClass);
          },
          unhighlight: function (element, errorClass, validClass) {
            $(element)
              .closest(".field")
              .removeClass(errorClass)
              .addClass(validClass);
          },
          errorPlacement: function (error, element) {
            if (element.is(":radio") || element.is(":checkbox")) {
              element.closest(".option-group").after(error);
            } else {
              error.insertAfter(element.parent());
            }
          },

          /* @ajax form submition 
						---------------------------------------------------- */
          submitHandler: function (form) {
            $(form).ajaxSubmit({
              target: ".result",
              beforeSubmit: function () {
                swapButton();
                $(".smartforms-modal-footer").addClass("progress");
              },
              error: function () {
                swapButton();
                $(".smartforms-modal-footer").removeClass("progress");
              },
              success: function () {
                swapButton();
                $(".smartforms-modal-footer").removeClass("progress");
                $(".alert-success").show().delay(7000).fadeOut();
                $(".field").removeClass("state-error, state-success");
                if ($(".alert-error").length == 0) {
                  $("#smart-form").resetForm();
                  reloadCaptcha();
                  setTimeout(function () {
                    $(".smartforms-modal").removeClass(
                      "smartforms-modal-visible"
                    );
                    $("body").removeClass("smartforms-modal-scroll");
                  }, 7500);
                }
              },
            });
          },
        });		
		
	});				
    