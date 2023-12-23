<template>
    <div :id="uuid">
        <div class="ba-click-captcha" :class="props.class">
            <div v-if="state.loading" class="loading">{{ i18n.global.t('utils.Loading') }}</div>
            <div v-else class="captcha-img-box">
                <img
                    class="captcha-img"
                    @click.prevent="onRecord($event)"
                    :src="state.captcha.base64"
                    :alt="i18n.global.t('validate.Captcha loading failed, please click refresh button')"
                />
                <span
                    v-for="(item, index) in state.xy"
                    :key="index"
                    class="step"
                    @click="onCancelRecord(index)"
                    :style="`left:${parseFloat(item.split(',')[0]) - 13}px;top:${parseFloat(item.split(',')[1]) - 13}px`"
                >
                    {{ index + 1 }}
                </span>
            </div>
            <div class="captcha-prompt" v-if="state.tip">
                {{ state.tip }}
            </div>
            <div v-else class="captcha-prompt">
                {{ i18n.global.t('validate.Please click') }}
                <span v-for="(text, index) in state.captcha.text" :key="index" :class="state.xy.length > index ? 'clicaptcha-clicked' : ''">
                    {{ text }}
                </span>
            </div>
            <div class="captcha-refresh-box">
                <div class="captcha-refresh-line captcha-refresh-line-l"></div>
                <i class="fa fa-refresh captcha-refresh-btn" :title="i18n.global.t('Refresh')" @click="load"></i>
                <div class="captcha-refresh-line captcha-refresh-line-r"></div>
            </div>
        </div>
        <div class="ba-layout-shade" @click="onClose"></div>
    </div>
</template>

<script setup lang="ts">
import { reactive, computed } from 'vue'
import { getCaptchaData, checkClickCaptcha } from '/@/api/common'
import { i18n } from '/@/lang'

interface Props {
    uuid: string
    callback?: (captchaInfo: string) => void
    class?: string
    unset?: boolean
    error?: string
    success?: string
}

const props = withDefaults(defineProps<Props>(), {
    uuid: '',
    callback: () => {},
    class: '',
    unset: false,
    error: i18n.global.t('validate.The correct area is not clicked, please try again!'),
    success: i18n.global.t('validate.Verification is successful!'),
})

const state: {
    loading: boolean
    xy: string[]
    tip: string
    captcha: {
        id: string
        text: string
        base64: string
        width: number
        height: number
    }
} = reactive({
    loading: true,
    xy: [],
    tip: '',
    captcha: {
        id: '',
        text: '',
        base64: '',
        width: 350,
        height: 200,
    },
})

const load = () => {
    state.loading = true
    getCaptchaData(props.uuid).then((res) => {
        state.xy = []
        state.tip = ''
        state.loading = false
        state.captcha = res.data
    })
}

const onRecord = (event: MouseEvent) => {
    if (state.xy.length < state.captcha.text.length) {
        state.xy.push(event.offsetX + ',' + event.offsetY)
        if (state.xy.length == state.captcha.text.length) {
            const captchaInfo = [state.xy.join('-'), (event.target as HTMLImageElement).width, (event.target as HTMLImageElement).height].join(';')
            checkClickCaptcha(props.uuid, captchaInfo, props.unset)
                .then(() => {
                    state.tip = props.success
                    setTimeout(() => {
                        props.callback?.(captchaInfo)
                        onClose()
                    }, 1500)
                })
                .catch(() => {
                    state.tip = props.error
                    setTimeout(() => {
                        load()
                    }, 1500)
                })
        }
    }
}

const onCancelRecord = (index: number) => {
    state.xy.splice(index, 1)
}

const onClose = () => {
    document.getElementById(props.uuid)?.remove()
}

const captchaBoxTop = computed(() => (state.captcha.height + 200) / 2 + 'px')
const captchaBoxLeft = computed(() => (state.captcha.width + 24) / 2 + 'px')

load()
</script>

<style scoped lang="scss">
.ba-click-captcha {
    padding: 12px;
    border: 1px solid var(--el-border-color-extra-light);
    background-color: var(--el-color-white);
    position: fixed;
    z-index: 9999991;
    left: calc(50% - v-bind('captchaBoxLeft'));
    top: calc(50% - v-bind('captchaBoxTop'));
    border-radius: 10px;
    box-shadow:
        0 0 0 1px hsla(0, 0%, 100%, 0.3) inset,
        0 0.5em 1em rgba(0, 0, 0, 0.6);
    .loading {
        color: var(--el-color-info);
        width: 350px;
        text-align: center;
        line-height: 200px;
    }
    .captcha-img-box {
        position: relative;
        .captcha-img {
            width: v-bind('state.captcha.width') px;
            height: v-bind('state.captcha.height') px;
            border: none;
            cursor: pointer;
        }
        .step {
            box-sizing: border-box;
            position: absolute;
            width: 20px;
            height: 20px;
            line-height: 20px;
            font-size: var(--el-font-size-small);
            font-weight: bold;
            text-align: center;
            color: var(--el-color-white);
            border: 1px solid var(--el-border-color-extra-light);
            background-color: var(--el-color-primary);
            border-radius: 30px;
            box-shadow: 0 0 10px var(--el-color-white);
            user-select: none;
            cursor: pointer;
        }
    }
    .captcha-prompt {
        height: 40px;
        line-height: 40px;
        font-size: var(--el-font-size-base);
        text-align: center;
        color: var(--el-color-info);
        span {
            margin-left: 10px;
            font-size: var(--el-font-size-medium);
            font-weight: bold;
            color: var(--el-color-error);
            &.clicaptcha-clicked {
                color: var(--el-color-primary);
            }
        }
    }
    .captcha-refresh-box {
        position: relative;
        margin-top: 10px;
        .captcha-refresh-line {
            position: absolute;
            top: 16px;
            width: 140px;
            height: 1px;
            background-color: #ccc;
        }
        .captcha-refresh-line-l {
            left: 5px;
        }
        .captcha-refresh-line-r {
            right: 5px;
        }
        .captcha-refresh-btn {
            cursor: pointer;
            display: block;
            margin: 0 auto;
            width: 32px;
            height: 32px;
            font-size: 32px;
            color: var(--el-color-info);
        }
    }
}
</style>
