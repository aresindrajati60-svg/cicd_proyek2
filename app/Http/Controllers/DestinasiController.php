<?php

namespace App\Http\Controllers;

use App\Models\Destinasi;
use App\Models\Kategori;
use App\Models\ActivityLog; // <-- TAMBAHAN
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DestinasiController extends Controller
{
    private function getPrefix()
    {
        return auth('superadmin')->check() ? 'superadmin' : 'admin';
    }

    // LIST DESTINASI
    public function index(Request $request)
{
    $keyword = $request->keyword;

    if(auth()->guard('superadmin')->check()){
        $id = auth()->guard('superadmin')->user()->id_superadmin;
        $role = 'superadmin';
    }else{
        $id = auth()->guard('web')->user()->id_admin;
        $role = 'admin';
    }

    $destinasi = Destinasi::with('kategori')
        ->where('created_by_id', $id)
        ->where('created_by_role', $role)
        ->when($keyword, function ($query) use ($keyword) {
            // Membuat search case-insensitive
            $query->where(function($q) use ($keyword) {
                $q->whereRaw('LOWER(nama) LIKE ?', ['%' . strtolower($keyword) . '%'])
                  ->orWhereRaw('LOWER(lokasi) LIKE ?', ['%' . strtolower($keyword) . '%']);
            });
        })
        ->latest()
        ->paginate(12);

    $prefix = $this->getPrefix();

    return view('destinasi.index', compact('destinasi', 'keyword', 'prefix'));
}

    // FORM CREATE DESTINASI
    public function create()
    {
        $kategori = Kategori::all();
        $prefix = $this->getPrefix();
        return view('destinasi.create', compact('kategori', 'prefix'));
    }

    // STORE DESTINASI BARU
    public function store(Request $request)
    {
        $data = $request->validate([
    'nama' => ['required', 'string', 'max:255', 'regex:/\S/'],
    'lokasi' => ['required', 'string', 'max:255', 'regex:/\S/'],
    'deskripsi' => ['required', 'string', 'regex:/\S/'],
    'alamat_lengkap' => ['required', 'string', 'regex:/\S/'],
    'jam_buka_weekday' => ['required', 'string', 'regex:/\S/'],
    'jam_buka_weekend' => ['required', 'string', 'regex:/\S/'],
    'harga_tiket_weekday' => ['required', 'numeric'],
    'harga_tiket_weekend' => ['required', 'numeric'],
    'id_kategori' => ['required', 'exists:kategori,id_kategori'],
    'foto' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
], [
    'nama.required' => 'Field nama harus diisi, tidak boleh kosong!',
    'nama.regex' => 'Field nama tidak boleh hanya spasi!',

    'lokasi.required' => 'Field lokasi harus diisi, tidak boleh kosong!',
    'lokasi.regex' => 'Field lokasi tidak boleh hanya spasi!',

    'deskripsi.required' => 'Field deskripsi harus diisi, tidak boleh kosong!',
    'deskripsi.regex' => 'Field deskripsi tidak boleh hanya spasi!',

    'alamat_lengkap.required' => 'Field alamat lengkap harus diisi, tidak boleh kosong!',
    'alamat_lengkap.regex' => 'Field alamat lengkap tidak boleh hanya spasi!',

    'jam_buka_weekday.required' => 'Jam buka weekday harus diisi!',
    'jam_buka_weekend.required' => 'Jam buka weekend harus diisi!',

    'harga_tiket_weekday.required' => 'Harga tiket weekday harus diisi!',
    'harga_tiket_weekend.required' => 'Harga tiket weekend harus diisi!',

    'id_kategori.required' => 'Kategori harus dipilih!',

    'foto.required' => 'Gambar destinasi harus diupload!',
]);

        // simpan pembuat
        if(auth()->guard('superadmin')->check()){
            $data['created_by_id'] = auth()->guard('superadmin')->user()->id_superadmin;
            $data['created_by_role'] = 'superadmin';
        }else{
            $data['created_by_id'] = auth()->guard('web')->user()->id_admin;
            $data['created_by_role'] = 'admin';
        }

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('destinasi', 'public');
        }

        $destinasi = Destinasi::create([
            'nama' => $data['nama'],
            'lokasi' => $data['lokasi'],
            'deskripsi' => $data['deskripsi'],
            'alamat_lengkap' => $data['alamat_lengkap'],
            'weekday' => $data['jam_buka_weekday'] ?? 'Belum ditentukan',
            'weekend' => $data['jam_buka_weekend'] ?? 'Belum ditentukan',
            'harga_tiket_weekday' => $data['harga_tiket_weekday'] ?? 0,
            'harga_tiket_weekend' => $data['harga_tiket_weekend'] ?? 0,
            'id_kategori' => $data['id_kategori'],
            'foto' => $data['foto'] ?? null,
            'created_by_id' => $data['created_by_id'],
            'created_by_role' => $data['created_by_role'],
        ]);

        // ================= LOG AKTIVITAS =================
        if(auth()->guard('superadmin')->check()){
            $user = auth()->guard('superadmin')->user()->name;
            $role = 'Super Admin';
        }else{
            $user = auth()->guard('web')->user()->name;
            $role = 'Admin Wisata';
        }

        ActivityLog::create([
            'user_name' => $user,
            'role' => $role,
            'activity' => 'Menambahkan destinasi "' . $destinasi->nama . '"',
            'status' => 'Success'
        ]);

        $prefix = $this->getPrefix();

        return redirect()->route($prefix . '.destinasi.index')
                         ->with('success', 'Destinasi berhasil ditambahkan!');
    }

    // FORM EDIT DESTINASI
    public function edit(Destinasi $destinasi)
    {
        $kategori = Kategori::all();
        $prefix = $this->getPrefix();
        return view('destinasi.edit', compact('destinasi', 'kategori', 'prefix'));
    }

    // UPDATE DESTINASI
    public function update(Request $request, $id)
    {
        $destinasi = Destinasi::findOrFail($id);

        $data = $request->validate([
    'nama' => ['required', 'string', 'max:255', 'regex:/\S/'],
    'lokasi' => ['required', 'string', 'max:255', 'regex:/\S/'],
    'deskripsi' => ['required', 'string', 'regex:/\S/'],
    'alamat_lengkap' => ['required', 'string', 'regex:/\S/'],
    'jam_buka_weekday' => ['required', 'string', 'regex:/\S/'],
    'jam_buka_weekend' => ['required', 'string', 'regex:/\S/'],
    'harga_tiket_weekday' => ['required', 'numeric'],
    'harga_tiket_weekend' => ['required', 'numeric'],
    'id_kategori' => ['required', 'exists:kategori,id_kategori'],
    'foto' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
], [
    'nama.required' => 'Field nama harus diisi, tidak boleh kosong!',
    'nama.regex' => 'Field nama tidak boleh hanya spasi!',

    'lokasi.required' => 'Field lokasi harus diisi, tidak boleh kosong!',
    'lokasi.regex' => 'Field lokasi tidak boleh hanya spasi!',

    'deskripsi.required' => 'Field deskripsi harus diisi, tidak boleh kosong!',
    'deskripsi.regex' => 'Field deskripsi tidak boleh hanya spasi!',

    'alamat_lengkap.required' => 'Field alamat lengkap harus diisi, tidak boleh kosong!',
    'alamat_lengkap.regex' => 'Field alamat lengkap tidak boleh hanya spasi!',

    'jam_buka_weekday.required' => 'Jam buka weekday harus diisi!',
    'jam_buka_weekend.required' => 'Jam buka weekend harus diisi!',

    'harga_tiket_weekday.required' => 'Harga tiket weekday harus diisi!',
    'harga_tiket_weekend.required' => 'Harga tiket weekend harus diisi!',

    'id_kategori.required' => 'Kategori harus dipilih!',

    'foto.required' => 'Gambar destinasi harus diupload!',
]);

        $updateData = [
            'nama' => $data['nama'],
            'lokasi' => $data['lokasi'],
            'deskripsi' => $data['deskripsi'],
            'alamat_lengkap' => $data['alamat_lengkap'],
            'weekday' => $data['jam_buka_weekday'] ?? 'Belum ditentukan',
            'weekend' => $data['jam_buka_weekend'] ?? 'Belum ditentukan',
            'harga_tiket_weekday' => $data['harga_tiket_weekday'] ?? 0,
            'harga_tiket_weekend' => $data['harga_tiket_weekend'] ?? 0,
            'id_kategori' => $data['id_kategori'],
        ];

        if ($request->hasFile('foto')) {
            if ($destinasi->foto && Storage::disk('public')->exists($destinasi->foto)) {
                Storage::disk('public')->delete($destinasi->foto);
            }

            $updateData['foto'] = $request->file('foto')->store('destinasi', 'public');
        }

        $destinasi->update($updateData);

        // ================= LOG AKTIVITAS =================
        if(auth()->guard('superadmin')->check()){
            $user = auth()->guard('superadmin')->user()->name;
            $role = 'Super Admin';
        }else{
            $user = auth()->guard('web')->user()->name;
            $role = 'Admin Wisata';
        }

        ActivityLog::create([
            'user_name' => $user,
            'role' => $role,
            'activity' => 'Mengupdate destinasi "' . $destinasi->nama . '"',
            'status' => 'Success'
        ]);

        $prefix = $this->getPrefix();

        return redirect()->route($prefix . '.destinasi.index')
                        ->with('success', 'Destinasi berhasil diperbarui!');
    }

    // DELETE DESTINASI
    public function destroy(Destinasi $destinasi)
    {
        if ($destinasi->foto && Storage::disk('public')->exists($destinasi->foto)) {
            Storage::disk('public')->delete($destinasi->foto);
        }

        // ================= LOG AKTIVITAS =================
        if(auth()->guard('superadmin')->check()){
            $user = auth()->guard('superadmin')->user()->name;
            $role = 'Super Admin';
        }else{
            $user = auth()->guard('web')->user()->name;
            $role = 'Admin Wisata';
        }

        ActivityLog::create([
            'user_name' => $user,
            'role' => $role,
            'activity' => 'Menghapus destinasi "' . $destinasi->nama . '"',
            'status' => 'Success'
        ]);

        $destinasi->delete();

        $prefix = $this->getPrefix();

        return redirect()->route($prefix . '.destinasi.index')
                         ->with('success', 'Destinasi berhasil dihapus!');
    }
}