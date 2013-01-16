<?php

class loginController extends Controller
{
	public function index()
	{
		if(!empty($_POST['username']) && !empty($_POST['password']))
		{
			if(self::$user->logIn($_POST['username'], $_POST['password']))
			{
				header('Location: /');
				exit;
			}
		}

		$this->render();
	}
}