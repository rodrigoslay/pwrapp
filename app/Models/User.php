<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'dark_mode',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function getRoleNames()
    {
        return $this->roles()->pluck('name')->toArray();
    }
    public function adminlte_image()
    {
        //return $this->avatar ? asset('pwrapp/storage/app/public/avatars/' . basename($this->avatar)) : 'https://picsum.photos/300/300';
    return $this->avatar? Storage::url($this->avatar) : 'https://picsum.photos/300/300';
    }

    public function adminlte_desc()
    {
             // Obtener el usuario autenticado
    $user = Auth::user();

    // Obtener los nombres de los roles del usuario
    $roles = collect($user->getRoleNames());

    // Convertir los roles a una cadena separada por comas (si hay más de un rol)
    return $roles->implode(', ');
    }

    public function adminlte_profile_url(){
        return 'profile/';
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
    // Relación muchos a muchos con WorkOrder
    public function workOrders()
    {
        return $this->belongsToMany(WorkOrder::class, 'service_work_order', 'mechanic_id', 'work_order_id');
    }
    // Relación para las órdenes de trabajo creadas por el usuario
    public function createdWorkOrders()
    {
        return $this->hasMany(WorkOrder::class, 'created_by');
    }

    // Relación para las órdenes de trabajo facturadas por el usuario
    public function facturadasWorkOrders()
    {
        return $this->hasMany(WorkOrder::class, 'created_by')->where('status', 'Facturado');
    }
}
