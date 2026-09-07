<script setup lang="ts">
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import { ClinicDeactivateKey, RefreshTableKey } from '@/keys/clinics/clinicKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import axios from 'axios';
import { inject, type Ref } from 'vue';
import { toast } from 'vue3-toastify';

const deactivateModal = inject<any>(ClinicDeactivateKey);
const refreshTableRef = inject(RefreshTableKey);
const loading = inject<Ref<boolean>>(LoadingKey)!;

if (!deactivateModal) {
    throw new Error('ClinicDeactivateModal precisa estar dentro do provider');
}

function close() {
    deactivateModal.isOpen.value = false;
}

async function submit() {
    if (!deactivateModal.clinic.value || loading.value) return;

    try {
        loading.value = true;
        await axios.patch(
            `/clinics/${deactivateModal.clinic.value.id}/deactivate`,
            {
                confirm: true,
            },
        );

		refreshTableRef?.value?.();

        toast.success('Clínica inativada com sucesso');
        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao inativar clínica',
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
	<div
		v-if="deactivateModal.isOpen.value"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Inativar clínica"
				subtitle="Confirme a inativação da clínica."
			/>

			<div class="px-6 py-5">
				<div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
					<p class="text-sm leading-relaxed text-amber-800">
						Ao inativar esta clínica, as agendas e inscrições
						relacionadas também serão inativadas.
					</p>
				</div>
			</div>

			<FormFooter
				:loading="loading"
				action="deactivate"
				action-label="Inativar"
				@cancel="close"
				@deactivate="submit"
			/>
		</div>
	</div>
</template>
