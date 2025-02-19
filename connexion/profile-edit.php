<?php 

	require 'functions.php';

	if(!is_logged_in())
	{
		redirect('login.php');
	}

	$id = $_GET['id'] ?? $_SESSION['PROFILE']['id'];

	$row = db_query("select * from users where id = :id limit 1",['id'=>$id]);

	if($row)
	{
		$row = $row[0];
	}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Modifier le profil</title>
	<link rel="stylesheet" type="text/css" href="./css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="./css/bootstrap-icons.css">
</head>
<body>

	<?php if(!empty($row)):?>
	
		<div class="row col-lg-8 border rounded mx-auto mt-5 p-2 shadow-lg">
			<div class="col-md-4 text-center">
				<img src="<?=get_image($row['image'])?>" class="js-image img-fluid rounded" style="width: 180px;height:180px;object-fit: cover;">
				<div>
					<div class="mb-3">
					  <label for="formFile" class="form-label">Click pour changer d' image</label>
					  <input onchange="display_image(this.files[0])" class="js-image-input form-control" type="file" id="formFile">
					</div>
					<div><small class="js-error js-error-image text-danger"></small></div>
				</div>
			</div>
			<div class="col-md-8">
				
				<div class="h2">Modofier le Profil</div>

				<form method="post" onsubmit="myaction.collect_data(event, 'profile-edit')">
					<table class="table table-striped">
						<tr><th colspan="2">Details de l'utilisateur:</th></tr>
						<tr><th><i class="bi bi-envelope"></i> Email</th>
							<td>
								<input value="<?=$row['email']?>" type="text" class="form-control" name="email" placeholder="Email">
								<div><small class="js-error js-error-email text-danger"></small></div>
							</td>
						</tr>
						<tr><th><i class="bi bi-person-circle"></i> Nom</th>
							<td>
								<input value="<?=$row['firstname']?>" type="text" class="form-control" name="firstname" placeholder="First name">
								<div><small class="js-error js-error-firstname text-danger"></small></div>
							</td>
						</tr>
						<tr><th><i class="bi bi-person-square"></i> Prenom</th>
							<td>
								<input value="<?=$row['lastname']?>" type="text" class="form-control" name="lastname" placeholder="Last name">
								<div><small class="js-error js-error-lastname text-danger"></small></div>
							</td>
						</tr>
						<tr><th><i class="bi bi-gender-ambiguous"></i> Genre</th>
							<td>
								<select name="gender" class="form-select form-select mb-3" aria-label=".form-select-lg example">
								  <option value="">--Selectionner un genre--</option>
								  <option selected value="<?=$row['gender']?>"><?=$row['gender']?></option>
								  <option value="Male">Homme</option>
								  <option value="Female">Femme</option>
								</select>
								<div><small class="js-error js-error-gender text-danger"></small></div>
							</td>
						</tr>
						
						<tr><th><i class="bi bi-key"></i> Mot de passe</th>
							<td>
								<input type="password" class="form-control" name="password" placeholder=" laisser vide pour conserver l'ancien mot de passe">
								<div><small class="js-error js-error-password text-danger"></small></div>
							</td>
						</tr>
						<tr><th><i class="bi bi-key-fill"></i> Reecrire le Mot de passe</th>
							<td>
								<input type="password" class="form-control" name="retype_password" placeholder="réécrire le mot de passe">
							</td>
						</tr>

					</table>

					<div class="progress my-3 d-none">
					  <div class="progress-bar" role="progressbar" style="width: 50%;" >Fonctionnement... 25%</div>
					</div>

					<div class="p-2">
						
						<button class="btn btn-primary float-end">Enregistrer</button>
						
						<a href="index.php">
							<label class="btn btn-secondary">Fermé</label>
						</a>

					</div>
				</form>

			</div>
		</div>

	<?php else:?>
		<div class="text-center alert alert-danger">Ce profil n'a pas été trouver</div>
		<a href="index.php">
			<button class="btn btn-primary m-4">Accueil</button>
		</a>
	<?php endif;?>

</body>
</html>

<script>

	var image_added = false;

	function display_image(file)
	{
		var img = document.querySelector(".js-image");
		img.src = URL.createObjectURL(file);

		image_added = true;
	}
 
	var myaction  = 
	{
		collect_data: function(e, data_type)
		{
			e.preventDefault();
			e.stopPropagation();

			var inputs = document.querySelectorAll("form input, form select");
			let myform = new FormData();
			myform.append('data_type',data_type);

			for (var i = 0; i < inputs.length; i++) {

				myform.append(inputs[i].name, inputs[i].value);
			}

			if(image_added)
			{
				myform.append('image',document.querySelector('.js-image-input').files[0]);
			}

			myaction.send_data(myform);
		},

		send_data: function (form)
		{

			var ajax = new XMLHttpRequest();

			document.querySelector(".progress").classList.remove("d-none");

			//reset the prog bar
			document.querySelector(".progress-bar").style.width = "0%";
			document.querySelector(".progress-bar").innerHTML = "Working... 0%";

			ajax.addEventListener('readystatechange', function(){

				if(ajax.readyState == 4)
				{
					if(ajax.status == 200)
					{
						//all good
						myaction.handle_result(ajax.responseText);
					}else{
						console.log(ajax);
						alert("An error occurred");
					}
				}
			});

			ajax.upload.addEventListener('progress', function(e){

				let percent = Math.round((e.loaded / e.total) * 100);
				document.querySelector(".progress-bar").style.width = percent + "%";
				document.querySelector(".progress-bar").innerHTML = "Working..." + percent + "%";
			});

			ajax.open('post','ajax.php', true);
			ajax.send(form);
		},

		handle_result: function (result)
		{
			console.log(result);
			var obj = JSON.parse(result);
			if(obj.success)
			{
				alert("Profile edited successfully");
				window.location.reload();
			}else{

				//show errors
				let error_inputs = document.querySelectorAll(".js-error");

				//empty all errors
				for (var i = 0; i < error_inputs.length; i++) {
					error_inputs[i].innerHTML = "";
				}

				//display errors
				for(key in obj.errors)
				{
					document.querySelector(".js-error-"+key).innerHTML = obj.errors[key];
				}
			}
		}
	};

</script>
