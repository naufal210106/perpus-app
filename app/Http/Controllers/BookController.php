<?php 
  
namespace App\Http\Controllers; 
  
use App\Models\Book; 
use Illuminate\Http\Request; 
  
class BookController extends Controller 
{ 
    public function index() 
    { 
        // Mengambil seluruh data buku, diurutkan dari yang terbaru, dibagi per 10 data (paginasi)
    $books = Book::latest()->paginate(10);

    // Mengirim data $books ke halaman Vue bernama Books/Index
    // PENTING: tulisan 'Books/Index' TIDAK memakai ekstensi .vue dan TIDAK memakai path lengkap
    // seperti resources/js/Pages/Books/Index.vue -- cukup 'Books/Index' saja
    return inertia('Books/Index', [
        'books' => $books,
    ]);
    } 
  
    public function create() 
{ 
    // Sama seperti index(), nama halaman cukup 'Books/Create' tanpa .vue 
    return inertia('Books/Create'); 
}
  
   public function store(Request $request) 
{ 
    // 1. Validasi data yang dikirim dari form (lihat Dasar Teori E.5) 
    $validated = $request->validate([ 
        'kode_buku'  => 'required|unique:books',   // wajib diisi, tidak boleh sama dengan kode_buku lain 
        'judul'      => 'required|max:255', 
        'pengarang'  => 'required|max:255', 
        'penerbit'   => 'nullable|max:255',        // boleh kosong 
        'tahun'      => 'nullable|digits:4',       // jika diisi, harus 4 digit angka 
        'stok'       => 'required|integer|min:0', 
    ]); 
  
    // 2. Jika seluruh data valid, simpan sebagai baris baru di tabel books 
    Book::create($validated); 
  
    // 3. Arahkan kembali ke halaman daftar buku, sertakan pesan sukses 
    return redirect()->route('books.index')->with('success', 'Buku berhasil ditambahkan'); 
} 
  
    public function edit(Book $book) 
    { 
        // 
    } 
  
    public function update(Request $request, Book $book) 
    { 
        // 
    } 
  
    public function destroy(Book $book) 
    { 
        // 
    } 
}