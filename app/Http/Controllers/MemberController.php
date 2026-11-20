<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        // Mengambil data anggota; jika ada kata kunci, filter berdasarkan nama, NIS/NIM, ATAU no. HP
        $members = Member::when($request->search, function ($query, $search) {
            $query->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis_nim', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
        })->latest()->paginate(10)->withQueryString();

        return inertia('Members/Index', [
            'members' => $members,
            'search' => $request->search,
        ]);
    }

    /**
     * Endpoint JSON untuk live search anggota (dipanggil oleh Axios).
     */
    public function search(Request $request)
    {
        $kataKunci = $request->query('q', '');

        $members = Member::where(function ($query) use ($kataKunci) {
            $query->where('nama', 'like', "%{$kataKunci}%")
                  ->orWhere('nis_nim', 'like', "%{$kataKunci}%");
        })
            ->latest()
            ->limit(10)
            ->get(['id', 'nama', 'nis_nim', 'no_hp', 'foto']);

        return response()->json($members);
    }

    /**
     * Endpoint JSON untuk validasi realtime: apakah NIS/NIM masih tersedia?
     */
    public function cekNis(Request $request)
    {
        $nisNim = $request->query('nis_nim', '');

        // abaikan = id anggota yang sedang diedit (kosong pada form tambah)
        $abaikanId = $request->query('abaikan');

        $sudahDipakai = Member::where('nis_nim', $nisNim)
            ->when($abaikanId, function ($query, $id) {
                $query->where('id', '!=', $id);
            })
            ->exists();

        return response()->json([
            'tersedia' => ! $sudahDipakai,
        ]);
    }

    public function create()
    {
        return inertia('Members/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|max:255',
            'nis_nim' => 'required|max:50|unique:members',
            'no_hp' => 'nullable|digits_between:8,15',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('members', 'public');
        }

        Member::create($validated);

        return redirect()->route('members.index')->with('success', 'Anggota berhasil ditambahkan');
    }

    public function show(Member $member)
    {
        //
    }

    public function edit(Member $member)
    {
        return inertia('Members/Edit', [
            'member' => $member,
        ]);
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'nama' => 'required|max:255',
            'nis_nim' => 'required|max:50|unique:members,nis_nim,' . $member->id,
            'no_hp' => 'nullable|digits_between:8,15',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            if ($member->foto) {
                Storage::disk('public')->delete($member->foto);
            }
            $validated['foto'] = $request->file('foto')->store('members', 'public');
        } else {
            unset($validated['foto']);
        }

        $member->update($validated);

        return redirect()->route('members.index')->with('success', 'Anggota berhasil diperbarui');
    }

    public function destroy(Member $member)
    {
        if ($member->foto) {
            Storage::disk('public')->delete($member->foto);
        }

        $member->delete();

        return redirect()->route('members.index')->with('success', 'Anggota berhasil dihapus');
    }
}