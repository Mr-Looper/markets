<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Desarrollada por Pablo Sepúlveda Valenzuela  (NøName)*/
	require APPPATH.'/libraries/REST_Controller.php';
	// require APPPATH . '/libraries/Authentication.php';
	class Reportes extends REST_Controller {

		// llama al constructor
		function __construct(){
			parent::__construct();
			$this->load->library("session");
			if (!$this->session->userdata("rut")) {
				$this->response(array('error' => "Su sesión ha expirado o no ha iniciado sesión correctamente. Por favor vuelva a ingresar."), 200);
			}else{
				$this->load->model("Usuario_model","usuario");
				$this->load->model("Utiles_model","utiles");
				$this->load->model("Eventos_model","eventos");
				$this->load->model("Evaluacion_model","evaluacion");
				$this->load->model("Funcionario_model","funcionario");
			}
		}

		function imprimirEncuesta_post(){
			$rut = $this->post("rut");
			// $periodo = $this->post("periodo");
			$periodo = $this->session->userdata("periodo");
      		header("Set-Cookie: fileDownload=true; path=/");
			$tipo = $this->post("tipo");
			$query = $this->evaluacion->get_evaluacion($rut, $periodo, $tipo);
			$metas = $this->evaluacion->get_metas($rut,$periodo);
			$metas_eva = $this->evaluacion->get_metas_evaluar($rut,$periodo);
			$promedios = $this->evaluacion->get_promedios_info($rut,$periodo);
			$observaciones = $this->evaluacion->get_observaciones($rut,$periodo);
				// print_r("<pre>");
				// print_r($observaciones[0]);
				// print_r("</pre>");
			if (sizeof($metas_eva) > 0) {
				$metas_eva = array($metas_eva);
			}
			$nivel = "";
			$comp = 0;
			foreach ($query as $k => $v) {
				$tipoCompetencia = $v["NIVEL"] == "0" ? "generales" : "especificas";
				if ($comp != $v["IDC"]) {
					$array[$tipoCompetencia][$v["IDC"]] = array();
				}
				array_push($array[$tipoCompetencia][$v["IDC"]], $v);
				$comp = $v["IDC"];
			}

			$query = $this->funcionario->get_funcionario($rut, $periodo, 0);
			$datos = $this->funcionario->userByRut($rut);
			$query[0]["CORREO"] = $datos["correo"];
			
			$bool = true;
			// $headerEva = $this->load->view("/evaluacion/headerEva", array("titulo_evaluacion" => "Evaluación Final"), $bool);
			$datosFuncionario = $this->load->view("/evaluacion/datos", array("funcionario" => $query[0]), $bool);
			$vistaGenerales = $this->load->view("/evaluacion/competencias", array("lista" => $array["generales"], "texto_titulo" => "Competencias Institucionales"), $bool);
			$vistaEspecificas = $this->load->view("/evaluacion/competencias", array("lista" => $array["especificas"], "texto_titulo" => "Competencias Específicas"), $bool);
			$footerEva = $this->load->view("/evaluacion/footerEva", array(), $bool);
			if ($tipo == 1) {
				// print_r("<pre>");
				// print_r($metas);
				// print_r("</pre>");
				$array_metas = array("metas" => $metas, "metas_eva" => $metas_eva);
				$vistaMetasEva = $this->load->view("/evaluacion/competencias", array("lista" => $metas_eva, "texto_titulo" => "Metas"), $bool);
				$vistaMetas = $this->load->view("/evaluacion/metas", array("metas" => $metas, "tipo" => $tipo), $bool);
			}else{
				$vistaMetasEva = "";
				$vistaMetas = "";
			}
			$vistaPromedios = $this->load->view("/evaluacion/promedios", array("promedios" => $promedios[0], "tipo" => $tipo, "observaciones" => $observaciones[0]), $bool);

			if($bool){
				$this->load->library("MPDF/Mpdf");

				// $mpdf = new mPDF('utf-8','Letter');
				// $mpdf->showImageErrors = true;


				$mpdf = new mPDF('utf-8','Letter',0,0,10.1,10.1,15,29,11.2,11.2, 'P');
				//se carga el estilo CSS
				$stylesheet = file_get_contents('assets/css/materialize.min.css');
				$stylesheet2 = file_get_contents("assets/css/pdf.css");
				// $mpdf->WriteHTML($stylesheet,1);
				$mpdf->WriteHTML($stylesheet2,1);

				//Carga el header
				$variable = "<img src=\"assets/img/LOGO_UCM2.gif\" style=\"float:left; left: 0px; top: 0px; max-width: 60px; max-height: 180px\">
				<div style=\"position:absolute\">
				<h4 style=\"margin-left:15px\">
				</h4>
				</div>
				<br>
				<br>";
				$header = $this->load->view("/evaluacion/header", array("titulo" => "Evaluación Final", "periodo" => $periodo), $bool);
				$mpdf->SetHTMLHeader($header);
				$mpdf->addpage("P");
				//Carga el contenido del HTML
				$mpdf->SetHTMLHeader('');
				$mpdf->WriteHTML($headerEva.$datosFuncionario.$vistaGenerales,2);
				$mpdf->AddPage(); 
				$mpdf->SetHTMLHeader('');
				$mpdf->WriteHTML($vistaEspecificas,3);
				$mpdf->AddPage(); 
				$mpdf->SetHTMLHeader('');
				$mpdf->WriteHTML($vistaMetasEva.$vistaMetas.$vistaPromedios.$footerEva,3);
				$mpdf->SetHTMLFooter('<h6 style= "text-align:center;font-family: Cambria, Georgia, serif; color: #9e9e9e">UNIVERSIDAD CATÓLICA DEL MAULE</h6>
				<h6 style= "text-align:center;font-family: Cambria, Georgia, serif; color: #9e9e9e">Avenida San Miguel N°3605 - Talca</h6>');
				$mpdf->debug = true;
				$mpdf->Output('carga_'.$rut.'_'.$periodo.'.pdf','D');
				// $this->response(array('respuesta' => 'carga_'.$rut.'_'.$periodo), 200);
				return true;
			}
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