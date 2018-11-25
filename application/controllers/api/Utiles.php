<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Desarrollada por Pablo Sepúlveda Valenzuela  (NøName)*/
	require APPPATH.'/libraries/REST_Controller.php';
	// require APPPATH . '/libraries/Authentication.php';
	class Utiles extends REST_Controller {

		// llama al constructor
		function __construct(){
			parent::__construct();
			$this->load->library("session");
			// if (!$this->session->userdata("rut")) {
				// $this->response(array('error' => "Su sesión ha expirado o no ha iniciado sesión correctamente. Por favor vuelva a ingresar."), 200);
			// }else{
				// $this->load->model("Usuario_model","usuario");
				// $this->load->model("Utiles_model","utiles");
			// }
		}
		function guardarAlgo_post(){
			$userdata = array(
				"username" => $this->post("username")
			);
			$this->session->set_userdata($userdata);
			echo json_encode(array("result" => true));
		}
		function obtenerMenu_get(){
			// $rut = $this->session->userdata("rut");
			// $profile_base = $this->session->userdata("profile_base");
			// $query = $this->utiles->get_menu($profile_base, $rut);
			$query = array(array("ID_MENU" => "home", "NOMBRE" =>  "Inicio", "MOSTRAR" => 1), array("ID_MENU" => "otro", "NOMBRE" =>  "otro", "MOSTRAR" => 1));
			// $query = array("error" => "asdasdasdad");
			echo json_encode($query);
			// $this->responseData($query, "No se encontraron datos");
		}
		function getMarkets_get(){
			$content = file_get_contents("https://bittrex.com/api/v1.1/public/getmarketsummaries", true);
			// print_r($content);
			$floats = ["High", "Low", "Volume", "Last", "BaseVolume", "Bid", "Ask", "PrevDay"];
			$array = json_decode($content);
			foreach ($array->result as $k => $v) {
				foreach ($array->result[$k] as $key => $value) {
					if (in_array($key, $floats)) {
						$array->result[$k]->$key = (String) number_format($value, 8, ".", "");
					}
				}
			}
			$this->responseData($array, "Error");
		}
		function getMarket_get(){
			$market = $this->get("market");
			$content = file_get_contents("https://bittrex.com/api/v1.1/public/getticker?market=".$market, true);

			$floats = ["Last", "Bid", "Ask"];

			$array = json_decode($content);

			$array->result->Bid = (String) number_format($array->result->Bid, 8, ".", "");
			$array->result->Last = (String) number_format($array->result->Last, 8, ".", "");
			$array->result->Ask = (String) number_format($array->result->Ask, 8, ".", "");

			// $array->result->Bid = (String) '0.00002323';
			// $array->result->Last = (String) '0.00002323';
			// $array->result->Ask = (String) '0.00002323';
			
			$this->responseData($array, "Error");
		}
		function obtnerNotificaciones_post(){
			// $rut = $this->session->userdata("rut");
			// $periodo = $this->session->userdata("periodo");
			// $eventos = $this->eventos->get_cantidad_nuevos($rut, $periodo);
			// $autoeva = $this->evaluacion->get_cantidad_autoevaluacion($rut, $periodo);
			$query = array("registroEventos" => 2, "autoevaluacion" => 2);
			$this->responseData($query, "No se encontraron datos");
		}

		private function responseData($data, $error = ""){
			if(sizeof($data) == 0){
				$this->response(array('error' => $error), 200);
			}else{
				$this->response($data, 200);
			}
		}
	}
?>