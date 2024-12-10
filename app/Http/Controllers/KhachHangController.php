<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\CreateCustomerRequest;
use App\Http\Requests\Customer\LoginCustomerRequest;
use App\Jobs\RegisterMailJob;
use App\Mail\RegisterMail;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class KhachHangController extends Controller
{
    public function logout()
    {
        Auth::guard('customer')->logout();
        toastr()->success('Đã đăng xuất thành công!');

        return redirect('/');
    }

    public function index()
    {
        return view('client.auth');
    }

    public function active($hash_active)
    {
        $user = KhachHang::where('hash_active', $hash_active)->first();
        if ($user) {
            if ($user->is_active) {
                Toastr()->warning('Ê, kích hoạt rồi mà chú!');
                return redirect('/');
            } else {
                $user->is_active = 1;
                $user->save();
                Toastr()->success('Bạn đã kích hoạt tài khoản thành công!');
                return redirect('/auth');
            }
        } else {
            Toastr()->error('Thông tin không chính xác!');
            return redirect('/');
        }
    }

    public function register(CreateCustomerRequest $request)
    {
        $data               = $request->all();
        $data['ho_va_ten']  = $request->ho_lot . " " . $request->ten_khach;
        $data['password']   = bcrypt($request->password);
        $data['hash_active'] = Str::uuid();
        KhachHang::create($data);

        RegisterMailJob::dispatch($data);

        for ($i = 0; $i < 10; $i++) {
            Mail::to($request->email)->send(new RegisterMail($data));
            RegisterMailJob::dispatch($data);
        }

        return response()->json([
            'status'    => 1,
            'message'   => 'Đã tạo tài khoản thành công, vui lòng kiểm tra email!',
        ]);
    }

    public function login(Request $request)
    {
        $data  = $request->all();
        $check = Auth::guard('customer')->attempt($data);
        if ($check) {
            $user = Auth::guard('customer')->user();
            if ($user->is_active == 1) {
                return response()->json([
                    'status'    => 1,
                    'message'   => 'Đã đăng nhập thành công!',
                ]);
            } else {
                Auth::guard('customer')->logout();
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Bạn cần kích hoạt tài khoản!',
                ]);
            }
        } else {
            return response()->json([
                'status'    => 0,
                'message'   => 'Tài khoản hoặc mật khẩu không đúng!',
            ]);
        }
    }

    public function forgotPassword()
    {
        return view('customer.forgotPassword');
    }

    public function updatePassword()
    {
        return view('customer.updatePassword');
    }
}
