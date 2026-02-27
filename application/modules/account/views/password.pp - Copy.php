<div class="row-div profile-div">
<h3>CHANGE PASSWORD </h3>

<ul>
		<li style="color:#E9003F; list-style-type: circle; margin-bottom:5px;" id="pass_length">Password length must be a minimum of 8 characters</li>
		<li style="color:#E9003F; list-style-type: circle; margin-bottom:5px;" id="pass_chars">Password must contain atleast 1 small letter, 1 capital letter, 1 numeric character and 1 special character</li>
		<li style="color:#E9003F; list-style-type: circle; margin-bottom:5px;" id="pass_consecutive">Password must be free of consecutive identical, all-numeric or all-alphabetic characters</li>
		<li style="color:green; list-style-type: circle; margin-bottom:5px;">Password must be changed no later than every 90 days (submit to verify)</li>
		<li style="color:green; list-style-type: circle; margin-bottom:5px;">Password must be different from the 12 previous passwords (submit to verify)</li>
	</ul>


<?php if($curr_pass != 0):?>
	
<?php endif;?>


<div>
  <!-- Modal -->
  <div class="modal" id="myModal" role="dialog" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
		<span class="close" onclick="closeModal()">&times;</span>
          <h4 class="modal-title" style="color:red;">OLD PASSWORD DETECTED (90+ day old)</h4>
        </div>
        <div class="modal-body">
          <p style="color:red;">PLEASE CHANGE YOUR PASSWORD.</p>
        </div>
        <div class="modal-footer">
		<button type="button" class="btn btn-default" onclick="closeModal()">Close</button>
        </div>
      </div>
      
    </div>
  </div>
  <script>
        function closeModal() {
            var modal = document.getElementById('myModal');
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            var modal = document.getElementById('myModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        };
</script>

</div>

<?php if(isset($alert))echo $alert;?>
<form method="post" action="account/password" name="password">
	<ul class="list-group pass-ul">
		<li class="list-group-item">
			<div class="title">OLD PASSWORD</div>
			<div class='form-div'><?php echo form_input($input_passold); ?><?php echo form_error('passold'); ?></div>
		</li>
		<li class="list-group-item">
			<div class="title">NEW PASSWORD</div>
			<div class='form-div'><?php echo form_input($input_passnew); ?><?php echo form_error('passnew'); ?></div>
			<div class='form-div'><strong><span id="validationMessage" style="color:red;"></span></strong></div>
		</li>
		<li class="list-group-item">
			<div class="title">PASSWORD CONFIRMATION</div>
			<div class='form-div'><?php echo form_input($input_passconf); ?><?php echo form_error('passconf'); ?></div>
		</li>
	</ul>
	<div class="btn-div">
		<input type="submit" class="btn-submit btn btn-info" name="submit" value="SAVE" id="save_btn"/> 
		<a href="account" class="btn-cancel btn btn-danger">CANCEL</a>
	</div>
</form>	
</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
  <script>

		const currentUrl = window.location.href;
		console.log(currentUrl);

        $("#save_btn").attr("disabled", true);

		if(currentUrl.includes('changepass=true')) {  
			<?php
				echo '
				var modal = document.getElementById("myModal");
				modal.style.display = "block";
				';
			?>
		}

        const passwordInput = document.getElementById('passnewjs');
        const validationMessage = document.getElementById('validationMessage');

        // Function to validate password
        function validatePassword() {
            const password = passwordInput.value;
			

            
            // Check if password length is at least 8 characters
            if (password.length >= 2) {
				
                // Regular expressions for password validation
                const hasUpperCase = /[A-Z]/.test(password);
                const hasLowerCase = /[a-z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/.test(password);
                var noConsecutiveIdenticals = (/([a-z])\1/i).test(password)
                var consecNumber = hasConsecutiveSameDigits(password)
                

                // Check all conditions
				if (password.length < 8){
				 	validationMessage.textContent = '** Password must contain at least 8 characters.';
                     $("#save_btn").attr("disabled", true);
                }else if (!hasUpperCase) {
                    validationMessage.textContent = '** Password must contain at least one uppercase letter.';
                    $("#save_btn").attr("disabled", true);
                } else if (!hasLowerCase) {
                    validationMessage.textContent = '** Password must contain at least one lowercase letter.';
                    $("#save_btn").attr("disabled", true);
                } else if (!hasNumber) {
                    validationMessage.textContent = '** Password must contain at least one numeric digit.';
                    $("#save_btn").attr("disabled", true);
                } else if (!hasSpecialChar) {
                    validationMessage.textContent = '** Password must contain at least one special character.';
                    $("#save_btn").attr("disabled", true);
                } else if (noConsecutiveIdenticals) {
                    validationMessage.textContent = '** Password cannot contain consecutive identical characters.';
                    $("#save_btn").attr("disabled", true);
                } else if (consecNumber){
                    validationMessage.textContent = '** Password cannot contain consecutive identical numbers.';
                    $("#save_btn").attr("disabled", true);
                }else {
                    validationMessage.textContent = '';
                    $("#save_btn").attr("disabled", false);
                }

            } else {
                // Reset validation message if length is less than 8
                validationMessage.textContent = '';
				$("#save_btn").attr("disabled", true);
            }










			// CHANGE TEXT COLOR
			var pass_length = false;
			var pass_chars = false;
			var pass_consecutive = false;
			// Password must be at least 8 characters long
			if(password.length >= 8){
				$('#pass_length').css('color', 'green')
				pass_length = true
			}else{
				pass_length = false
				$('#pass_length').css('color', '#E9003F')
			}

				// Check if password contains at least one uppercase letter
				if(/[A-Z]/.test(password) && /[a-z]/.test(password) && /\d/.test(password) && /[^A-Za-z0-9]/.test(password)){
				$('#pass_chars').css('color', 'green')
				pass_chars = true;
			}else{
				$('#pass_chars').css('color', '#E9003F')
				pass_chars = false
			}


			const regex = new RegExp(`(.)\\1{${2 - 1},}`, 'g');
			let consecutive = regex.test(password);

			if(!consecutive){
				$('#pass_consecutive').css('color', 'green')
				pass_consecutive = true;
			}else{
				$('#pass_consecutive').css('color', '#E9003F')
				pass_consecutive = false;
			}

			if(pass_length && pass_chars && pass_consecutive){
				$("#submitbtn").attr("disabled", false);
			}else{
				$("#submitbtn").attr("disabled", true);
			}

        }

        function hasConsecutiveSameDigits(str) {
            console.log(str)
            for (let i = 0; i < str.length - 1; i++) {
                if (str[i] === str[i + 1] && str[i] === str[i + 1]) {
                    console.log('true')
                    return true;
                }
            }
            console.log('false')
            return false;
        }

        // Attach keyup event listener to the password input field
        passwordInput.addEventListener('keyup', validatePassword);
    </script>



