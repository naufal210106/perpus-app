<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        // Request $request ditambahkan sebagai parameter, untuk membaca query string ?search=...

        $books = Book::when($request->search, function ($query, $search) {
            // Blok ini HANYA dijalankan apabila $request->search tidak kosong
            $query->where('judul', 'like', "%{$search}%")
                  ->orWhere('pengarang', 'like', "%{$search}%");
        })->latest()->paginate(10)->withQueryString();

        return inertia('Books/Index', [
            'books' => $books,
            'search' => $request->search,
        ]);
    }

    /**
     * Endpoint JSON untuk live search buku (dipanggil oleh Axios).
     */
    public function search(Request $request)
    {
        $kataKunci = $request->query('q', '');

        $books = Book::where(function ($query) use ($kataKunci) {
            $query->where('judul', 'like', "%{$kataKunci}%")
                  ->orWhere('pengarang', 'like', "%{$kataKunci}%");
        })
            ->latest()
            ->limit(10)
            ->get(['id', 'kode_buku', 'judul', 'pengarang', 'stok']);

        return response()->json($books);
    }

    public function create()
    {
        return inertia('Books/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_buku'  => 'required|unique:books',
            'judul'      => 'required|max:255',
            'pengarang'  => 'required|max:255',
            'penerbit'   => 'nullable|max:255',
            'tahun'      => 'nullable|digits:4',
            'stok'       => 'required|integer|min:0',
        ]);

        Book::create($validated);

        return redirect()->route('books.index')->with('success', 'Buku berhasil ditambahkan');
    }

    public function edit(Book $book)
    {
        return inertia('Books/Edit', [
            'book' => $book,
        ]);
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'kode_buku'  => 'required|unique:books,kode_buku,' . $book->id,
            'judul'      => 'required|max:255',
            'pengarang'  => 'required|max:255',
            'penerbit'   => 'nullable|max:255',
            'tahun'      => 'nullable|digits:4',
            'stok'       => 'required|integer|min:0',
        ]);

        $book->update($validated);

        return redirect()->route('books.index')->with('success', 'Buku berhasil diperbarui');
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()->route('books.index')->with('success', 'Buku berhasil dihapus');
    }
}