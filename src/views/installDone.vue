<template>
    <Header />
    <div class="container">
        <div class="table-title">✨ 感谢使用 BuildAdmin ✨</div>
        <div class="done-box">
            <div>后台地址</div>
            <div class="admin-url">http://localhost:8000</div>
        </div>
        <div class="done-button">
            <button class="index">访问前台</button>
            <button class="admin">访问后台</button>
        </div>
    </div>
</template>

<script setup lang="ts">
import Header from '../components/header/index.vue'
</script>

<style scoped lang="scss">
.container {
    margin-top: 20px;
    .table-title {
        display: block;
        text-align: center;
        font-size: 20px;
        color: #67c23a;
    }
    .done-box {
        display: block;
        max-width: 300px;
        margin: 20px auto;
        background-color: #FFCDCD;
        padding: 20px;
        border-radius: 6px;
        text-align: center;
        color: #D9534F;
        font-size: 15px;
    }
    .admin-url {
        background-color: #fcfcfc;
        font-size: 16px;
        text-align: center;
        padding: 5px;
        border-radius: 4px;
        margin-top: 10px;
        cursor: pointer;
        &:hover {
            text-decoration: underline;
        }
    }
    .done-button {
        display: flex;
        justify-content: space-around;
        max-width: 300px;
        margin: 0 auto;
        .index {
            background-color: #909399;
            color: #fff;
        }
        .admin {
            background-color: #409eff;
            color: #fff;
        }
        button {
            font-weight: bold;
            border-radius: 6px;
            border: none;
            outline: none;
            width: 110px;
            height: 40px;
            opacity: 0.8;
            &:hover{
                opacity: 1;
            }
        }
    }
}
</style>
