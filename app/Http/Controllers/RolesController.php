<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use DataTables;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Contracts\DataTable;

class RolesController extends Controller
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
            return $this->getRoles();
        }
        return view('users.roles.index');
    }

    /**
     * Mostrar el formulario para crear un nuevo recurso.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permissions = Permission::get();
        return view('users.roles.create')->with(['Permissions'=> $permissions]);
    }

    /**
     * Almacenar un recurso recién creado en el almacenamiento.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validar nombre
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'permission' => 'required'
        ]);
        $role = Role::create(['name' => strtolower(trim($request->name))]);
        $role->syncPermissions($request->permission);
        if($role)
        {
            toast('Nuevo rol agregado exitosamente.','success');
            return view('users.roles.index');
        }
        toast('Error al guardar el rol','error');
        return back()->withInput();
    }

    /**
     * Mostrar el recurso especificado.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Role $role)
    {
        if($request->ajax())
        {
            return $this->getRolesPermissions($role);
        }
        return view('users.roles.show')->with(['role' => $role]);
    }

    /**
     * Mostrar el formulario para editar el recurso especificado.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        return view('users.roles.edit')->with(['role' => $role]);
    }

    /**
     * Actualizar el recurso especificado en el almacenamiento.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Role $role, Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
        ]);
        $role->update($request->only('name'));
        $role->syncPermissions($request->permission);
        if($role)
        {
            toast('Rol actualizado exitosamente.','success');
            return view('users.roles.index');
        }
        toast('Error al actualizar el rol','error');
        return back()->withInput();
    }

    /**
     * Eliminar el recurso especificado del almacenamiento.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Role $role)
    {
        if($request->ajax() && $role->delete())
        {
            return response(["message" => "Rol eliminado exitosamente"], 200);
        }
        return response(["message" => "¡Error al eliminar los datos! Por favor, inténtelo de nuevo"], 201);
    }

    private function getRoles()
    {
        $data = Role::withCount(['users', 'permissions'])->get();
        return DataTables::of($data)
                ->addColumn('name', function($row){
                    return ucfirst($row->name);
                })
                ->addColumn('users_count', function($row){
                    return $row->users_count;
                })
                ->addColumn('permissions_count', function($row){
                    return $row->permissions_count;
                })
                ->addColumn('action', function($row){
                    $action = "";
                    $action.="<a class='btn btn-xs btn-success' id='btnShow' href='".route('users.roles.show', $row->id)."'><i class='fas fa-eye'></i></a> ";
                    $action.="<a class='btn btn-xs btn-warning' id='btnEdit' href='".route('users.roles.edit', $row->id)."'><i class='fas fa-edit'></i></a>";
                    $action.=" <button class='btn btn-xs btn-outline-danger' id='btnDel' data-id='".$row->id."'><i class='fas fa-trash'></i></button>";
                    return $action;
                })
                ->make('true');
    }

    private function getRolesPermissions($role)
    {
        $permissions = $role->permissions;
        return DataTables::of($permissions)->make('true');
    }
}
