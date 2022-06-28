<template>
    <el-aside class="ba-user-layouts">
        <div class="userinfo">
            <div @click="routerPush('account/profile')" class="user-avatar-box">
                <img class="user-avatar" :src="userInfo.avatar" alt="" />
                <Icon class="user-avatar-gender" :name="userInfo.getGenderIcon()['name']" size="14" :color="userInfo.getGenderIcon()['color']" />
            </div>
            <p class="username">{{ userInfo.nickname }}</p>
            <el-button-group>
                <el-button
                    @click="routerPush('account/integral')"
                    v-blur
                    class="userinfo-button-item"
                    :title="$t('user.user.integral') + ' ' + userInfo.score"
                    size="default"
                    plain
                >
                    <span>{{ $t('user.user.integral') + ' ' + userInfo.score }}</span>
                </el-button>
                <el-button
                    @click="routerPush('account/balance')"
                    v-blur
                    class="userinfo-button-item"
                    :title="$t('user.user.balance') + ' ' + userInfo.money"
                    size="default"
                    plain
                >
                    <span>{{ $t('user.user.balance') + ' ' + userInfo.money }}</span>
                </el-button>
            </el-button-group>
        </div>

        <div class="user-menus">
            <template v-for="(item, idx) in memberCenter.state.viewRoutes">
                <div v-if="memberCenter.state.showHeadline" class="user-menu-max-title">{{ item.title }}</div>
                <div
                    v-for="menu in item.children"
                    @click="routerPush(menu.name!)"
                    class="user-menu-item"
                    :class="memberCenter.state.activeRoute?.name == menu.name ? 'active' : ''"
                >
                    <Icon :name="menu.icon" size="16" color="var(--color-secondary)" />
                    <span>{{ menu.title }}</span>
                </div>
            </template>
        </div>
    </el-aside>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useUserInfo } from '/@/stores/userInfo'
import { useMemberCenter } from '/@/stores/memberCenter'

const router = useRouter()
const userInfo = useUserInfo()
const memberCenter = useMemberCenter()

const routerPush = (routeName: string) => {
    if (document.body.clientWidth < 992) {
        memberCenter.toggleMenuExpand(false)
    }
    router.push({ name: routeName })
}
</script>

<style scoped lang="scss">
.ba-user-layouts {
    width: 240px;
    background-color: var(--color-basic-white);
    box-shadow: var(--el-box-shadow-light);
}
.userinfo {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    padding: 20px 0;
}
.username {
    display: block;
    text-align: center;
    width: 100%;
    padding: 10px 0;
    font-size: var(--el-font-size-large);
    font-weight: bold;
}
.user-avatar-box {
    position: relative;
    cursor: pointer;
}
.user-avatar {
    display: block;
    width: 100px;
    border-radius: 50%;
}
.user-avatar-gender {
    position: absolute;
    bottom: 0px;
    right: 10px;
    height: 22px;
    width: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #fff;
    border-radius: 50%;
    box-shadow: var(--el-box-shadow);
}
.userinfo-button-item {
    font-size: var(--el-font-size-small);
    height: 30px;
}
.user-menus {
    font-size: var(--el-font-size-base);
    color: var(--color-regular);
    padding-bottom: 20px;
}
.user-menu-max-title {
    font-size: 15px;
    color: var(--color-secondary);
    padding: 5px 30px;
}
.user-menu-item {
    padding: 10px 30px;
    cursor: pointer;
    .icon {
        width: 16px;
        height: 16px;
        text-align: center;
        margin-right: 8px;
    }
}
.user-menu-item:hover,
.user-menu-item.active {
    border-left: 2px solid var(--color-primary);
    padding-left: 28px;
    color: var(--color-primary);
    .icon {
        color: var(--color-primary) !important;
    }
    background-color: var(--color-bg-1);
}
@media screen and (max-width: 991px) {
    .ba-user-layouts {
        width: 100%;
        background-color: var(--color-basic-white);
        box-shadow: none;
    }
}
</style>
