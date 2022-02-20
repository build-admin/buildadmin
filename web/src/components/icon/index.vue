<script lang="ts">
import { h, resolveComponent, defineComponent, computed, CSSProperties } from 'vue'
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
            type: String,
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
            let s = `${size.replace('px', '')}px`
            return {
                fontSize: s,
                color: color,
            }
        })

        if (props.name.indexOf('el-icon-') === 0) {
            return () => h('el-icon', { class: 'icon el-icon', style: iconStyle.value }, [h(resolveComponent(props.name))])
        } else if (props.name.indexOf('local-') === 0 || isExternal(props.name)) {
            return () => h(svg, { name: props.name, size: props.size, color: props.color })
        } else {
            return () => h('i', { class: [props.name, 'icon'], style: iconStyle.value })
        }
    },
})
</script>
