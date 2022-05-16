<template>
    <div class="lang">
        <img :src="lang" alt="lang icon" />
        <div class="lang-list">
            <span class="lang-list-arrow"></span>
            <div @click="selectLang('zh-cn')" class="lang-item">中文简体</div>
            <div @click="selectLang('en')" class="lang-item">English</div>
        </div>
    </div>
    <div class="logo-box">
        <img class="logo" alt="Build Admin logo" :src="logo" />
        <div class="title">{{ t('Install BuildAdmin') }}</div>
    </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import logo from '/@/assets/img/logo.svg'
import lang from '/@/assets/img/lang.svg'

const { t, locale } = useI18n()

const selectLang = (name: string) => {
    window.localStorage.setItem('ba-lang', name)
    location.reload()
}
</script>

<style scoped lang="scss">
.lang {
    position: fixed;
    top: 10px;
    right: 10px;
    width: 86px;
    &:hover,
    .lang-list:hover {
        .lang-list {
            display: block;
        }
    }
    img {
        display: block;
        margin: 0 auto;
        width: 30px;
        height: 30px;
    }
    .lang-list {
        display: none;
        margin-top: 5px;
        background-color: #fff;
        position: relative;
        border-radius: 4px;
        transition: all 0.5s ease;
        .lang-list-arrow {
            top: -4px;
            width: 10px;
            height: 10px;
            position: absolute;
            left: 38px;
        }
        .lang-list-arrow::before {
            border-bottom-color: transparent !important;
            border-right-color: transparent !important;
            border-top-left-radius: 2px;
            border: 1px solid #fff;
            background: #fff;
            right: 0;
            position: absolute;
            width: 10px;
            height: 10px;
            z-index: -1;
            content: ' ';
            transform: rotate(45deg);
            box-sizing: border-box;
        }
        .lang-item {
            cursor: pointer;
            padding: 10px 15px;
            border-bottom: 1px solid #ebeef5;
            &:hover {
                background-color: #f5f5f5;
            }
        }
        .lang-item:last-child {
            border: none;
        }
    }
}
.logo-box {
    margin-top: 10px;
    .logo {
        display: block;
        margin: 0 auto;
        width: 110px;
        height: 110px;
    }
    .title {
        display: block;
        text-align: center;
        font-size: 28px;
        color: #606266;
    }
}
</style>
