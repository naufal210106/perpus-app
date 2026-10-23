<script setup> 
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; 
import { Link, router } from '@inertiajs/vue3'; 
import { ref } from 'vue'; 
// 'ref' baru ditambahkan: dipakai untuk menyimpan ketikan pengguna pada kotak pencarian 
  
const props = defineProps({ 
    books: Object, 
    search: String,   // menerima kata kunci terakhir yang dikirim dari Langkah 7 
}); 
  
// Nilai awal kotak pencarian diisi dari props.search, bukan selalu kosong 
const keyword = ref(props.search); 
  
// Function baru: dijalankan saat pengguna menekan Enter pada kotak pencarian 
function cari() { 
    // router.get mengarahkan ke /books dengan tambahan ?search=..., memicu index() di Langkah 7 
    router.get('/books', { search: keyword.value }); 
} 
  
function hapus(id) { 
    if (confirm('Yakin ingin menghapus buku ini?')) { 
        router.delete(route('books.destroy', id)); 
    } 
} 
</script>
  
<template> 
    <AuthenticatedLayout> 
        <h1>Data Buku</h1> 
  
        <!-- Flash message baru: $page.props.flash tersedia karena Langkah 5 --> 
        <div v-if="$page.props.flash.success"> 
            {{ $page.props.flash.success }} 
        </div> 
  
        <Link href="/books/create">+ Tambah Buku</Link> 
        <!-- Enter memanggil cari() -->
        <input
            v-model="keyword"
            @keyup.enter="cari"
            type="text"
            placeholder="Cari judul atau pengarang..."
        >
  
        <table> 
            <thead> 
                <tr> 
                    <th>Kode</th> 
                    <th>Judul</th> 
                    <th>Pengarang</th> 
                    <th>Stok</th> 
                    <th>Aksi</th> 
                    <!-- kolom baru untuk tombol Edit dan Hapus --> 
                </tr> 
            </thead> 
            <tbody> 
                <tr v-for="book in books.data" :key="book.id"> 
                    <td>{{ book.kode_buku }}</td> 
                    <td>{{ book.judul }}</td> 
                    <td>{{ book.pengarang }}</td> 
                    <td>{{ book.stok }}</td> 
                    <td> 
                        <!-- Link menuju form edit, mengarah ke Books/Edit.vue (Langkah 3) --> 
<Link :href="`/books/${book.id}/edit`">Edit</Link> 
<!-- @click memanggil function hapus() yang baru ditambahkan di 
atas --> 
<button @click="hapus(book.id)">Hapus</button> 
</td> 
</tr> 
</tbody> 
</table> 
</AuthenticatedLayout> 
</template> 