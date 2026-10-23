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
        // where(...)->orWhere(...) berarti: cocok di judul ATAU cocok di pengarang 
        $query->where('judul', 'like', "%{$search}%") 
              ->orWhere('pengarang', 'like', "%{$search}%"); 
    })->latest()->paginate(10)->withQueryString(); 
 
    // withQueryString() memastikan kata kunci pencarian tetap terbawa saat pindah halaman paginasi 
  
    return inertia('Books/Index', [ 
        'books' => $books, 
        'search' => $request->search,   // dikirim balik ke Vue agar kotak pencarian tetap terisi 
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
    // $book sudah otomatis berisi data buku yang akan diedit (hasil Route Model Binding) 
    // Kirim data tersebut ke halaman Vue 'Books/Edit' agar form bisa menampilkan nilai lama 
    return inertia('Books/Edit', [ 
        'book' => $book, 
    ]); 
} 
  
public function update(Request $request, Book $book) 
{ 
    // Aturan validasi sama seperti store() pada Pertemuan 6, dengan SATU perbedaan penting: 
    // aturan unique:books untuk kode_buku perlu mengizinkan kode milik buku ini sendiri, 
    // karena jika tidak, Laravel akan mengira kode_buku ini 'sudah dipakai' oleh dirinya sendiri 
    $validated = $request->validate([ 
        'kode_buku'  => 'required|unique:books,kode_buku,' . $book->id,  // kecualikan id buku ini 
        'judul'      => 'required|max:255', 
        'pengarang'  => 'required|max:255', 
        'penerbit'   => 'nullable|max:255', 
        'tahun'      => 'nullable|digits:4', 
        'stok'       => 'required|integer|min:0', 
    ]); 
  
    // Mengganti data lama pada $book dengan data baru yang sudah divalidasi 
    $book->update($validated); 
  
    // Kembali ke daftar buku, sertakan flash message (lihat Dasar Teori E.3) 
    return redirect()->route('books.index')->with('success', 'Buku berhasil diperbarui'); 
}
  
    public function destroy(Book $book) 
{ 
// Menghapus baris data buku ini secara permanen dari tabel books 
$book->delete(); 
// Kembali ke daftar buku, sertakan flash message 
return redirect()->route('books.index')->with('success', 'Buku berhasil dihapus'); 
} 
}