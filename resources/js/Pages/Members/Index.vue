<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import axios from 'axios';

const props = defineProps({
    members: Object,
    search: String,
});

const keyword = ref(props.search ?? '');
const hasilCari = ref(null);
const sedangMencari = ref(false);
const pesanError = ref('');

let timer = null;
let permintaan = null;

const daftarAnggota = computed(() => hasilCari.value ?? props.members.data);

async function ambilHasil() {
    if (permintaan) {
        permintaan.abort();
    }
    permintaan = new AbortController();
    sedangMencari.value = true;
    pesanError.value = '';

    try {
        const respons = await axios.get(route('members.search'), {
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
    if (confirm('Yakin ingin menghapus anggota ini?')) {
        router.delete(route('members.destroy', id), {
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
        <div class="p-4">
            <h1 class="text-xl font-bold">Data Anggota</h1>

            <div v-if="$page.props.flash.success" class="my-2 text-green-700">
                {{ $page.props.flash.success }}
            </div>

            <div class="my-3 flex items-center gap-3">
                <Link href="/members/create">+ Tambah Anggota</Link>

                <input
                    v-model="keyword"
                    type="text"
                    placeholder="Ketik nama atau NIS/NIM..."
                >

                <span v-if="sedangMencari" class="text-gray-500">Mencari...</span>
            </div>

            <div v-if="pesanError" class="my-2 text-red-600">{{ pesanError }}</div>

            <table class="border-collapse">
                <thead>
                    <tr>
                        <th class="border px-3 py-2">Foto</th>
                        <th class="border px-3 py-2">NIS/NIM</th>
                        <th class="border px-3 py-2">Nama</th>
                        <th class="border px-3 py-2">No. HP</th>
                        <th class="border px-3 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="member in daftarAnggota" :key="member.id">
                        <td class="border px-3 py-2">
                            <img
                                v-if="member.foto"
                                :src="`/storage/${member.foto}`"
                                alt="Foto anggota"
                                class="h-16 w-16 object-cover"
                            >
                            <span v-else>-</span>
                        </td>
                        <td class="border px-3 py-2">{{ member.nis_nim }}</td>
                        <td class="border px-3 py-2">{{ member.nama }}</td>
                        <td class="border px-3 py-2">{{ member.no_hp }}</td>
                        <td class="border px-3 py-2">
                            <Link :href="`/members/${member.id}/edit`" class="mr-3">Edit</Link>
                            <button @click="hapus(member.id)">Hapus</button>
                        </td>
                    </tr>
                    <tr v-if="daftarAnggota.length === 0">
                        <td colspan="5" class="border px-3 py-2">
                            {{ keyword.trim() !== '' ? 'Tidak ada anggota yang ditemukan.' : 'Belum ada data anggota.' }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="hasilCari === null" class="mt-3 flex gap-3">
                <template v-for="link in members.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" v-html="link.label" />
                    <span v-else v-html="link.label" class="text-gray-400" />
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>