<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegionController extends Controller
{
    private const LEVELS = ['kecamatan', 'kabupaten', 'provinsi'];

    public function index()
    {
        return view('regions.index', [
            'regions' => Region::withCount('projects')->orderBy('name')->paginate(15),
            'levels' => self::LEVELS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:regions,name'],
            'level' => ['required', Rule::in(self::LEVELS)],
        ]);

        Region::create($data);

        return redirect()->route('regions.index')->with('status', 'Wilayah ditambahkan.');
    }

    public function edit(Region $region)
    {
        return view('regions.edit', ['region' => $region, 'levels' => self::LEVELS]);
    }

    public function update(Request $request, Region $region)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('regions', 'name')->ignore($region->id)],
            'level' => ['required', Rule::in(self::LEVELS)],
        ]);

        $region->update($data);

        return redirect()->route('regions.index')->with('status', 'Wilayah diperbarui.');
    }

    public function destroy(Region $region)
    {
        if ($region->projects()->exists()) {
            return redirect()->route('regions.index')
                ->withErrors(['hapus' => "Wilayah \"{$region->name}\" masih dipakai proyek, tidak bisa dihapus."]);
        }

        $region->delete();

        return redirect()->route('regions.index')->with('status', 'Wilayah dihapus.');
    }
}