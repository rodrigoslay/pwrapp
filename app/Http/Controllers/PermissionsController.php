<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use DataTables;

class PermissionsController extends Controller
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
            return $this->getPermissions($request->role_id);
        }
        return view('users.permissions.index');
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
    public function store(Request $request)
    {
        // Validar nombre
        $this->validate($request, [
            'name' => 'required|unique:permissions,name'
        ]);
        $permission = Permission::create(["name" => strtolower(trim($request->name))]);
        if($permission)
        {
            toast('Permiso agregado exitosamente.', 'success');
            return view('users.permissions.index');
        }
        toast('Error al guardar el permiso', 'error');
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
    public function edit(Permission $permission)
    {
        return view('users.permissions.edit')->with(['permission' => $permission]);
    }

    /**
     * Actualizar el recurso especificado en el almacenamiento.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permission $permission)
    {
        $this->validate($request, [
            "name" => 'required|unique:permissions,name,' . $permission->id
        ]);

        if($permission->update($request->only('name')))
        {
            toast('Permiso actualizado exitosamente.', 'success');
            return view('users.permissions.index');
        }
        toast('Error al actualizar el permiso', 'error');
        return back()->withInput();
    }

    /**
     * Eliminar el recurso especificado del almacenamiento.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Permission $permission)
    {
        if($request->ajax() && $permission->delete())
        {
            return response(["message" => "Permiso eliminado exitosamente"], 200);
        }
        return response(["message" => "¡Error al eliminar los datos! Por favor, inténtelo de nuevo"], 201);
    }

    private function getPermissions($role_id)
    {
        $data = Permission::get();
        return DataTables::of($data, $role_id)
            ->addColumn('chkBox', function($row) use ($role_id){
                if($row->name == "dashboard")
                {
                    return "<input type='checkbox' name='permission[".$row->name."]' value=".$row->name." checked onclick='return false;'>";
                }else{
                    if($role_id != "")
                    {
                        $role = Role::where('id', $role_id)->first();
                        $rolePermissions = $role->permissions->pluck('name')->toArray();
                        if(in_array($row->name, $rolePermissions))
                        {
                            return "<input type='checkbox' name='permission[".$row->name."]' value=".$row->name." checked>";
                        }
                    }
                    return "<input type='checkbox' name='permission[".$row->name."]' value=".$row->name." class='permission'>";
                }
            })
            ->addColumn('action', function($row){
                $action = "";
                $action .= "<a class='btn btn-xs btn-warning' id='btnEdit' href='".route('users.permissions.edit', $row->id)."'><i class='fas fa-edit'></i></a>";
                $action .= " <button class='btn btn-xs btn-outline-danger' id='btnDel' data-id='".$row->id."'><i class='fas fa-trash'></i></button>";
                return $action;
            })
        ->rawColumns(['chkBox', 'action'])->make(true);
    }
}
