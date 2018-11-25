<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Desarrollada por Pablo Sepúlveda Valenzuela  (NøName)*/
	require APPPATH.'/libraries/REST_Controller.php';
	// require APPPATH . '/libraries/Authentication.php';
	class Login extends REST_Controller {

		// llama al constructor
		function __construct(){
			parent::__construct();
			$this->load->model("Usuario_model","usuario");
			$this->load->model("Utiles_model","utiles");
			$this->load->library("session");
		}

		function prueba_get(){
			$a = $this->get("a");
			print_r($a);
		}

		function validarUsuario_post(){
			try{

				$profile = '';
				$auth_gdi = false;
				$userdata = array();
				$semestre = array();
				$username = $this->post('username');
				$str = $this->post('password');

				// $password = $this->decodePass($this->decodePass($str));
				$password = $str;
				// $user_obj = $this->autenticar($username, $password);
				$user = $this->userByRut($username);
				if(isset($user["fullname"])){
					$version = $this->utiles->get_version();
					$periodo = $this->utiles->get_periodo();
					$usuario = $this->usuario->get_usuario($username);
					if(isset($usuario[0]["RUT"])){
						$privilegios = $this->utiles->get_privilegio($usuario[0]["PRIVILEGIOS"]);
						$userdata = array(
							"username" => $user["fullname"],
							"correo" => $user["correo"],
							"mensaje" => $user["mensaje"],
							"profile" => $usuario[0]["JEFE"],
							"profile_base" => $usuario[0]["PRIVILEGIOS"],
							"result" => $user["result"],
							"rut" => $user["rut"],
							"logged_in_gdi" => true,
							"periodo" => $periodo[0]["PERIODO"],
							"version" => $version[0]["VERSION"],
							"privilegios" => $privilegios[0]
						);
						$auth_gdi = true;
					}else{
						$userdata = array(
							'result' => false,
							'mensaje' => 'No tiene autorización para ingresar al sistema'
						);
					}
				}else{
					$userdata = array(
						'result' => false,
						'mensaje' => 'No ha logrado conectarse al sistema. Verifique sus datos de ingreso o contactese con Soporte DI'
					);
				}
				$this->session->set_userdata($userdata);
				$data = array('auth_gdi' => $auth_gdi, 'userdata'=> $userdata);
				$this->response($data, 200);
			}catch(Exception $e){
				$this->response(array('error'=>$e->getMessage()), $e->getCode());
			}
		}

		function cerrarSesion_get(){
			$this->session->sess_destroy();
			$this->response(true, 200);
		}

		function restaurarSesion_post(){
			$username = $this->post("rut");
			// $conectado = $this->usuario->get_conectado($username);
			// if (intval($conectado[0]["CONECTADO"]) != 0) {
			$user_obj = $this->userByRut($username);
			// print_r($user_obj);
			$data = $this->iniciar($user_obj[0], $username);
			$this->response($data, 200);
			// }else{
				// $this->session->sess_destroy();
				// $this->response(array("error" => "Su sesión se ha cerrado."), 200);
			// }
		}

		function iniciar($user_obj, $username){
			$user = $this->userByRut($username);
			if(isset($user["fullname"])){
				$version = $this->utiles->get_version();
				$periodo = $this->utiles->get_periodo();
				$usuario = $this->usuario->get_usuario($username);
				if(isset($usuario[0]["RUT"])){
					$privilegios = $this->utiles->get_privilegio($usuario[0]["PRIVILEGIOS"]);
					$userdata = array(
						"username" => $user["fullname"],
						"correo" => $user["correo"],
						"mensaje" => $user["mensaje"],
						"profile" => $usuario[0]["JEFE"],
						"profile_base" => $usuario[0]["PRIVILEGIOS"],
						"result" => $user["result"],
						"rut" => $user["rut"],
						"logged_in_gdi" => true,
						"periodo" => $periodo[0]["PERIODO"],
						"version" => $version[0]["VERSION"],
						"privilegios" => $privilegios[0]
					);
					$auth_gdi = true;
				}else{
					$userdata = array(
						'result' => false,
						'mensaje' => 'No tiene autorización para ingresar al sistema'
					);
				}
			}else{
				$userdata = array(
					'result' => false,
					'mensaje' => 'No ha logrado conectarse al sistema. Verifique sus datos de ingreso o contactese con Soporte DI'
				);
			}
			$this->session->set_userdata($userdata);
			return array('auth_gdi' => $auth_gdi, 'userdata'=> $userdata);
		}


	    function obtenerDatosUsuario_get(){
			$version = $this->utiles->get_version();
			$user_data = array(
				"username"=> $this->session->userdata("username"),
				"correo"=> $this->session->userdata("correo"),
				"mensaje" => $this->session->userdata("mensaje"),
				"profile" => $this->session->userdata("profile"),
				"profile_base" => $this->session->userdata("profile_base"),
				"cargo" => $this->session->userdata("cargo"),
				"result" => $this->session->userdata("result"),
				"rut" => $this->session->userdata("rut"),
				"logged_in_gdi" => $this->session->userdata("logged_in_gdi"),
				"periodo" => $this->session->userdata("periodo"),
				"version" => $version[0]["VERSION"]
			);
			$this->response($user_data,200);
		}


		function decodePass($str){
			$str = (base64_decode($str));
			$key = 146;
			$pos = 0;
			$ostr = '';
			while ($pos < strlen($str)) {
				$ostr .= chr( $key ^ ord(substr($str,$pos,1)) );
				$pos ++;
			}
			return $ostr;
		}
		function autenticar($username, $password) {
			//cargamos el archivo de configuracion de ldap
			$this->load->config('ldap');
			//creamos el objeto que retornaremos
			$obj_usuario = array();

			//si el usuario o contraseña estan vacios, se devuelve un mensaje
			if($username == '' || $password == ''){
				$obj_usuario['mensaje'] = "Usuario y password son requeridos";
				$obj_usuario['result'] = false;

				return $obj_usuario;
			}else{
				//recuperamos los datos de configuracion
				$ldap_host = $this->config->item('ldap_host');
				$base_dn = $this->config->item('base_dn');
				$ldap_usr_dom = $this->config->item('ldap_user_domain');
				$ldap_grupo = $this->config->item('ldap_grupo');

				//creamos la conexion de ldap
				$ldap = ldap_connect($ldap_host);
				ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION,3);
				ldap_set_option($ldap, LDAP_OPT_REFERRALS,0);
				//validamos si el usuario existe en active directory
				$ldapBind = @ldap_bind($ldap, $username.$ldap_usr_dom, $password);
				//si el usuario no existe retornamos mensaje
				if(!$ldapBind){
					$obj_usuario['mensaje'] = "Usuario y/o password invalidos";
					$obj_usuario['result'] = false;

					ldap_unbind($ldap);
					return $obj_usuario;
				}else{
					$filter = "(&(objectClass=user) (samaccountname=".$username.") ".$ldap_grupo.")";
					//buscamos el usuario en el grupo de active directory
					$sr = ldap_search($ldap, $base_dn, $filter);
					//devolvemos los datos del usuario
					$usu = ldap_get_entries($ldap, $sr);
					if ($usu["count"] > 0){
						//guardamos los datos en el aray que se retorna
						if($usu[0]["useraccountcontrol"][0] == 66048 || $usu[0]["useraccountcontrol"][0] == 512){
							$obj_usuario['fullname'] = isset($usu[0]["cn"][0]) ? $usu[0]["cn"][0] : "";
							$obj_usuario['apellidos'] = isset($usu[0]["sn"][0]) ? $usu[0]["sn"][0] : "";
							$obj_usuario['descripcion'] = isset($usu[0]["description"][0]) ? $usu[0]["description"][0] : "";
							$obj_usuario['telefono'] = isset($usu[0]["telephonenumber"][0]) ? $usu[0]["telephonenumber"][0] : "";
							$obj_usuario['correo'] = isset($usu[0]["mail"][0]) ? $usu[0]["mail"][0] : "";
							$obj_usuario['rut'] = isset($usu[0]["samaccountname"][0]) ? $usu[0]["samaccountname"][0] : "";
							$obj_usuario['mensaje'] = "Usuario validado correctamente";
							$obj_usuario['result'] = true;

							ldap_unbind($ldap);
							return $obj_usuario;
						}else{
							$obj_usuario['mensaje'] = "Usuario desabilitado, por favor comuniquese con el departamento de informática";
							$obj_usuario['result'] = false;

							ldap_unbind($ldap);
							return $obj_usuario;
						}
					}else{
						$obj_usuario['mensaje'] = "No se pudo obtener el usuario";
						$obj_usuario['result'] = false;

						ldap_unbind($ldap);
						return $obj_usuario;
					}
				}
			}
		}
		function userByRut($username) {
			//cargamos el archivo de configuracion de ldap
			$this->load->config('ldap');
			//creamos el objeto que retornaremos
			$obj_usuario = array();

			//si el usuario o contraseña estan vacios, se devuelve un mensaje
			if($username == ''){
				$obj_usuario['mensaje'] = "Usuario y password son requeridos";
				$obj_usuario['result'] = false;

				return $obj_usuario;
			}else{
				//recuperamos los datos de configuracion
				$ldap_host = $this->config->item('ldap_host');
				$base_dn = $this->config->item('base_dn');
				$ldap_usr_dom = $this->config->item('ldap_user_domain');
				$ldap_grupo = $this->config->item('ldap_grupo');

				//creamos la conexion de ldap
				$ldap = ldap_connect($ldap_host);
				ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION,3);
				ldap_set_option($ldap, LDAP_OPT_REFERRALS,0);
				//validamos si el usuario existe en active directory
				$ldapBind = @ldap_bind($ldap, 'buscador'.$ldap_usr_dom, 'c@t4l3j0');
				//si el usuario no existe retornamos mensaje
				if(!$ldapBind){
					$obj_usuario['mensaje'] = "Usuario y/o password invalidos";
					$obj_usuario['result'] = false;

					ldap_unbind($ldap);
					return $obj_usuario;
				}else{
					$filter = "(&(objectClass=user) (samaccountname=".$username.") ".$ldap_grupo.")";
					//buscamos el usuario en el grupo de active directory
					$sr = ldap_search($ldap, $base_dn, $filter);
					//devolvemos los datos del usuario
					$usu = ldap_get_entries($ldap, $sr);
					if ($usu["count"] > 0){
						//guardamos los datos en el aray que se retorna
						// if($usu[0]["useraccountcontrol"][0] == 66048 || $usu[0]["useraccountcontrol"][0] == 512){
							$mail = $this->identificarMail($usu[0]);
							$obj_usuario['fullname'] = isset($usu[0]["cn"][0]) ? $usu[0]["cn"][0] : "";
							$obj_usuario['apellidos'] = isset($usu[0]["sn"][0]) ? $usu[0]["sn"][0] : "";
							$obj_usuario['descripcion'] = isset($usu[0]["description"][0]) ? $usu[0]["description"][0] : "";
							$obj_usuario['telefono'] = isset($usu[0]["telephonenumber"][0]) ? $usu[0]["telephonenumber"][0] : "";
							$obj_usuario['correo'] = $mail; //isset($usu[0]["mail"][0]) ? $usu[0]["mail"][0] : "";
							$obj_usuario['rut'] = isset($usu[0]["samaccountname"][0]) ? $usu[0]["samaccountname"][0] : "";
							$obj_usuario['mensaje'] = "Usuario validado correctamente";
							$obj_usuario['result'] = true;

							ldap_unbind($ldap);
							return $obj_usuario;
						// }else{
						//   $obj_usuario['mensaje'] = "Usuario desabilitado, por favor comuniquese con el departamento de informática";
						//   $obj_usuario['result'] = false;

						//   ldap_unbind($ldap);
						//   return $obj_usuario;
						// }
					}else{
						$obj_usuario['mensaje'] = "No se pudo obtener el usuario";
						$obj_usuario['result'] = false;

						ldap_unbind($ldap);
						return $obj_usuario;
					}
				}
			}
		}
		private function identificarMail($usuario){
			$mail = "";
			if(isset($usuario["mail"][0])){
				if (isset($usuario["proxyaddresses"][0])) {
					if ($usuario["mail"][0] != $usuario["proxyaddresses"][0] && strpos($usuario["mail"][0], "alu.ucm") != -1) {
						$mail =  $usuario["proxyaddresses"][0];
					}else{
						 $mail =  $usuario["mail"][0];
					}
				}else{
					$mail =  $usuario["mail"][0];
				}
			}else{
				$mail = "";
			}
			return $mail;
		}

		private function crearAnexo($telefono){
			if($telefono == " "){
				return "-";
			}else{
				$fono = explode("-", $telefono);

				if($fono[0] == "75"){
					return "5".substr($fono[1], -3);
				}else{
					if($fono[0] == "71" && substr($fono[1], 0, 4) == "2413"){
						return "1".substr($fono[1], -3);
					}else if($fono[0] == "71" && substr($fono[1], 0, 4) == "2203"){
						return "1".substr($fono[1], -3);
					}else if($fono[0] == "71" && substr($fono[1], 0, 3) == "263"){
						return substr($fono[1], -4);
					}else{
						return $telefono;
					}
				}
			}
		}
	}
?>