<?php
	require_once('dbconnection.php');
	require_once('XMLCreator.php');
	class AccountManager{
			private $xmlWriter;
			function __construct(){
				$this->xmlWriter = new XMLCreator(XMLCreator::ACCOUNT);
			}
			
			function login($user, $pass){
					$sql = "SELECT * FROM accounts WHERE username ='$user'";
					$res = mysql_query($sql);
					if($res){
						
						if($r = mysql_fetch_array($res)){
							if($r['password'] == $pass){
								$this->xmlWriter->add('username', $user);
								$this->xmlWriter->add('fullname', $r['fullname']);
								$this->xmlWriter->add('password', $r['password']);
								if($r['admin'])
									$this->xmlWriter->add('admin', 'true');
								else
									$this->xmlWriter->add('admin','false');
							}
							else{
								//echo "pass = $pass and result = ".$r[password]."\n";
								$this->xmlWriter->add('error', 'password incorrect');
							}
						}
						else
							$this->xmlWriter->add('error','No such user account');
					}
					else{
						$this->xmlWriter->add('error', mysql_error());						
					}
					
					$this->xmlWriter->save2();
					//$this->xmlWriter->saveFile("acc.xml");	
			} 
	}
?>
