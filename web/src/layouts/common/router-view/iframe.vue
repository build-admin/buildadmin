<template>
    <div class="default-main" v-loading="state.loading">
        <iframe
            :src="state.iframeSrc"
            :style="iframeStyle(3)"
            frameborder="0"
            height="100%"
            width="100%"
            id="iframe"
            ref="iframeRef"
            @load="hideLoading"
        ></iframe>
    </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { mainHeight as iframeStyle } from '/@/utils/layout'
import { useRouter } from 'vue-router'

const router = useRouter()

const state = reactive({
    loading: true,
    iframeSrc: router.currentRoute.value.meta.url as string,
})

const hideLoading = () => {
    state.loading = false
}
</script>
