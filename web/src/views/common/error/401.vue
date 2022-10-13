<template>
    <div class="page">
        <div class="container">
            <div class="fbi">401 WARNING</div>
            <div class="warning">
                {{ $t('401.noPowerTip') }}
            </div>
            <div class="page-footer">
                <el-button-group>
                    <el-button size="large" type="info">
                        <router-link class="stopcode-a" to="/">{{ $t('404.Return to home page') }}</router-link>
                    </el-button>
                    <el-button size="large" type="info">
                        <router-link class="stopcode-a" to="">
                            <span @click="$router.back()">{{ $t('404.Back to previous page') }}</span>
                        </router-link>
                    </el-button>
                </el-button-group>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
.page {
    height: 100vh;
    width: 100vw;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.container {
    color: var(--ba-bg-color-overlay);
    width: 60vw;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
}
.fbi {
    display: inline-block;
    font-size: 80px;
    font-weight: bold;
    text-align: center;
    background: #aa0000;
    margin: 20px auto;
    padding: 0 30px;
}
.warning {
    font-size: 24px;
    width: 100%;
}
.warning:first-letter {
    font-size: 40px;
}
.page-footer {
    padding-top: 60px;
}
.stopcode-a {
    color: var(--ba-bg-color-overlay);
    text-decoration: none;
}
@media screen and (max-width: 768px) {
    .container {
        width: 90vw;
    }
    .fbi {
        font-size: 50px;
        padding: 10px 30px;
    }
    .warning {
        font-size: 16px;
    }
    .warning:first-letter {
        font-size: 30px;
    }
}
</style>
