<template></template>

<script setup lang="ts">
import baTableClass from '/@/utils/baTable'
import { authGroup } from '/@/api/controllerUrls'
import { baTableApi } from '/@/api/common'

const baTable = new baTableClass(new baTableApi(authGroup), {
    column: [],
})
</script>

<style scoped lang="scss"></style>
