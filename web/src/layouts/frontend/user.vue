<template>会员中心布局</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useUserInfo } from '/@/stores/userInfo'
import { useRouter } from 'vue-router'

const router = useRouter()
const userInfo = useUserInfo()

onMounted(() => {
    if (!userInfo.token) return router.push({ name: 'userLogin' })

    // 请求会员中心
})
</script>

<style scoped lang="scss"></style>
