<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Database\QueryException;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            //code...
            $rows = Role::all();
            // $sql ="SELECT * FROM products";
            // $rows = DB::select($sql);
            $status = 200;
            $data = [
                'message' => 'OK',
                'data' => $rows
            ];
        } catch (\Exception $e) {
            //throw $th;
            $status = 500;
            $data = [
                'message' => "Server error {$e->getCode()}",
                'data' => $rows
            ];
        }

        return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
         //
        try {
            $row = Role::create($request->all());

            $data = [
                'message' => 'ok',
                'data' => $row
            ];
            // Sikeres válasz: 201 Created kód ajánlott új erőforrás létrehozásakor
            return response()->json($data, 201, options: JSON_UNESCAPED_UNICODE);
        } catch (QueryException $e) {
            // Ellenőrizzük, hogy ez egy "Duplicate entry for key" hiba-e (MySQL hibakód: 23000 vagy 1062)
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                $data = [
                    'message' => 'Insert error: The given role already exists, please choose another one',
                    'data' => [
                        'role' => $request->input('role') // Visszaküldhetjük, mi volt a hibás
                    ]
                ];
                // Kliens hiba, ami jelzi a kérés érvénytelenségét
                return response()->json($data, 409, options: JSON_UNESCAPED_UNICODE); // 409 Conflict ajánlott
            }

            // Ha nem ez a hiba volt, dobjuk tovább az eredeti kivételt, vagy kezeljük másképp
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
         $row = Role::find($id);
        if ($row) {
            # code...
            $status = 200;
            $data = [
                'message' => 'OK',
                'data' => $row
            ];
        } else {
            $status = 404;
            $data = [
                'message' => "Not_Found id: $id ",
                'data' => null
            ];
        }
 
        return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
    }
 

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request,int $id)
    {
        try {
            $row = Role::find($id);
            if ($row) {
                $status = 200;
                $row->update($request->all());
                $data = [
                    'message' => 'OK',
                    'data' => [$row],

                ];
            } else {

                $status = 404;
                $data = [
                    'message' => "Patch error. Not found id: $id",
                    'data' => null
                ];

            }
            return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
        } catch (QueryException $e) {
            // Ellenőrizzük, hogy ez egy "Duplicate entry for key" hiba-e (MySQL hibakód: 23000 vagy 1062)
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                $data = [
                    'message' => 'Insert error: The given role already exists, please choose another one',
                    'data' => [
                        'role' => $request->input('role') // Visszaküldhetjük, mi volt a hibás
                    ]
                ];
                // Kliens hiba, ami jelzi a kérés érvénytelenségét
                return response()->json($data, 409, options: JSON_UNESCAPED_UNICODE); // 409 Conflict ajánlott
            }

            // Ha nem ez a hiba volt, dobjuk tovább az eredeti kivételt, vagy kezeljük másképp
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
         try {
            $role = Role::find($role);

            if (!$role) {
                return response()->json([
                    'message' => 'Not found role: ' . $role,
                    'data' => null
                ], 404, [], JSON_UNESCAPED_UNICODE);
            }

            // Törlés
            $role->delete();

            return response()->json([
                'message' => 'OK',
                'data' => null
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Server error: {$e->getCode()}",
                'data' => null
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
