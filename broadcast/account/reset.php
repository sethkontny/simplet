<?php

	$TextTitle = 'Reset';
	$WebTitle = 'Reset &nbsp;&middot;&nbsp; Account';
	$Canonical = 'account/reset';
	$PostType = 'Page';
	$FeaturedImage = '';
	$Description = '';
	$Keywords = 'reset account';

	require_once '../../request.php';

if (htmlentities($Request['path'], ENT_QUOTES, 'UTF-8') == '/'.$Canonical) {

	if ($Member_Auth) { // Logged In

		header('Location: /account/', TRUE, 302);
		die();

	} elseif(isset($Mail)&&$Mail==true) {

		if(isset($_GET['key'])) { // Enter Password

			$Reset_Key = htmlentities($_GET['key'], ENT_QUOTES, 'UTF-8');

			$Key_Check = mysqli_query($MySQL_Connection, "SELECT * FROM `Resets` WHERE `Key`='$Reset_Key' AND `Active`='1' LIMIT 0, 1", MYSQLI_STORE_RESULT);
			if (!$Key_Check) exit('Invalid Query (Key_Check): '.mysqli_error($MySQL_Connection));
			$Key_Count = mysqli_num_rows($Key_Check);
			if ($Key_Count == 0) {
				$Error = 'Invalid Key.';
			} else {

				if(isset($_POST['pass'])) { // New Password

					$Pass_New = htmlentities($_POST['pass'], ENT_QUOTES, 'UTF-8');

					$Key_Fetch = mysqli_fetch_assoc($Key_Check); // Bring them to me. Alive.
					$Member_ID = $Key_Fetch['Member_ID'];

					$Salt = stringGenerator();

					$Pass_Hash = passHash($Pass_New, $Salt);

					$Time = time();

					$Reset = mysqli_query($MySQL_Connection, "UPDATE `Members` SET `Pass`='$Pass_Hash', `Salt`='$Salt', `Modified`='$Time' WHERE `ID`='$Member_ID' AND `Status`='Active'", MYSQLI_STORE_RESULT);
					if (!$Reset) exit('Invalid Query (Reset): '.mysqli_error($MySQL_Connection));

					$Key_Remove = mysqli_query($MySQL_Connection, "UPDATE `Resets` SET `Active`='0', `Modified`='$Time' WHERE `Key`='$Reset_Key'", MYSQLI_STORE_RESULT);
					if (!$Key_Remove) exit('Invalid Query (Key_Remove): '.mysqli_error($MySQL_Connection));

					require '../../header.php';

					echo '<h2>Pass Reset Sucessfully</h2>';
					echo '<h3>You should probably go <a href="login">login</a>.</h3>';

					require '../../footer.php';


				} else {

					require '../../header.php'; ?>

					<form class="col span_1_of_1" action="" method="post">
						<h2>Reset Password</h2>
						<div class="section group">
							<div class="col span_1_of_3"><label for="pass"><h3>Pass</h3></label></div>
							<div class="col span_1_of_6"><br></div>
							<div class="col span_1_of_2"><input type="password" name="pass" placeholder="Qwerty1234" required /></div>
						</div>
						<div class="section group">
							<div class="col span_1_of_3">
								<p>No account? &nbsp; <a href="signup">Sign Up</a></p>
								<p>Remembered it? &nbsp; <a href="login">Login</a></p>
							</div>
							<div class="col span_1_of_6"><br></div>
							<div class="col span_1_of_2"><input type="hidden" name="key" value="<?php echo $Key; ?>" /><input type="submit" value="Reset" /></div>
						</div>
					</form>
					<div class="clear"></div>

	<?php 			require '../../footer.php';

				}

			}

			if(isset($Error)) {

				require '../../header.php';

				echo '<h2>Reset Error</h2>';
				echo '<h3>'.$Error.'</h3>';

				require '../../footer.php';

			}

		} elseif(isset($_POST['mail'])) { // Send Mail

			$Reset_Mail = htmlspecialchars($_POST['mail'], ENT_QUOTES, 'UTF-8');

			$Member_Check = mysqli_query($MySQL_Connection, "SELECT * FROM `Members` WHERE `Mail`='$Reset_Mail' AND `Status`='Active'", MYSQLI_STORE_RESULT);
			if (!$Member_Check) exit('Invalid Query (Member_Check): '.mysqli_error($MySQL_Connection));

			$Member_Count = mysqli_num_rows($Member_Check);
			if ($Member_Count == 0) {
				$Error = 'There is no user registered with that email.';
			} else {

				$Fetch_Member = mysqli_fetch_assoc($Member_Check); // Bring them to me. Alive.
				$Member_ID = $Fetch_Member['ID'];; // Number
				$Member_Name = $Fetch_Member['Name'];; // Do they have a name?

				$Reset_Key = stringGenerator();
				$Time = time();

				$Reset_New = mysqli_query($MySQL_Connection, "INSERT INTO `Resets` (`Member_ID`, `Mail`, `Key`, `Active`, `IP`, `Created`, `Modified`) VALUES ('$Member_ID', '$Reset_Mail', '$Reset_Key', '1', '$User_IP', '$Time', '$Time');", MYSQLI_STORE_RESULT);
				if (!$Reset_New) exit('Invalid Query (Reset_New): '.mysqli_error($WriteConnection));

				require '../../Browning_Send.php';

				$Mail_Response = Browning_Send(
					$Reset_Mail,
					'Password Reset',
					'Hello '.$Member_Name.', you wanted to reset your password? '.$Request['scheme'].'://'.$Request['host'].'/account/reset?key='.$Reset_Key,
				);

				if($Mail_Response) {
					$Reset_Message = 'A Password Reset has been initiated. Please check your email.';
				} else {
					$Error = 'Unable to send mail.';
				}

			}

			require '../../header.php';

			if(isset($Error)) {
				echo '<h2>Password Reset Failed</h2>';
				echo '<h3>'.$Error.'</h3>';
			} else {
				echo '<h2>Password Reset Initiated</h2>';
				echo '<h3>An email has been sent to '.$Reset_Mail.'</h3>';
			}

			require '../../footer.php';

		} else { // Ask for Mail

			require '../../header.php';

			?>

			<form class="col span_1_of_1" action="" method="post">
				<h2>Reset Password</h2>
				<div class="section group">
					<div class="col span_1_of_3"><label for="mail"><h3>Mail</h3></label></div>
					<div class="col span_1_of_6"><br></div>
					<div class="col span_1_of_2"><input type="email" name="mail" placeholder="johnsmith@example.com" required /></div>
				</div>
				<div class="section group">
					<div class="col span_1_of_3">
						<p>No account? <a class="floatright" href="signup">Sign Up</a></p>
						<p>Remembered it? <a class="floatright" href="login">Login</a></p>
					</div>
					<div class="col span_1_of_6"><br></div>
					<div class="col span_1_of_2"><input type="submit" value="Reset" /></div>
				</div>
			</form>
			<div class="clear"></div>

<?php		require '../../footer.php';

		}

	} else {

		require '../../header.php'; ?>

		<h2>Sorry, this installation of Simplet does not support reseting passwords.</h2>
		<h4>If you are the owner of this site, you need to set the Mailgun API URL and Key for your site.</h4>

<?php	require '../../footer.php';

	}

} ?>
