<template>
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="baTable.form.extend!.showFile"
        @close="baTable.form.extend!.showFile = false"
        width="50%"
        :destroy-on-close="true"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">模块文件管理</div>
        </template>
        <div class="file-box h100">
            <div class="sys-file file-window h100">
                <el-scrollbar class="ba-table-form-scrollbar">
                    <div class="tree-header">
                        <el-popover
                            placement="right"
                            :width="200"
                            trigger="hover"
                            content="勾选目录以复制到模块，系统文件目录【展开】后才能自动选中模块已有文件！"
                        >
                            <template #reference>
                                <div class="sys-file-tip">
                                    <span>系统文件</span>
                                    <Icon name="el-icon-Warning" size="14" />
                                </div>
                            </template>
                        </el-popover>
                    </div>
                    <el-tree
                        ref="treeRef"
                        :default-checked-keys="state.moduleFiles"
                        :props="{ isLeaf: 'dir' }"
                        :load="loadRootNode"
                        lazy
                        show-checkbox
                        node-key="path"
                        highlight-current
                        @check="onCheckChange"
                    />
                </el-scrollbar>
            </div>
            <div class="module-file file-window h100">
                <el-scrollbar class="ba-table-form-scrollbar">
                    <div class="tree-header">
                        <span>模块文件</span>
                        <div class="tree-header-btns">
                            <el-popover
                                placement="left"
                                :width="200"
                                trigger="hover"
                                :content="'文件的变更立即生效，建议时常备份，备份目录：' + state.backupDir + ' 初次打开此窗口会自动备份一次'"
                            >
                                <template #reference>
                                    <div class="tree-header-btn">
                                        <Icon
                                            size="13"
                                            v-if="state.loading.backup"
                                            color="var(--el-color-primary)"
                                            name="el-icon-Loading"
                                            class="loading-icon"
                                        />
                                        <span @click="onModuleBackup">备份</span>
                                    </div>
                                </template>
                            </el-popover>
                            <el-popover placement="left" :width="200" trigger="hover" content="点击下载模块包，请注意弹窗是否被拦截">
                                <template #reference>
                                    <div class="tree-header-btn">
                                        <Icon
                                            size="13"
                                            v-if="state.loading.pack"
                                            color="var(--el-color-primary)"
                                            name="el-icon-Loading"
                                            class="loading-icon"
                                        />
                                        <span @click="onModulePack">打模块包</span>
                                    </div>
                                </template>
                            </el-popover>
                        </div>
                    </div>
                    <el-tree
                        :key="state.moduleTreeUid"
                        ref="treeRef"
                        :props="{ isLeaf: 'dir' }"
                        :load="loadModuleNode"
                        lazy
                        node-key="path"
                        highlight-current
                    >
                        <template #default="{ node, data }">
                            <span class="custom-tree-node">
                                <span>{{ node.label }}</span>
                                <el-popconfirm title="确认删除？" @confirm="onDelPath(data)">
                                    <template #reference>
                                        <Icon name="el-icon-DeleteFilled" @click.stop size="13" />
                                    </template>
                                </el-popconfirm>
                            </span>
                        </template>
                    </el-tree>
                </el-scrollbar>
            </div>
        </div>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button :disabled="state.loading.pack || state.loading.backup" @click="baTable.form.extend!.showFile = false">关闭</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive, ref, inject } from 'vue'
import type baTableClass from '/@/utils/baTable'
import { ElTree } from 'element-plus'
import type Node from 'element-plus/es/components/tree/src/model/node'
import { dir, modulePack, moduleBackup, delModuleFile, fileChange } from '/@/api/backend/modulesdev'
import { uuid } from '/@/utils/random'

const baTable = inject('baTable') as baTableClass

interface Tree {
    dir: boolean
    path: string
    label: string
    children?: Tree[]
}

const treeRef = ref<InstanceType<typeof ElTree>>()
const state: {
    backupDir: string
    loading: {
        pack: boolean
        backup: boolean
    }
    moduleFiles: string[]
    moduleTreeUid: string
} = reactive({
    backupDir: '',
    loading: {
        pack: false,
        backup: false,
    },
    moduleFiles: [],
    moduleTreeUid: uuid(),
})

const loadNode = (node: Node, resolve: (data: Tree[]) => void, type: 'root' | 'module') => {
    dir({
        uid: baTable.form.extend!.uid,
        type: type,
        path: node.data.path ? node.data.path : '',
    }).then((res) => {
        if (type == 'root' && !node.data.path) {
            state.backupDir = res.data.backupDir
            state.moduleFiles = res.data.moduleFiles
            if (res.data.needBackup) onModuleBackup()
        }
        resolve(res.data.data)
    })
}

const loadRootNode = (node: Node, resolve: (data: Tree[]) => void) => {
    return loadNode(node, resolve, 'root')
}
const loadModuleNode = (node: Node, resolve: (data: Tree[]) => void) => {
    return loadNode(node, resolve, 'module')
}

const onDelPath = (data: Tree) => {
    delModuleFile({ path: data.path }).then(() => {
        state.moduleTreeUid = uuid()
    })
}

const onCheckChange = (data: Tree, select: anyObj) => {
    const { checkedKeys } = select
    fileChange({
        uid: baTable.form.extend!.uid,
        dir: !data.dir ? 1 : 0,
        path: data.path,
        checked: checkedKeys.includes(data.path) ? 1 : 0,
    }).then(() => {
        state.moduleTreeUid = uuid()
    })
}

const onModuleBackup = () => {
    state.loading.backup = true
    moduleBackup({
        uid: baTable.form.extend!.uid,
    }).finally(() => {
        state.loading.backup = false
    })
}

const onModulePack = () => {
    state.loading.pack = true
    modulePack({
        uid: baTable.form.extend!.uid,
    })
        .then((res) => {
            window.location.href = res.data.url
        })
        .finally(() => {
            state.loading.pack = false
        })
}
</script>

<style scoped lang="scss">
.loading-icon {
    animation: icon-circle 1s linear 0s infinite;
}
@keyframes icon-circle {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
.sys-file-tip {
    display: flex;
    align-items: center;
    cursor: pointer;
    .icon {
        margin-left: 2px;
    }
}
.file-box {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 20px 0;
    .file-window {
        width: 49%;
        border: 1px solid var(--el-border-color-extra-light);
        .tree-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            padding: 5px 8px;
            background-color: var(--el-border-color-extra-light);
            line-height: 13px;
            .tree-header-btns {
                display: flex;
                align-items: center;
                .tree-header-btn {
                    display: flex;
                    align-items: center;
                    font-size: 12px;
                    padding-left: 6px;
                    color: var(--el-color-primary);
                    cursor: pointer;
                    opacity: 1;
                    &:hover {
                        opacity: 0.8;
                    }
                }
            }
        }
        .custom-tree-node {
            display: flex;
            width: 100%;
            padding-right: 10px;
            align-items: center;
            justify-content: space-between;
            user-select: none;
        }
    }
}
</style>
