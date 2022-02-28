<template>
    <div>
        <div>index/index.vue</div>
        <div>{{ defaultLang }}</div>
        <div>{{ $t(`layout.seting`) }}</div>
        <el-date-picker type="date"></el-date-picker>
        <br />
        <Icon size="60" color="red" name="local-lang" />
        <Icon size="50" color="blue" name="el-icon-Edit" />
        <Icon size="80" color="red" name="iconfont icon-ellipsis" />
        <Icon name="fa fa-address-book" />

        <br />
        <div :style="{ width: '400px', marginLeft: '100px' }"><selector v-model="test" />{{ test }}</div>
        <br />
        <div :style="{ width: '400px', marginLeft: '100px' }">
            <selector @change="geted" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import en from '/@/lang/frame/en'
import { useRouter } from 'vue-router'
// import messages from '/@/lang/pages/zh-cn/home'
import { store } from '/@/store/index'
import { editDefaultLang } from '/@/lang/index'
import { getLocalIconfontNames } from '/@/utils/iconfont'
import selector from '/@/components/icon/selector.vue'

var test = ref('el-icon-Aim')

setTimeout(() => {
    test.value = 'el-icon-Avatar'
}, 2000)

const geted = (icon: string) => {
    console.log(icon)
}

const router = useRouter()
/* setTimeout(() => {
    router.push({name: 'admin'})
}, 2000) */

/* setTimeout(() => {
    getLocalIconfontNames().then((res) => {
        console.log(res)
    })
}, 1000) */

/* const { t } = useI18n({
    locale: 'zh-cn',
    messages: {
        'zh-cn': messages,
    },
}) */

const defaultLang = computed(() => store.state.config.defaultLang)

/* setTimeout(() => {
    store.commit('config/setAndCache', {
        name: 'defaultLang',
        value: 'en',
    })
    console.log('设置了')
}, 2000) */

/* setTimeout(() => {
    store.commit('config/setMulti', {
        defaultLang: 'test1',
        fallbackLang: 'test2',
    })
}, 2000) */
</script>
