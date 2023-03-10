<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'size:10', 'regex:/^0[^6421][0-9]{8}$/', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'active_token' => str::random(40),
        ]);
    }

    public function activation($token) {
        $user = User::where('active_token', $token)->first();
        if(isset($user)) {
            if(!$user->active) {
                $user->active = 1;
                $user->save();
                return redirect()->route('home_page')->with(['alert' => [
                    'type' => 'success',
                    'title' => 'K??ch ho???t t??i kho???n th??nh c??ng',
                    'content' => 'Ch??c m???ng b???n ???? k??ch ho???t t??i kho???n th??nh c??ng. B???n c?? th??? ?????ng nh???p ngay b??y gi???.'
                ]]);
            }
            else {
                return redirect()->route('home_page')->with(['alert' => [
                    'type' => 'warning',
                    'title' => 'T??i kho???n ???? ???????c k??ch ho???t',
                    'content' => 'T??i kho???n ???? ???????c k??ch ho???t t??? tr?????c. B???n c?? th??? ?????ng nh???p ng??y b??y gi???.'
                ]]);
            }
        } else {
            return redirect()->route('home_page')->with(['alert' => [
                'type' => 'error',
                'title' => 'K??ch ho???t t??i kho???n kh??ng th??nh c??ng',
                'content' => 'M?? k??ch ho???t kh??ng ????ng. vui l??ng ki???m tra l???i email ????ng k??!'
            ]]);
        }
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        $user->sendActiveAccountNotification($user->active_token);

        $this->guard()->logout();

        return redirect()->route('home_page')->with(['alert' => [
            'type' => 'success',
            'title' => '????ng k?? t??i kho???n th??nh c??ng',
            'content' => 'Vui l??ng ki???m tra email ????ng k?? ????? k??ch ho???t t??i kho???n.'
        ]]);
    }
}
