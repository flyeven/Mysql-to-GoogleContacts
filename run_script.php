<html>
	<head>
		<style>
			body{background:rgb(202,202,202);font-size:15px;font-family:courier;}form{background:white;width:69%;margin:50pxauto;padding:30px;box-shadow:0px0px17px5px#8888A5;}input[type='text']{width:100%;padding-top:3px;padding-bottom:3px;font-size:15px;font-family:courier;margin-bottom:50px;}input[type='submit']{border:0px;background:rgb(0,189,0);color:#fff;padding:10px34px;text-transform:uppercase;}
		</style>
	</head>
	<body>
		<form action="https://accounts.google.com/o/oauth2/auth">
			Client ID:<br />
			<input name="client_id" value="YOUR CLIENT ID" type="text" readonly="readonly" />
			<br>
			<br>

			Redirect URI ID:<br />
			<input name="redirect_uri" value="http://localhost/validate.php" type="text" readonly="readonly" />
			<br>
			<br>
			Scope:<br />
			<input name="scope" value="https://www.google.com/m8/feeds/" type="text" readonly="readonly" />
			<br>
			<br>
			Response Type:<br />
			<input name="response_type" value="code" type="text" readonly="readonly" />
			<br>
			<br>
			<input type="submit" value="import" / >
		</form>


	</body>
</html>
