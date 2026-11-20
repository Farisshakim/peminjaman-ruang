<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::withCount('rooms')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.facilities.index', compact('facilities'));
    }

    public function create()
    {
        return view('admin.facilities.form', [
            'facility' => new Facility(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:facilities,name',
            ],
        ], [
            'name.required' => 'Nama fasilitas wajib diisi.',
            'name.max' => 'Nama fasilitas maksimal 100 karakter.',
            'name.unique' => 'Nama fasilitas sudah terdaftar.',
        ]);

        Facility::create($data);

        return redirect()
            ->route('admin.facilities.index')
            ->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function edit(Facility $facility)
    {
        return view('admin.facilities.form', compact('facility'));
    }

    public function update(Request $request, Facility $facility)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('facilities', 'name')->ignore($facility->id),
            ],
        ], [
            'name.required' => 'Nama fasilitas wajib diisi.',
            'name.max' => 'Nama fasilitas maksimal 100 karakter.',
            'name.unique' => 'Nama fasilitas sudah terdaftar.',
        ]);

        $facility->update($data);

        return redirect()
            ->route('admin.facilities.index')
            ->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Facility $facility)
    {
        if ($facility->rooms()->exists()) {
            return redirect()
                ->route('admin.facilities.index')
                ->with('error', 'Fasilitas masih digunakan oleh ruang dan tidak dapat dihapus.');
        }

        try {
            $facility->delete();
        } catch (QueryException $exception) {
            // Menangani kemungkinan fasilitas dipakai ruang
            // setelah pemeriksaan di atas, sebelum penghapusan.
            if ((int) ($exception->errorInfo[1] ?? 0) === 1451) {
                return redirect()
                    ->route('admin.facilities.index')
                    ->with('error', 'Fasilitas masih digunakan oleh ruang dan tidak dapat dihapus.');
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.facilities.index')
            ->with('success', 'Fasilitas berhasil dihapus.');
    }
}