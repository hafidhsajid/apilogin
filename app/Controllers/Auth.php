<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Auth as model;
use CodeIgniter\Database\Query;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;


class Auth extends BaseController
{
	use ResponseTrait;

	public function __construct()
	{
		$this->session = session();
		$config         = new \Config\Encryption();
		$config->key = 'X4i7Z3Zca97XOT9gsePBzHkeAT6DW3KprcZmeICdO9J0CWwxV4TKOwkte9hBC9H1';
		$this->encrypter = \Config\Services::encrypter($config);
	}

	public function index()
	{
		return view('Auth/login');
	}


	public function logout()
	{
		$params = array('userid', 'name', 'level');
		// $this->session->unset_userdata($params);
		// $this->session->set_flashdata('flash', 'Berhasil Logout, Terimakasih Sudah Menggunakan layanan kami.');
		$this->session->destroy();
		redirect('admin');
	}
	public function regis()
	{
		$builder = new model();
		$nama = htmlspecialchars($this->request->getPost('nama'));
		$email = htmlspecialchars($this->request->getPost('email'));
		$password = htmlspecialchars($this->request->getPost('password'));
		$phone = htmlspecialchars($this->request->getPost('phone'));
		$alamat = htmlspecialchars($this->request->getPost('alamat'));

		$query = $builder->where('email', $email)->first();
		if ($query) {
			$data = [
				'status' => 500,
				'messages'=>'Sudah terdaftar'
			];
			$this->setResponseFormat('json')->fail('Error', 500, '', 'Sudah Daftar');
		} else {
			$enc = bin2hex($this->encrypter->encrypt($password));
			$data = array(
				'nama' 	=> $nama,
				'email' 	=> $email,
				'password' 	=> $enc,
				'phone' 	=> $phone,
				'alamat' 	=> $alamat,
				'level' 	=> 'User'
			);
			$data = array('response'=>'success', 'message'=>'Berhasil mendaftar');
			// $builder->insert($data);
			$this->respondCreated($data);
		}

		echo json_encode($data);

	}

	public function cekLogin()
	{
		$email = htmlspecialchars($this->request->getPost('email'));
		$password = htmlspecialchars($this->request->getPost('password'));
		$session = session();

		$builder = new model();
		$query = $builder->where('email', $email)->first();

		if ($query) {
			$enc = $this->encrypter->decrypt(hex2bin($query['password']));
			if ($password == $enc) {
				$params = array(
					'nama' => $query['nama'],
					'id_user' => $query['id_user'],
					'email' => $query['email'],
					'level' => $query['level']
				);
				$session->set($params);
				// $this->session->set_userdata($params);
				// $data = ['toast' => toast('success', 'Selamat Datang ' . $row['name'] . '!'), 'level' => $row['level']];
				$data = array('response' => 'success', 'message' => 'Selamat Datang');
				$this->respond($data);
			} else {
				// $data = ['toast' => toast('error', 'Password anda salah!'), 'level' => null];
				// $data = 'pass salah';
				$data = array('response' => 'error', 'message' => 'Password salah');
				$this->fail($data, 500);
			}
		} else {
			$data = array('response' => 'error', 'message' => 'User Tidak Ditemukan!');
			$this->fail($data,500);
		}
		echo json_encode($data);
	}
	public function setsession()
	{	
		$email = htmlspecialchars($this->request->getPost('email'));
		$password = htmlspecialchars($this->request->getPost('password'));
		$params = array(
			'email' => $email,
			'password' => $password
			// 'id_user' => $query['id_user'],
			// 'email' => $query['email'],
			// 'level' => $query['level']
		);
		$this->session->set($params);
	}
	public function getsession()
	{
		if ($this->session->get('email')){
			echo 'belumlogout';
		};
	}
}
