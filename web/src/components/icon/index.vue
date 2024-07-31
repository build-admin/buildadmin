<script lang="ts">
import { createVNode, resolveComponent, defineComponent, computed, type CSSProperties } from 'vue'
import svg from '/@/components/icon/svg/index.vue'
import { isExternal } from '/@/utils/common'
export default defineComponent({
    name: 'Icon',
    props: {
        name: {
            type: String,
            required: true,
        },
        size: {
            type: [String,Number],
            default: '18px',
        },
        color: {
            type: String,
            default: '#000000',
        },
    },
    setup(props) {
        const iconStyle = computed((): CSSProperties => {
            const { size, color } = props
            let s = `${String(size).replace('px', '')}px`
            return {
                fontSize: s,
                color: color,
            }
        })

        if (props.name.indexOf('el-icon-') === 0) {
            return () => createVNode('el-icon', { class: 'icon el-icon', style: iconStyle.value }, [createVNode(resolveComponent(props.name))])
        } else if (props.name.indexOf('local-') === 0 || isExternal(props.name)) {
            return () => createVNode(svg, { name: props.name, size: props.size, color: props.color })
        } else {
            return () => createVNode('i', { class: [props.name, 'icon'], style: iconStyle.value })
        }
    },
})
</script>
