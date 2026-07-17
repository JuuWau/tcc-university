<script setup lang="ts">
import { MessageCircle } from 'lucide-vue-next';

const props = defineProps<{
    params: {
        phone?: string;
        message?: string;
    };
}>();

const phone = props.params.phone?.replace(/\D/g, '');

const hasPhone = !!phone;
</script>

<template>
    <div class="flex h-full items-center justify-center">
        <a
            v-if="hasPhone"
            :href="`https://wa.me/55${phone}?text=${encodeURIComponent(props.params.message ?? '')}`"
            target="_blank"
            class="text-green-700 hover:text-green-800"
            title="Enviar WhatsApp"
        >
            <MessageCircle class="h-5 w-5" />
        </a>

        <MessageCircle
            v-else
            class="h-5 w-5 cursor-not-allowed text-gray-400"
            title="Paciente sem telefone cadastrado"
        />
    </div>
</template>