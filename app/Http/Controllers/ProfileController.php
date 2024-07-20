<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
{
    $user = Auth::user();

    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        'password' => 'nullable|string|min:8|confirmed',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ], [
        'name.required' => 'El nombre es obligatorio.',
        'name.string' => 'El nombre debe ser una cadena de texto.',
        'name.max' => 'El nombre no puede tener más de 255 caracteres.',
        'email.required' => 'El correo electrónico es obligatorio.',
        'email.string' => 'El correo electrónico debe ser una cadena de texto.',
        'email.email' => 'El correo electrónico debe ser una dirección de correo válida.',
        'email.max' => 'El correo electrónico no puede tener más de 255 caracteres.',
        'email.unique' => 'El correo electrónico ya está en uso.',
        'password.nullable' => 'La contraseña puede estar vacía.',
        'password.string' => 'La contraseña debe ser una cadena de texto.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        'avatar.nullable' => 'El avatar puede estar vacío.',
        'avatar.image' => 'El avatar debe ser una imagen.',
        'avatar.mimes' => 'El avatar debe ser un archivo de tipo: jpeg, png, jpg, gif, svg.',
        'avatar.max' => 'El avatar no puede ser mayor de 2048 kilobytes.',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    $input = $request->only(['name', 'email']);
    if ($request->filled('password')) {
        $input['password'] = Hash::make($request->password);
    }

    DB::beginTransaction();
    try {
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::delete($user->avatar);
            }
            $input['avatar'] = $request->file('avatar')->store('public/avatars');
        }

        $input['dark_mode'] = $request->has('dark_mode');
        //dd($input);
        $user->update($input);

        DB::commit();
        return redirect()->route('profile.edit')->with('success', 'Perfil actualizado exitosamente.');
    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->route('profile.edit')->with('error', 'Error al actualizar el perfil: ' . $e->getMessage());
    }
}

}
