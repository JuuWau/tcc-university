<script setup lang="ts">
import Multiselect from '@vueform/multiselect'
</script>

<template>
    <Multiselect
        v-bind="$attrs"
        :noOptionsText="'Nenhuma opção disponível'"
        :noResultsText="'Nenhum resultado encontrado'"
    />
</template>