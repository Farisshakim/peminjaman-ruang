<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
                Rule::unique('facilities', 'name'),
            ],
            'total_stock' => [
                'required',
                'integer',
                'min:0',
                'max:100000',
            ],
        ], [
            'name.required' => 'Nama fasilitas wajib diisi.',
            'name.unique' => 'Nama fasilitas sudah terdaftar.',
            'name.max' => 'Nama fasilitas maksimal 100 karakter.',
            'total_stock.required' => 'Total stok wajib diisi.',
            'total_stock.integer' => 'Total stok harus berupa bilangan bulat.',
            'total_stock.min' => 'Total stok tidak boleh negatif.',
            'total_stock.max' => 'Total stok maksimal 100000.',
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
            'total_stock' => [
                'required',
                'integer',
                'min:0',
                'max:100000',
            ],
        ], [
            'name.required' => 'Nama fasilitas wajib diisi.',
            'name.unique' => 'Nama fasilitas sudah terdaftar.',
            'name.max' => 'Nama fasilitas maksimal 100 karakter.',
            'total_stock.required' => 'Total stok wajib diisi.',
            'total_stock.integer' => 'Total stok harus berupa bilangan bulat.',
            'total_stock.min' => 'Total stok tidak boleh negatif.',
            'total_stock.max' => 'Total stok maksimal 100000.',
        ]);

        DB::transaction(function () use ($facility, $data) {
            $current = Facility::whereKey($facility->id)
                ->lockForUpdate()
                ->firstOrFail();

            $allocated = DB::table('room_facilities')
                ->where('facility_id', $current->id)
                ->lockForUpdate()
                ->get()
                ->sum('quantity');

            if ((int) $data['total_stock'] < $allocated) {
                throw ValidationException::withMessages([
                    'total_stock' =>
                        "Total stok tidak boleh kurang dari {$allocated}, "
                        .'karena barang tersebut sudah dialokasikan ke ruangan.',
                ]);
            }

            $current->update($data);
        }, 3);

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