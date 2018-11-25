<?php
defined("BASEPATH") OR exit("No direct script access allowed");

class Home extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->helper("url");
		$this->load->library("session");
		// $this->load->model("Utiles_model","utiles");
    }
	public function index(){
		// header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Origin: *');
		header ("Access-Control-Expose-Headers: Content-Length, X-JSON");
		header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
		header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
		// $profile="";
		// $profile_base="";
		// $sede = "";
		// $auth_gdi = false;
		// $user_data = array();
		// $semestre = array();
		// $data = array();
		// $version = $this->utiles->get_version();
		// if ($this->session->userdata("logged_in_gdi") == TRUE ) {
		// 	$auth_gdi = true;
		// 	$user_data = array(
		// 		"username"=> $this->session->userdata("username"),
		// 		"correo"=> $this->session->userdata("correo"),
		// 		"mensaje" => $this->session->userdata("mensaje"),
		// 		"profile" => $this->session->userdata("profile"),
		// 		"profile_base" => $this->session->userdata("profile_base"),
		// 		"cargo" => $this->session->userdata("cargo"),
		// 		"result" => $this->session->userdata("result"),
		// 		"rut" => $this->session->userdata("rut"),
		// 		"logged_in_gdi" => $this->session->userdata("logged_in_gdi"),
		// 		"periodo" => $this->session->userdata("periodo"),
		// 		"version" => $version[0]["VERSION"]
		// 	);

		// 	$profile = $this->session->userdata("profile");
		// 	$profile_base = $this->session->userdata("profile_base");
		// }

		// $data = array("auth_gdi" => $auth_gdi, "profile" => $profile, "profile_base" => $profile_base, "userdata"=> $user_data, "periodo" => 2017);
		$this->load->view("home");
	}
}
