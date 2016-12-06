<?php

namespace App\Http\Controllers;

use Hash;
use Keygen;
use App\Models\User;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    protected function generateNumericKey()
    {
        // prefixes the key with a random integer between 1 - 9 (inclusive)
        return Keygen::numeric(7)->prefix(mt_rand(1, 9))->generate(true);
    }

    protected function generateID()
    {
        $id = $this->generateNumericKey();

        // Ensure ID does not exist
        // Generate new one if ID already exists
        while (User::whereId($id)->count() > 0) {
            $id = $this->generateNumericKey();
        }

        return $id;
    }

    protected function generateCode()
    {
        return Keygen::bytes()->generate(
            function($key) {
                // Generate a random numeric key
                $random = Keygen::numeric()->generate();

                // Manipulate the random bytes with the numeric key
                return substr(md5($key . $random . strrev($key)), mt_rand(0,8), 20);
            },
            function($key) {
                // Add a (-) after every fourth character in the key
                return join('-', str_split($key, 4));
            },
            'strtoupper'
        );
    }

    public function showAllUsers(Request $request)
    {
        // Return a collection of all user records
        return User::all();
    }
    
    public function createNewUser(Request $request)
    {
        $user = new User;

        // Generate unique ID
        $user->id = $this->generateID();

        // Generate code for user
        $user->code = $this->generateCode();

        // Collect data from request input
        $user->firstname = $request->input('firstname');
        $user->lastname = $request->input('lastname');
        $user->email = $request->input('email');

        $password = $request->input('password');

        // Generate random base64-encoded token for password salt
        $salt = Keygen::token(64)->generate();

        $user->password_salt = $salt;

        // Create a password hash with user password and salt
        $user->password_hash = Hash::make($password . $salt . str_rot13($password));

        // Save the user record in the database
        $user->save();

        return $user;
    }
    
    public function showOneUser(Request $request, $id)
    {
        // Return a single user record by ID
        return User::find($id);
    }
    
    public function showRandomPassword(Request $request)
    {
        // Set length to 12 if not specified in request
        $length = (int) $request->input('length', 12);

        // Generate a random alphanumeric combination
        $password = Keygen::alphanum($length)->generate();

        return ['length' => $length, 'password' => $password];
    }
}
