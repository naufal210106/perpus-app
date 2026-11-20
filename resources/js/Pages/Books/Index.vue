<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import axios from 'axios';

const props = defineProps({
    books: Object,
    search: String,
});

const keyword = ref(props.search ?? '');
const hasilCari = ref(null);
const sedangMencari = ref(false);
const pesanError = ref('');

let timer = null;
let permintaan = null;

const daftarBuku = computed(() => hasilCari.value ?? props.books.data);

async function ambilHasil() {
    if (permintaan) {
        permintaan.abort();
    }
    permintaan = new AbortController();

    sedangMencari.value = true;
    pesanError.value = '';

    try {
        const respons = await axios.get(route('books.search'), {
            params: { q: keyword.value.trim() },
            signal: permintaan.signal,
        });
        hasilCari.value = respons.data;
        sedangMencari.value = false;
    } catch (error) {
        if (axios.isCancel(error)) {
            return;
        }
        sedangMencari.value = false;
        pesanError.value = 'Pencarian gagal. Muat ulang halaman, lalu coba lagi.';
    }
}

watch(keyword, (nilai) => {
    clearTimeout(timer);

    if (nilai.trim() === '') {
        if (permintaan) {
            permintaan.abort();
        }
        hasilCari.value = null;
        sedangMencari.value = false;
        pesanError.value = '';
        return;
    }

    timer = setTimeout(ambilHasil, 300);
});

function hapus(id) {
    if (confirm('Yakin ingin menghapus buku ini?')) {
        router.delete(route('books.destroy', id), {
            onSuccess: () => {
                if (keyword.value.trim() !== '') {
                    ambilHasil();
                }
            },
        });
    }
}

onBeforeUnmount(() => {
    clearTimeout(timer);
    if (permintaan) {
        permintaan.abort();
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <h1>Data Buku</h1>

        <div v-if="$page.props.flash.success" class="alert alert-success" style="color: red; margin-bottom: 10px;">
            {{ $page.props.flash.success }}
        </div>

        <Link href="/books/create">+ Tambah Buku</Link>

        <input
            v-model="keyword"
            type="text"
            placeholder="Ketik judul atau pengarang..."
        >

        <span v-if="sedangMencari">Mencari...</span>

        <p v-if="pesanError">{{ pesanError }}</p>

        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Judul</th>
                    <th>Pengarang</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="book in daftarBuku" :key="book.id">
                    <td>{{ book.kode_buku }}</td>
                    <td>{{ book.judul }}</td>
                    <td>{{ book.pengarang }}</td>
                    <td>{{ book.stok }}</td>
                    <td>
                        <Link :href="`/books/${book.id}/edit`">Edit</Link>
                        <button @click="hapus(book.id)">Hapus</button>
                    </td>
                </tr>
                <tr v-if="daftarBuku.length === 0">
                    <td colspan="5">Tidak ada buku yang ditemukan.</td>
                </tr>
            </tbody>
        </table>
    </AuthenticatedLayout>
</template>