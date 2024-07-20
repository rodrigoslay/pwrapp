<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UsersController extends Controller
{
    /**
     * Mostrar una lista del recurso.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax())
        {
            return $this->getUsers();
        }
        return view('users.index')->with(["roles" => Role::get()]);
    }

    /**
     * Mostrar el formulario para crear un nuevo recurso.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Almacenar un recurso recién creado en el almacenamiento.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $user)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email:rfc,dns|unique:users,email'
        ]);
        if($request->has('roles'))
        {
            $user->create($request->all())->roles()->sync($request->roles);
        }else{
            $user->create($request->all());
        }
        if($user)
        {
            toast('Nuevo usuario creado con éxito.','success');
            return Redirect::to('users');
        }
        toast('Error al crear un nuevo usuario','error');
        return back()->withInput();
    }

    /**
     * Mostrar el recurso especificado.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Mostrar el formulario para editar el recurso especificado.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return view('users.edit', [
            "user" => $user,
            "userRole" => $user->roles->pluck('name')->toArray(),
            "roles" => Role::latest()->get()
        ]);
    }

    /**
     * Actualizar el recurso especificado en el almacenamiento.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email:rfc,dns|unique:users,email,'.$user->id,
        ]);

        $user->update($request->all());
        $user->roles()->sync($request->input('roles'));

        if($user)
        {
            toast('Usuario actualizado con éxito.','success');
            return Redirect::to('users');
        }
        toast('Error al actualizar el usuario','error');
        return back()->withInput();
    }

    /**
     * Eliminar el recurso especificado del almacenamiento.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, User $user)
    {
        if($request->ajax() && $user->delete())
        {
            return response(["message" => "Usuario eliminado con éxito"], 200);
        }
        return response(["message" => "¡Error al eliminar los datos! Por favor, inténtelo de nuevo"], 201);
    }

    private function getUsers()
    {
        $data = User::with('roles')->get();
        return DataTables::of($data)
                ->addColumn('name', function($row){
                    return ucfirst($row->name);
                })
                ->addColumn('date', function($row){
                    return Carbon::parse($row->created_at)->format('d M, Y h:i:s A');
                })
                ->addColumn('roles', function($row){
                    $role = "";
                    if($row->roles != null)
                    {
                        foreach($row->roles as $next)
                        {
                            $role.='<span class="badge badge-primary">'.ucfirst($next->name).'</span> ';
                        }
                    }
                    return $role;
                })
                ->addColumn('action', function($row){
                    $action = "";
                    $action.="<a class='btn btn-xs btn-warning' id='btnEdit' href='".route('users.edit', $row->id)."'><i class='fas fa-edit'></i></a>";
                    $action.=" <button class='btn btn-xs btn-outline-danger' id='btnDel' data-id='".$row->id."'><i class='fas fa-trash'></i></button>";
                    return $action;
                })
                ->rawColumns(['name', 'date','roles', 'action'])->make('true');
    }
}
