<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// 'member' diterima dari MemberController@edit, berisi data lama anggota ini
const props = defineProps({
    member: Object,
});

const form = useForm({
    _method: 'put', // "menyamar" sebagai PUT (method spoofing)
    nama: props.member.nama,
    nis_nim: props.member.nis_nim,
    no_hp: props.member.no_hp,
    foto: null, // tetap null apabila pengguna tidak memilih foto baru
});

const pratinjau = ref(null);

function pilihFoto(event) {
    const berkas = event.target.files[0];
    form.foto = berkas ?? null;
    pratinjau.value = berkas ? URL.createObjectURL(berkas) : null;
}

function submit() {
    // Dikirim dengan POST (bukan PUT) karena PHP tidak bisa membaca berkas dari permintaan PUT;
    // Laravel membaca _method: 'put' lalu meneruskannya ke method update()
    form.post(route('members.update', props.member.id));
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-4">
            <h1 class="text-xl font-bold">Edit Anggota</h1>

            <form @submit.prevent="submit" class="mt-3 space-y-3">
                <div>
                    <label class="block">Nama</label>
                    <input v-model="form.nama" type="text">
                    <div v-if="form.errors.nama" class="text-red-600">{{ form.errors.nama }}</div>
                </div>

                <div>
                    <label class="block">NIS/NIM</label>
                    <input v-model="form.nis_nim" type="text">
                    <div v-if="form.errors.nis_nim" class="text-red-600">{{ form.errors.nis_nim }}</div>
                </div>

                <div>
                    <label class="block">No. HP</label>
                    <input v-model="form.no_hp" type="text">
                    <div v-if="form.errors.no_hp" class="text-red-600">{{ form.errors.no_hp }}</div>
                </div>

                <div>
                    <label class="block">Foto Saat Ini</label>
                    <img
                        v-if="member.foto"
                        :src="`/storage/${member.foto}`"
                        alt="Foto saat ini"
                        class="h-56 w-56 object-cover"
                        style="width: 224px; height: 224px; object-fit: cover;"
                    >
                    <span v-else>Belum ada foto</span>
                </div>

                <div>
                    <label class="block">Ganti Foto (kosongkan jika tidak ingin mengganti)</label>
                    <input type="file" accept="image/png, image/jpeg" @change="pilihFoto">
                    <div v-if="form.errors.foto" class="text-red-600">{{ form.errors.foto }}</div>
                    <img
                        v-if="pratinjau"
                        :src="pratinjau"
                        alt="Pratinjau foto baru"
                        class="mt-2 h-56 w-56 object-cover"
                        style="width: 224px; height: 224px; object-fit: cover;"
                    >
                </div>

                <button type="submit" :disabled="form.processing">Simpan Perubahan</button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>