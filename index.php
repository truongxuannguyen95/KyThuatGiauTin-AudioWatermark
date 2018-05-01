<!DOCTYPE html>
<html>

	<!--
	
	- Sử dụng PHP với version >= 7
	- Cấu hình lại file "php.ini" trong xampp/php
		upload_max_filesize = 100M
		post_max_size = 100M
		
	-->

	<?php
		ini_set('max_execution_time', 180);
		ini_set('memory_limit', '-1');
		error_reporting(0);
		date_default_timezone_set('asia/ho_chi_minh');
		if (!isset($_SESSION)) session_start();
		include "connectdb.php";

		class WavFile{
			private static $HEADER_LENGTH = 44;

			public static function ReadFile($filename) {
	            $filesize = filesize($filename);
	            if ($filesize<self::$HEADER_LENGTH)
	                return false;           
	            $handle = fopen($filename, 'rb');
	            $wav = array(
	                    'header'    => array(
	                        'chunkid'       => self::readString($handle, 4),
	                        'chunksize'     => self::readLong($handle),
	                        'format'        => self::readString($handle, 4)
	                        ),
	                    'subchunk1' => array(
	                        'id'            => self::readString($handle, 4),
	                        'size'          => self::readLong($handle),
	                        'audioformat'   => self::readWord($handle),
	                        'numchannels'   => self::readWord($handle),
	                        'samplerate'    => self::readLong($handle),
	                        'byterate'      => self::readLong($handle),
	                        'blockalign'    => self::readWord($handle),
	                        'bitspersample' => self::readWord($handle)
	                        ),
	                    'subchunk2' => array(
	                        'id'            => self::readString($handle, 4),
	                        'size'			=> self::readLong($handle),
	                        'data'          => null
	                        ),
	                    'subchunk3' => array(
	                    	'id'			=> null,
	                    	'size'			=> null,
	                        'data'          => null
	                        )
	                    );
	            $wav['subchunk2']['data'] = fread($handle, $wav['subchunk2']['size']);
	            $wav['subchunk3']['id'] = self::readString($handle, 4);
	            $wav['subchunk3']['size'] = self::readLong($handle);
				$wav['subchunk3']['data'] = fread($handle, $wav['subchunk3']['size']);
	            fclose($handle);
	            return $wav;
		    }

		    private static function readString($handle, $length) {
		        return self::readUnpacked($handle, 'a*', $length);
		    }

		    private static function readLong($handle) {
		        return self::readUnpacked($handle, 'V', 4);
		    }

		    private static function readWord($handle) {
		        return self::readUnpacked($handle, 'v', 2);
		    }

		    private static function readUnpacked($handle, $type, $length) {
		        $r = unpack($type, fread($handle, $length));
		        return array_pop($r);
		    }
			
		}
		
		$checkLogin = true;
		if(isset($_POST['btnUserLogin'])) {
			
			$userLogin = $_POST['userLogin'];
			$pswLogin = $_POST['pswLogin'];
			$hash_psw = sha1($pswLogin);
			$qr = $conn->prepare("select user from users where user = :user and password = :psw limit 1;");
			$qr->bindParam(":user", $userLogin, PDO::PARAM_STR);
			$qr->bindParam(":psw", $hash_psw, PDO::PARAM_STR);
			$qr->execute();
			if ($qr->rowCount() === 1){
				$row = $qr->fetch();
				$_SESSION['user'] = $row['user'];
				header('Location: index.php'); //Refresh lại để xóa input login;				
			}
			else {
				$checkLogin = false;
			}			
			
			$qr = $conn->prepare("select user, password from users");
			$qr->execute();
			$rs_allusers = $qr->fetchAll();
		}
		
		$checkSignin = -1;
		if(isset($_POST['btnUserSignin'])) {
			$userSignin = $_POST['userSignin'];
			$stPwsSignin = $_POST['stPwsSignin'];
			$ndPwsSignin = $_POST['ndPwsSignin'];
			$pwsSignin = sha1($stPwsSignin);
			$qr = $conn->prepare("select user from users where user = :user limit 1;");
			$qr->bindParam(":user", $userSignin, PDO::PARAM_STR);
			$qr->execute();
			if ($qr->rowCount() === 1){
				$checkSignin = 0;
			}
			else if($stPwsSignin != $ndPwsSignin) {
				$checkSignin = 2;
			} else {
				$checkSignin = 1;
				$qr = $conn->prepare("insert into users (user, password, permission) values (:user, :psw, 'user');");
				$qr->bindParam(":user", $userSignin, PDO::PARAM_STR);
				$qr->bindParam(":psw", $pwsSignin, PDO::PARAM_STR);
				$qr->execute();
			}			
		}
		
		$checkChooseFile = "Chưa có file nhạc nào được chọn";
		$signdat = "";
		$isChose = false;
		if(isset($_POST['btnCheckSign'])){
			$fileName = $_FILES["chooseFile"]["tmp_name"];		
			$fileType = strtolower($_FILES['chooseFile']['type']);
			if ($fileType == "audio/wav"){
				$checkChooseFile = $_FILES["chooseFile"]["name"];
				$isChose = true;
				//Đọc audio file
					
				$wavFile = new WavFile;
				$tmp = $wavFile->ReadFile($fileName);
				unlink($fileName);

				//Lấy mã nhị phân của signature

				function BintoText($bin){
					$text = "";
					for($i = 0; $i < strlen($bin)/8 ; $i++)
						$text .= chr(bindec(substr($bin, $i*8, 8)));
					return $text;
				}

				$subchunk3data = unpack("H*", $tmp['subchunk3']['data']);

				$signature = "";
				for($i = 0; $i < 80; $i++){
					$signature .= substr(str_pad(base_convert(substr($subchunk3data[1], $i*2, 2), 16, 2), 8, '0', STR_PAD_LEFT), 7, 1);
				}
				$lenofsigndat = BintoText(substr($signature, 0, 80));
				if (is_numeric($lenofsigndat)){
					for($i = 80; $i < 80+$lenofsigndat*8; $i++){
						$signature .= substr(str_pad(base_convert(substr($subchunk3data[1], $i*2, 2), 16, 2), 8, '0', STR_PAD_LEFT), 7, 1);
					}
					$signdat = BintoText(substr($signature, 80, $lenofsigndat*8));
				}
			}
		}

		$qr = $conn->prepare("select id, song, singer from multimedia where owner = 'admin' order by stt desc;");
		$qr->execute();
		$rs_allsongs = $qr->fetchAll();

		if(!isset($_SESSION['checkUpload'])){
			$_SESSION['checkUpload'] = -1;
		}
		if (isset($_SESSION['user'])){
			$qr = $conn->prepare("select permission from users where user = '" . $_SESSION['user'] .  "';");
			$qr->execute();
			$rs_permission = $qr->fetch();

			if ($rs_permission['permission'] == "admin"){
				if(isset($_POST['btnUploadFile'])){
					$fileName = $_FILES["chooseFileUpload"]["tmp_name"];
					$fileType = strtolower($_FILES['chooseFileUpload']['type']);

					if ($fileType == "audio/wav"){
							
						// Upload audio file lên Google Drive
							
						require_once 'google-api-php-client-2.2.1/vendor/autoload.php';
						$client = new Google_Client();
						putenv('GOOGLE_APPLICATION_CREDENTIALS=google-api-php-client-2.2.1/service_account_keys.json');
						$client = new Google_Client();
						$client->addScope(Google_Service_Drive::DRIVE);
						$client->useApplicationDefaultCredentials();
						$service = new Google_Service_Drive($client);

						$content = file_get_contents($fileName);
						$fileMetadata = new Google_Service_Drive_DriveFile(array('name' => $_POST['nameSinger'] . " - " . $_POST['nameSong'] . ".wav"));
						$file = $service->files->create($fileMetadata, array(
							'data' => $content,
							'mimeType' => 'audio/wav',
							'uploadType' => 'multipart',
							'fields' => 'id'));
						$fileId = $file->id;
						unlink($fileName);

						//Share file

						$service->getClient()->setUseBatch(true);
						$batch = $service->createBatch();
						$filePermission = new Google_Service_Drive_Permission(array(
							'type' => 'anyone',
							'role' => 'reader',
						));
						$request = $service->permissions->create($fileId, $filePermission, array('fields' => 'id'));
						$batch->add($request, 'anyone');
						$results = $batch->execute();
						$service->getClient()->setUseBatch(false);
						$fileUrl = "https://drive.google.com/file/d/" . $fileId . "/view?usp=sharing";
							
						// Lưu file được mua vào Database

						$qr = $conn->prepare("insert into multimedia (id, parentid, song, singer, url, owner) values (:id, :parentid, :song, :singer, :url, 'admin');");
						$qr->bindParam(":id", $fileId, PDO::PARAM_STR);
						$qr->bindParam(":parentid", $fileId, PDO::PARAM_STR);
						$qr->bindParam(":song", $_POST['nameSong'], PDO::PARAM_STR);
						$qr->bindParam(":singer", $_POST['nameSinger'], PDO::PARAM_STR);
						$qr->bindParam(":url", $fileUrl, PDO::PARAM_STR);
						$qr->execute();
							
						$_SESSION['checkUpload'] = 1;
					} else {
						$_SESSION['checkUpload'] = 0;
					}
				} 
			}
			$qr = $conn->prepare("select id, song, singer from multimedia where owner = '" . $_SESSION['user'] . "' order by stt desc;");
			$qr->execute();
			$rs_myplaylist = $qr->fetchAll();		
		}			
	?>
	<head>
		<title>Kĩ thuật giấu tin</title>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
		<link rel="stylesheet" href="style.css?ts=<?=time()?>" />
		<link href="https://fonts.googleapis.com/css?family=Oregano:400|Open+Sans:400|Roboto+Condensed:400,600,700" rel="stylesheet">
	</head>
	<body>
		<div id="header">
		    <div class="container">
				<div class="header-title">
					<span class="header-brand"><?php echo "AudioWatermark"; ?></span>
				</div>
				<div class="right">
					<?php
						if (isset($_SESSION['user'])){
							echo "<form action=\"\" method=\"post\">
								<div id=\"logged-in\">
									<a href=\"logout.php\"><img src=\"img/log_out.png\" title=\"Thoát\" class=\"logout\" ></a>
									<span class=\"username\" style=\"font-weight: bold; font-size: 18px; font-family: 'Snell Roundhand', cursive;\">Xin chào, " . $_SESSION['user'] . "</span>
								</div></form>";
						}
						else{ 
							echo "<div id=\"no-login\">
									<button class=\"btnUser\" onclick=\"document.getElementById('login').style.display='block'; document.getElementById('signinSuccess').style.display='none'; document.getElementById('loginFail').style.display = 'none'; document.getElementById('loginForm').className = 'modal-content-login animate';\" style=\"width:auto;\">Đăng nhập</button>
									<button class=\"btnUser\" onclick=\"document.getElementById('signin').style.display='block'; document.getElementById('signinFail').style.display='none'; document.getElementById('signinForm').className = 'modal-content-signin animate';\" style=\"width:auto;\">Đăng ký</button>
								</div>";
						}
					?>
					<div id="login" class="modal">
						<form id="loginForm" class="modal-content-login animate" action="" method="post">
							<div class="imgcontainerForm">
								<span onclick="document.getElementById('login').style.display='none'" class="close" title="Close Modal">&times;</span>
								<img src="img/avatar.png" alt="Avatar" class="avatar">
							</div>
							<div class="containerForm">
								<div id="signinSuccess" style="text-color: blue; margin-bottom: 10px; display: none">
									<span style="color: blue">Chúc mừng bạn đăng ký thành công! Bạn có thể đăng nhập tại đây.</span>
								</div>
								<div id="loginFail" style="text-color: red; margin-bottom: 10px; display: none">
									<span style="color: red">Tài khoản hoặc mật khẩu sai!</span>
								</div>
								<label style="margin-top: 3px"><b>Tài khoản</b></label>
								<input type="text" title="Nhập tài khoản đăng nhập" id="userLogin" style="margin-top: -5px" placeholder="Nhập tài khoản đăng nhập" name="userLogin" required oninvalid="setCustomValidity('Vui lòng nhập tài khoản')" oninput="setCustomValidity('')">

								<label for="psw" style="margin-top: 3px"><b>Mật khẩu</b></label>
								<input type="password" title="Nhập mật khẩu đăng nhập" style="margin-top: -5px" placeholder="Nhập mật khẩu đăng nhập" name="pswLogin" required oninvalid="setCustomValidity('Vui lòng nhập mật khẩu')" oninput="setCustomValidity('')">
														
								<button name="btnUserLogin" style="margin-top: 15px" class="btnLoginSignin" type="submit">Đăng nhập</button>
								<button type="button" onclick="document.getElementById('login').style.display='none'" class="btnCancel">Hủy bỏ</button>
							</div>
						</form>
						<?php
							if(!$checkLogin) {
								?>
								<script type="text/javascript">
									document.getElementById('loginForm').className = 'modal-content-login-error animate';
									document.getElementById('loginFail').style.display = 'block';
									document.getElementById('login').style.display= 'block';
								</script>
								<?php
							}
						?>
					</div>
					
					<div id="signin" class="modal">
					<form id="signinForm" class="modal-content-signin animate" action="" method="post">
						<div class="imgcontainerForm">
							<span onclick="document.getElementById('signin').style.display='none';" class="close" title="Close Modal">&times;</span>
							<img src="img/avatar.png" alt="Avatar" class="avatar">
						</div>

						<div class="containerForm">
							<div id="signinFail" style="text-color: red; margin-bottom: 10px; display: none">
								<span id="errorSignin" style="color: red">Tài khoản đã tồn tại!</span>
							</div>
							
							<label style="margin-top: 3px"><b>Tài khoản</b></label>
							<input pattern="[a-zA-z0-9]{6,32}" title="Nhập tài khoản đăng ký" type="text" style="margin-top: -5px" placeholder="Nhập tài khoản đăng ký" name="userSignin" required oninvalid="setCustomValidity('Vui lòng nhập tài khoản')" oninput="setCustomValidity(''); checkValidity(); setCustomValidity(validity.valid ? '' :'Tài khoản phải từ 6 -> 32 ký tự và không chứa ký tự đặc biệt');">

							<label for="psw" style="margin-top: 3px"><b>Mật khẩu</b></label>
							<input pattern=".{6,32}" title="Nhập mật khẩu đăng ký" type="password" style="margin-top: -5px" placeholder="Nhập mật khẩu đăng ký" name="stPwsSignin" required oninvalid="setCustomValidity('Vui lòng nhập mật khẩu')" oninput="setCustomValidity(''); checkValidity(); setCustomValidity(validity.valid ? '' :'Mật khẩu phải từ 6 -> 32 ký tự');"/>
							
							<label for="psw"style="margin-top: 3px"><b>Xác thực</b></label>
							<input type="password" title="Nhập xác thực mật khẩu đăng ký" style="margin-top: -5px" placeholder="Nhập xác thực mật khẩu đăng ký" name="ndPwsSignin" required oninvalid="setCustomValidity('Vui lòng nhập xác thực mật khẩu')" oninput="setCustomValidity('')">
							
							<button name="btnUserSignin" class="btnLoginSignin" type="submit">Đăng ký</button>
							<button type="button" onclick="document.getElementById('signin').style.display='none'" class="btnCancel">Hủy bỏ</button>
						</div>
					</form>
					<?php
					if($checkSignin == 0 || $checkSignin == 2) {
						?>
						<script type="text/javascript">
							document.getElementById('signin').style.display = 'block';
							document.getElementById('signinFail').style.display = 'block';
							document.getElementById('signinSuccess').style.display = 'none'; 
							document.getElementById('signinForm').className = 'modal-content-signin-error animate';
						</script>
						<?php
						if($checkSignin == 0) {
							?>
							<script type="text/javascript">
								document.getElementById('errorSignin').innerHTML  = 'Tài khoản đã tồn tại!'
							</script>
							<?php
						} else {
							?>
							<script type="text/javascript">
								document.getElementById('errorSignin').innerHTML  = 'Xác thực mật khẩu không khớp!'
							</script>
							<?php
						}
					} else if($checkSignin == 1) {
						?>
						<script type="text/javascript">
							document.getElementById('login').style.display = 'block';
							document.getElementById('signinFail').style.display = 'none';
							document.getElementById('signinSuccess').style.display= 'block'
							document.getElementById('loginForm').className = 'modal-content-login-error animate';
						</script>
						<?php
					}
				?>		  
				</div>	
					<script>
						var login = document.getElementById('login');
						var signin = document.getElementById('signin');
						window.onclick = function(event) {
							if (event.target == login) {
								login.style.display = "none";
							}
							if (event.target == signin) {
								signin.style.display = "none";
							}
						}
					</script>	
				</div>
		    </div>
		</div>
		
		<div id="middle">
			<div id="viewContent">
				<button id="btnShowBuySong" class="btnViewContent" style="width:auto;">Mua nhạc</button>
				<button id="btnShowMySongs" class="btnViewContent" style="width:auto;">Nhạc của tôi</button>
				<button id="btnShowCheckSign" class="btnViewContent" style="width:auto;">Kiểm tra nhạc</button>
				<button id="btnShowUpload" class="btnViewContent" style="width:auto; display: none">Upload nhạc</button>
				<script>
					var btnShowBuySong = document.getElementById('btnShowBuySong');
					var btnShowMySongs = document.getElementById('btnShowMySongs');
					var btnShowCheckSign = document.getElementById('btnShowCheckSign');
					var btnShowUpload = document.getElementById('btnShowUpload');
					btnShowBuySong.className = 'btnClicked';
					window.onclick = function(event) {
						if (event.target == btnShowBuySong) {
							btnShowBuySong.className = 'btnClicked';
							btnShowMySongs.className = 'btnViewContent';
							btnShowCheckSign.className = 'btnViewContent';
							btnShowUpload.className = 'btnViewContent';
							document.getElementById('tableBuySong').style.display='block';
							document.getElementById('tableMySongs').style.display='none';
							document.getElementById('divCheckSign').style.display='none';
							document.getElementById('divUploadFile').style.display='none';
						}
						if (event.target == btnShowMySongs) {
							btnShowBuySong.className = 'btnViewContent';
							btnShowMySongs.className = 'btnClicked';
							btnShowCheckSign.className = 'btnViewContent';
							btnShowUpload.className = 'btnViewContent';
							document.getElementById('tableBuySong').style.display='none';
							document.getElementById('tableMySongs').style.display='block'
							document.getElementById('divCheckSign').style.display='none';
							document.getElementById('divUploadFile').style.display='none';
						}
						if (event.target == btnShowCheckSign) {
							btnShowBuySong.className = 'btnViewContent';
							btnShowMySongs.className = 'btnViewContent';
							btnShowCheckSign.className = 'btnClicked';
							btnShowUpload.className = 'btnViewContent';
							document.getElementById('tableBuySong').style.display='none';
							document.getElementById('tableMySongs').style.display='none';
							document.getElementById('divCheckSign').style.display='block';
							document.getElementById('divUploadFile').style.display='none';
						}
						if (event.target == btnShowUpload) {
							btnShowBuySong.className = 'btnViewContent';
							btnShowMySongs.className = 'btnViewContent';
							btnShowCheckSign.className = 'btnViewContent';
							btnShowUpload.className = 'btnClicked';
							document.getElementById('tableBuySong').style.display='none';
							document.getElementById('tableMySongs').style.display='none';
							document.getElementById('divCheckSign').style.display='none';
							document.getElementById('divUploadFile').style.display='block';
						}
					}
				</script>
				<?php
					if (isset($rs_permission['permission'])){
		       			if ($rs_permission['permission'] == "admin"){
							?>
					       	<script>
								document.getElementById('btnShowUpload').style.display = 'block';
							</script>
							<?php
				       	}
		       		}
				?>
			</div>
			<div id="content">
				<div id="report" class="modal">
					<form action="" method="post">
					<div class="modal-content-report animate">
						<label class="lbReport">Thông báo</label>
						<div class="containerForm">
							<label id="lbReportInfo"></label>
							<button id="btnOK" type="submit" style="margin-top: 10px;" class="btnCancel">Đồng ý</button>
						</div>
					</div>
					</form>
				</div>
				<table id="tableBuySong">
					<thead>
						<tr>
							<th style="padding-top: 10px; width: 10%">#</th>
							<th style="padding-top: 10px; width: 40%">Tên bài hát</th>
							<th style="padding-top: 10px; width: 30%">Tên ca sĩ</th>
							<th style="padding-top: 10px; width: 20%">Trạng thái</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$i = 1;
							foreach ($rs_allsongs as $key => $value) {
								echo "<tr class=\"" . ($i % 2 ? "odd" : "even") . "\">
										<td style=\"width: 10%\">" . $i . "</td>
										<td style=\"width: 40%\">" . $value['song'] . "</td>
										<td style=\"width: 30%\">" . $value['singer'] . "</td>
										<td style=\"width: 20%\">";

								if (isset($_SESSION['user'])){
									$qr = $conn->prepare("select id from multimedia where owner = '" . $_SESSION['user'] .  "' and parentid = '" . $value['id'] . "' limit 1;");
									$qr->execute();
									$rs_isLicenced = $qr->fetch();
									if ($rs_isLicenced['id'] == ""){
										echo "<button id=\"" . $value['id'] . "\" class=\"btnBuySong\">Mua nhạc</button>";
									}
									else{
										echo "<button disabled=\"true\">Đã mua</button>";
									}
								}
								else{
									echo "<span style=\"color: " . ($i % 2 ? "blue" : "red") . "\">Cần đăng nhập</span>";
								}
								echo "	</td>
									</tr>";
								$i++;
							}
						?>
					</tbody>				
				</table>
				
				<table id="tableMySongs" style="display: none">
					<thead>
						<tr>
							<th style="width: 100%; padding-top: 10px; font-size: 18px">Danh sách bài hát</th>
						</tr>
					</thead>
					<tbody>
						<?php
							if (isset($_SESSION['user'])){
								$i = 1;
								foreach ($rs_myplaylist as $key => $value){
									echo "<tr class=\"" . ($i % 2 ? "oddND" : "even") . "\">
										<td style=\"width: 10%\">" . $i . "</td>
										<td style=\"width: 40%\">" . $value['song'] . " - " . $value['singer'] . "</td>
										<td style=\"width: 50%\">
											<audio controls>
												<source src=\"http://docs.google.com/uc?export=open&id=" . $value['id'] . "&type=.wav\">					
											</audio>
										</td></tr>";
									$i++;
								}
								if($i == 1) {
									echo "<tr class=\"odd\">
											<td style=\"width: 100%\">Bạn chưa sở hữu bài hát nào!</td>
										 </tr>";
								}
							} else {
								echo "<tr class=\"odd\">
										<td style=\"width: 100%; color: red\">Bạn hiện tại chưa đăng nhập!</td>
									 </tr>";
							}
						?>
					</tbody>
				</table>
				
				<div id="divCheckSign" style="display: none">
					<div style=" height: 60px; align-items: center; justify-content: center; display: flex; margin-top: 15px">
						<label id="showResult"></label>
					</div>
					<form action="" method="post" enctype="multipart/form-data">
						<input id="chooseFile" name="chooseFile" type="file" accept="audio/wav" style="display: none;"/>
						<label id="nameChooseFile" class="lbName">Chưa có file nhạc được tải lên</label>
						<label title="Chọn file kiểm tra" for="chooseFile"><img src="img/search.png" class="upFile" /></label>
						<button id="btnCheckSign" name="btnCheckSign" title="Vui lòng chọn file để kiểm tra" class="btnUpload" disabled="true">Kiểm tra</button>
					</form>
					<?php 
						if(isset($_POST['btnCheckSign'])){
							?>
							<script>
								var fileName = '<?php echo $checkChooseFile; ?>';
								var Mess = '<?php echo "" . ($signdat!="" ? "Bài hát thuộc về: ". $signdat ."": "Không tìm thấy chữ ký nào") . ""; ?>';
								document.getElementById('showResult').innerHTML = Mess;
							</script>
							<?php
						}
						if($signdat=="")
						{
							if($isChose) {
								?>
								<script>
									document.getElementById('showResult').className = 'lbShowMessEmpty';
									document.getElementById('btnShowCheckSign').className = 'btnClicked';
									document.getElementById('btnShowBuySong').className = 'btnViewContent';
									document.getElementById('tableBuySong').style.display='none';
									document.getElementById('tableMySongs').style.display='none';
									document.getElementById('divCheckSign').style.display='block';
									document.getElementById('divUploadFile').style.display='none';
								</script>
								<?php
							} else {
								?>
								<script>
									document.getElementById('showResult').style.display = 'none';
								</script>
								<?php
							}
						} else {
							?>
							<script>
								document.getElementById('showResult').className = 'lbShowMess';
								document.getElementById('btnShowCheckSign').className = 'btnClicked';
								document.getElementById('btnShowBuySong').className = 'btnViewContent';
								document.getElementById('tableBuySong').style.display='none';
								document.getElementById('tableMySongs').style.display='none';
								document.getElementById('divCheckSign').style.display='block';
								document.getElementById('divUploadFile').style.display='none';
							</script>
							<?php
						}
					?>
				</div>
				
				<div id="divUploadFile" style="margin-top: 75px; display: none">
					<form id="formUploadFile" action="" method="post" enctype="multipart/form-data">
						<div>
							<label id="nameFileUpload" name="checkHadFile" class="lbName">Chưa có file nhạc được tải lên</label>
							<input id="chooseFileUpload" name="chooseFileUpload" type="file" accept="audio/wav" style="display: none;"/>
							<label title="Chọn file upload" for="chooseFileUpload"><img src="img/search.png" class="upFile" /></label>
						</div>
						<div>
							<label class="lbNameInfo">Tên bài hát</label>
							<input id="nameSong" name="nameSong" placeholder="Nhập tên bài hát" class="inputInfo"/>
						</div>
						<div style="margin-top: 20px">
							<label class="lbNameInfo">Tên ca sĩ</label>
							<input id="nameSinger" name="nameSinger" placeholder="Nhập tên ca sĩ" class="inputInfo"/>
						</div>
						<button type="submit" title="Vui lòng nhập đầy đủ thông tin" id="btnUploadFile" name="btnUploadFile" class="btnUpload" disabled="true">Upload nhạc</button>
					</form>
					<form action="" method="post">
					<div id="reportUpload" class="modal">
						<div class="modal-content-report animate">
							<label class="lbReport">Thông báo</label>
							<div class="containerForm">
								<label id="lbReportUpload"></label>
								<button type="submit" style="margin-top: 10px;" class="btnCancel">Đồng ý</button>
							</div>
						</div>
					</div>
					</form>
					<?php
						if($_SESSION['checkUpload'] != -1) {
							?>
							<script>
								document.getElementById('btnShowUpload').className = 'btnClicked';
								document.getElementById('btnShowBuySong').className = 'btnViewContent';
								document.getElementById('tableBuySong').style.display='none';
								document.getElementById('tableMySongs').style.display='none';
								document.getElementById('divCheckSign').style.display='none';
								document.getElementById('divUploadFile').style.display='block';
							</script>
							<?php
							if($_SESSION['checkUpload'] == 1) {
								unset($_SESSION['checkUpload']);
								?>
								<script>
									document.getElementById('reportUpload').style.display = 'block';
									document.getElementById('lbReportUpload').innerHTML = 'Upload nhạc thành công!';
									document.getElementById('lbReportUpload').style.color = 'blue';
								</script>
								<?php
							} else if($_SESSION['checkUpload'] == 0) {
								unset($_SESSION['checkUpload']);
								?>
								<script>
									document.getElementById('reportUpload').style.display = 'block';
									document.getElementById('lbReportUpload').innerHTML = 'Upload nhạc thất bại!';
									document.getElementById('lbReportUpload').style.color = 'red';
								</script>
								<?php
							}
						}
					?>
					<script>
						var hadFile = document.getElementById('nameFileUpload');
						var nameSong = document.getElementById('nameSong');
						var nameSinger = document.getElementById('nameSinger');
						var checkName = false;
						var checkSinger = false;
						var checkHadFile = false;
						nameSong.onkeyup = function() {
							console.log(this.value);
							if(this.value == "")
								checkName = false;
							else
								checkName = true;
							if(hadFile.textContent === "Chưa có file nhạc được tải lên")
							{
								checkHadFile = false;
							} else {
								checkHadFile = true;
							}
							if(checkName && checkSinger && checkHadFile) {
								document.getElementById('btnUploadFile').disabled = false;
								document.getElementById('btnUploadFile').title = 'Upload nhạc';
							} else {
								document.getElementById('btnUploadFile').disabled = true;
								document.getElementById('btnUploadFile').title = 'Vui lòng nhập đầy đủ thông tin';
							}
						};
						nameSinger.onkeyup = function() {
							console.log(this.value);
							if(this.value == "")
								checkSinger = false;
							else
								checkSinger = true;
							if(hadFile.textContent === "Chưa có file nhạc được tải lên")
							{
								checkHadFile = false;
							} else {
								checkHadFile = true;
							}
							if(checkName && checkSinger && checkHadFile) {
								document.getElementById('btnUploadFile').disabled = false;
								document.getElementById('btnUploadFile').title = 'Upload nhạc';
							} else {
								document.getElementById('btnUploadFile').disabled = true;
								document.getElementById('btnUploadFile').title = 'Vui lòng nhập đầy đủ thông tin';
							}
						};
					</script>
				</div>
			</div>
		</div>
		<div id="footer">
			<span class="footer-title"><?php echo "Designer by Trương Xuân Nguyên - N14DCAT002"; ?></span>
		</div>		
	</body>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script type="text/javascript" src="wow.min.js"></script>
	<script type="text/javascript">
	    new WOW().init();
		
    	$(".btnBuySong").click(function(){
	    	$("*").css("cursor", "wait");
	    	var buysongid = $(this).attr("id");
	    	$.ajax({
				url: "buysong.php",
				type: "POST",
				data: { buysongid : buysongid },
				success : function(response){
					$("*").css("cursor", "default");
					if (response == "Mua thành công"){
						$('#lbReportInfo').prop('style', 'color: blue');
						$('#lbReportInfo').html('Mua nhạc thành công!');
						$('#btnOK').prop('style', 'cursor: pointer');
						$('#report').prop('style', 'display: block');
					}
					else if (response == "Mua thất bại"){
						$('#lbReportInfo').prop('style', 'color: red');
						$('#lbReportInfo').html('Mua nhạc thất bại!');
						$('#btnOK').prop('style', 'cursor: pointer');
						$('#report').prop('style', 'display: block');
					}
				}
			});
    	});
		
		$('#chooseFile').change(function(){
			var filename = $('#chooseFile').val().split('\\').pop();
			if(filename.length !== 0) {
				$('#nameChooseFile').html(filename);
				$('#btnCheckSign').prop('disabled', false);
				$('#btnCheckSign').prop('title', 'Kiểm tra');
			} else {
				$('#btnCheckSign').prop('disabled', true);
				$('#btnCheckSign').prop('title', 'Vui lòng chọn file để kiểm tra');
			}
		});
		
		$('#chooseFileUpload').change(function(){
			var filename = $('#chooseFileUpload').val().split('\\').pop();
			if(filename.length !== 0)
				$('#nameFileUpload').html(filename);
			if($('#nameSong').val().length === 0 || $('#nameSinger').val().length === 0){
				$('#btnUploadFile').prop('disabled', true);
				$('#btnUploadFile').prop('title', 'Vui lòng nhập đầy đủ thông tin');
			} else {
				$('#btnUploadFile').prop('disabled', false);
				$('#btnUploadFile').prop('title', 'Upload nhạc');
			}
		});	
		
		$('#btnCheckSign').click(function(){
	    	$("*").css("cursor", "wait");
		});

		$('#btnUploadFile').click(function(){
	    	$("*").css("cursor", "wait");
		});
    </script>
</html>