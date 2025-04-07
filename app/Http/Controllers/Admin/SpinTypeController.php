<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpinType;
use Illuminate\Http\Request;

class SpinTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $spinTypes = SpinType::all();
        return view('spin-types.index', compact('spinTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('spin-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:spin_types,name',
            'display_name' => 'required',
        ]);

        SpinType::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
        ]);

        return redirect()->route('admin.spin-types.index')->with('success', 'Thêm loại phần thưởng thành công');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $spinType = SpinType::findOrFail($id);
        return view('spin-types.edit', compact('spinType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $spinType = SpinType::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:spin_types,name,' . $spinType->id,
            'display_name' => 'required',
        ]);

        $spinType->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
        ]);

        return redirect()->route('admin.spin-types.index')->with('success', 'Cập nhật loại phần thưởng thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $spinType = SpinType::findOrFail($id);
        $spinType->delete();

        return redirect()->route('admin.spin-types.index')->with('success', 'Xóa loại phần thưởng thành công');
    }
}
