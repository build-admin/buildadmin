<template>
    <div class="theme-toggle-content">
        <div class="switch">
            <div class="switch-action">
                <Icon name="local-dark" color="#f2f2f2" size="13px" class="switch-icon dark-icon" />
                <Icon name="local-light" color="#303133" size="13px" class="switch-icon light-icon" />
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
.theme-toggle-content {
    display: flex;
    align-items: center;
    height: 24px;
    padding: 0 12px;
}
.switch {
    display: inline-block;
    position: relative;
    width: 40px;
    height: 20px;
    border: 1px solid var(--el-border-color);
    border-radius: 10px;
    box-sizing: border-box;
    background-color: var(--ba-bg-color);
    cursor: pointer;
    transition:
        border-color 0.3s,
        background-color 0.5s;
}
.switch-action {
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    top: 1px;
    left: 1px;
    border-radius: 50%;
    background-color: #ffffff;
    transform: translate(0);
    color: var(--el-text-color-primary);
    transition: all 0.3s;
}
.switch-icon {
    position: absolute;
    left: 1px;
    bottom: 1px;
    transition: all 0.3s;
    cursor: pointer;
}
.light-icon {
    opacity: 1;
}
.dark-icon {
    opacity: 0;
}

@at-root .dark {
    .switch {
        background-color: #2c2c2c;
    }
    .switch-action {
        transform: translate(20px);
        background-color: #141414;
    }
    .dark-icon {
        opacity: 1;
    }
    .light-icon {
        opacity: 0;
    }
}
</style>
