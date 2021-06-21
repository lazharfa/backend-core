<?php
namespace App\Traits;

use Validator;

trait Helper {
	protected function isEmail($email)
	{
		$validator = Validator::make(['email' => $email],[
		  'email' => 'email'
		]);

		if($validator->passes()){
		  	return true;
		}

		return false;
	}

	protected function isPhoneNumber($phone)
	{
		$validator = Validator::make(['phone' => $phone],[
		  'phone' => 'regex:/^([0-9\s\-\+\(\)]*)$/|min:10'
		]);

		if($validator->passes()){
		  	return true;
		}

		return false;
	}

    protected function getContactType($contact)
    {
        if ($this->isPhoneNumber($contact)) {
            return 'phone';
        }

        if ($this->isEmail($contact)) {
            return 'email';
        }

        return null;
    }
    
}