<template>
    <div v-loading="state.loading">
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
import { useRoute } from 'vue-router'

const route = useRoute()

const state = reactive({
    loading: true,
    iframeSrc: route.params.url as string,
})

const hideLoading = () => {
    state.loading = false
}
</script>
