<template></template>

<script setup lang="ts">
import baTableClass from '/@/utils/baTable'
import { authGroup } from '/@/api/controllerUrls'

const baTable = new baTableClass(authGroup, {
    column: [],
})
</script>

<style scoped lang="scss"></style>
